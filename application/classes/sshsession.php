<?php defined('SYSPATH') or die('No direct script access.');

// Require APT package libssh2-php
class SSHSession {

	private $host;
	private $port;
	private $auth_type;
	private $key_dir;
	private $key_pref;
	private $con;
	private $stderr;
	private $stdout;
	private $image;
	private $pkgmgr;
	private $packages;
	private $reqquote;
	private $requpdate;
	private $runcount=0;

	//include_once "packagemang.inc.php";
	//Create the objects and set up the session details (for use in connect). If libssh2 isn't installed, throws a fatal error.
	function __construct($hostname,$port=22,$auth_type="rsa",$key_dir="/var/www/.ssh/",$key_pref="id_") 
	{
		if(!function_exists("ssh2_connect")) throw new Exception("function ssh2_connect doesn't exist");
		$this->host=$hostname;
		$this->port=$port;
		$this->auth_type=$auth_type;
		$this->key_dir=$key_dir;
		$this->key_pref=$key_pref;
	}
	//function to conduct a ping of the remote node to ensure that it is up and running
	//This is mostly for nodes where their connection drops stale traffic. So it pings twice, to ensure the tunnel is "fixed"
      //Before reporting an error
	function ping() 
	{
		exec(sprintf('ping -c 2 -W 5 %s', escapeshellarg($this->host)), $res, $rval);
		 return $rval === 0;
	}

	//Connects the given ssh session. If username and password are given, text authentication is used. else the settings passed to constructor 
	//for public key based authentication are used.
	function connect($username=null,$password=null) 
	{
		global $silent;
		if(!$this->ping())
		{
			throw new SSHConnectError("Node is not responding to Ping");
		}
		if(!$this->con=ssh2_connect($this->host,$this->port)) 
		{
			throw new SSHConnectError("Unable to Start SSH Session.");
		}
		else 
		{
			//Am connected to host
			if(!empty($username))
			{
				//uname/pwd login
				if(!ssh2_auth_password($this->con,$username,$password))
				{
					throw new SSHAuthError("Unable to authenticate ".
							"using username and password",1);
				}
			}
			else 
			{
				//key based auth
				$pref = $this->key_dir.$this->key_pref.$this->auth_type;
				if(! @ssh2_auth_pubkey_file($this->con,
									"root",
									$pref.".pub",
									$pref))
				{
					if(!$silent)
						error_log("Trying alternate public key for host ".$this->host);
					$other_key = 'rsa';
					if($this->key_pref == 'rsa')
					{
						$other_key = 'dsa';
					}
					$pref =$this->key_dir.$this->key_pref.$other_key;
					if(! @ssh2_auth_pubkey_file($this->con,
										"root",
										$pref.".pub",
										$pref))
					{
						/* Oh well. doubly broken */
						throw new SSHAuthError("Unable to ".
								"authenticate using ".
								"public key",2);
					}
				}
			}
		}
	}
	//Uploads the contents of a localdir to the remote directory
	function upload_dir($localdir,$remotedir)
	{
		$files = scandir($localdir);
		foreach ($files as $file)
		{
			$localfile = $localdir . '/' . $file;
			$remotefile = $remotedir . '/' . $file;
			
			if ($file == '.' || $file == '..')
				continue;

			if (is_dir($localfile))
			{
				$this->upload_dir($localfile, $remotefile);
			}
			else
			{
				$this->fixDirectory($remotefile , FALSE);
				$this->upload($localfile, $remotefile);
			}
		}
	}
	// Uploads local file to remote path.
	// If it fails, it attempts to recover by checking for the remote directory tree. else it throws an SSHUploadError
	function upload($localfilename,$remotefilename) 
	{
		if(!($stream = ssh2_scp_send($this->con, $localfilename, $remotefilename, 0755))) 
		{
			//failed - try to recover...
			if(file_exists($localfilename) && is_readable($localfilename)) 
			{
				try {
					//local file exists, so lets try to recover by checking for directory path errors...
					$this->fixDirectory($remotefilename);
					return $this->upload($localfilename,$remotefilename);
				} catch (SSHUploadError $e) {
					//Local directory tree is fine. error is undocumetnted.
					throw $e;
				}

			} 
			else 
			{
				// File doesn't exist or is inaccessible. throw exception.
				throw new SSHUploadError("Invalid local filename");
			}
		} 
		else 
		{
			return true;
		}

	}
	//Works out which directory the file is meant to be in, and if it exists, throws an exception (something else was the cause)
	// else it tries to create the directory.
	function fixDirectory($remotefile, $fail_on_exists = TRUE) 
	{
		echo $remotefile." upload rejected. Attempting to check for directory\n";
		if(!strstr($remotefile,"/"))
			throw new SSHUploadError("Invalid file string");
		else 
		{
			$directory = dirname($remotefile);

			print "Checking for ".$directory."\n";
			$command = 'if [ -d "'.$directory.'" ]; then echo true; else echo false; fi;';
			print $command."\n";
			if($this->execute($command)=="false\n") 
			{
				print "Directory doesn't exist\n";
				print $this->execute("mkdir -p ".$directory);
			} 
			else if ($fail_on_exists == TRUE)
			{
				throw new SSHUploadError("Unrecoverable Upload error: Remote directory exists. SSH subsystem failure.");
			}
			else {
				// do nothing
			}
		}
	}
	//executes command on current ssh session
	function execute($command) {


		if(!($stream = ssh2_exec($this->con, $command)) ) 
		{
			throw new SSHExecuteError("Unable to execute Command (local)");
		} 
		else 
		{
			$stderr = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
			stream_set_blocking($stream,true);
			stream_set_blocking($stderr,true);
			stream_set_timeout($stream,180);
			stream_set_timeout($stderr,180);
			$data="";
			while ( $buf = fread($stream,4096)) 
			{
				$data .=$buf;
			}
			$errdata="";
			while ($buf = fread($stderr,4096)) 
			{
				$errdata .=$buf;
			}
			if(strlen($errdata) > 0 && (strstr($errdata,"ash: ")|| stristr($errdata,"ERROR")))
				throw new SSHExecuteError("Unable to execute Command - Remote Error returned: ".$err);
			
		}
		fclose($stream);
		return $data;
	}
	function install($package) 
	{
		if($this->runcount++==0)
			$this->update();
		echo "Local Package: ".$this->packages[$package][$this->image]."<br/>";
		if(($package = $this->packages[$package][$this->image])!=false) 
		{
		//	$cmd = $this->pkgmg['packagemgr'][$this->image]." install ".$package;
			$cmd = $this->getCommand("install",$package);
			echo "Executing: ".$cmd."<br/>";
			try 
			{
				echo $this->execute($cmd);
			} 
			catch (SSHExecuteError $e) 
			{
				throw new SSHInstallError($e->getMessage());
			}
		}
		else throw new SSHInstallError("Not allowed to install this module on this image");
	}
	function getInstalledPackages()
	{


		$cmd = $this->getCommand("list","");
		echo "Executing: ".$cmd."<br/>";
		try
		{
			$string =  $this->execute($cmd);
		}
		catch (SSHExecuteError $e)
		{
			throw new SSHExecuteError($e->getMessage());
		}



		return $string;
	}
	function remove($package)
	{
		if(($package = $this->packages[$package][$this->image])!=false) 
		{
			$cmd = getCommand($package,"remove");
			try
			{
				$this->execute($cmd);
			}
			catch(SSHExecuteError $e)
			{
				throw new SSHRemoveError($e->getMessage());
			}
		}
		else throw new SSHRemoveError("Not allowed to remove this module on this image");
	}
	function upgrade()
	{
		if($this->runcount++==0)
			$this->update();
//		$cmd = $this->pkgmg['packagemgr'][$this->image]." upgrade";
		$cmd = $this->getCommand("upgrade");
		try
		{
			$this->execute($cmd);
		} 
		catch (SSHExecuteError $e) 
		{
			throw new SSHUpgradeError($e->getMessage());
		}
	}
	function update()
	{
		//$cmd = $this->pkgmg['packagemgr'][$this->image]." update";

		$cmd = $this->getCommand("update");

		try 
		{
			$this->execute($cmd);
		} 
		catch (SSHExecuteError $e) 
		{
			throw new SSHUpdateError($e->getMessage());
		}
	}
	function getCommand($operation,$package='')
	{
		switch($operation) 
		{
	
			case "list":
				return $this->pkgmgr." list_installed";
			case "update":
				return $this->pkgmgr." update";
			case "upgrade":
				return $this->pkgmgr." upgrade";
			case "install":
				if($package=='') throw new SSHInstallError("No package name provided");
				return $this->pkgmgr." install ".  ($this->reqquote==1?"'":"").$package.($this->reqquote==1?"'":"");
			case "remove":
				if($package=='') throw new SSHInstallError("No package name provided");
				return $this->pkgmgr." remove ".   ($this->reqquote==1?"'":"").$package.($this->reqquote==1?"'":"");
			default: throw new Error("Unhandled Operation '$operation'. File: ".__FILE__." Line: ".__LINE__);
		}

	}
	function setImage($image,$pkgmgr,$requpdate,$reqquote)
	{
		$this->image	 = $image;
		$this->pkgmgr	 = $pkgmgr;
		$this->requpdate = $requpdate;
		$this->reqquote	 = $reqquote;
	}
	function setPackages($pkgmg) 
	{
		$this->packages=$pkgmg;
	}
}
class SSHConnectError extends Exception {}
class SSHAuthError extends Exception {}
class SSHExecuteError extends Exception {}
class SSHUploadError extends Exception {}
class SSHInstallError extends Exception {}
class SSHRemoveError extends Exception {}
class SSHUpgradeError extends Exception {}
class SSHUpdateError extends Exception {}
class NodeOfflineError extends Exception {}
class FileNotFoundError extends Exception {}
class SQLError extends Exception {}
class ConfigError extends Exception {}
/*
try {
	$ssh = new SSHSession("node253.sown.org.uk");
	$ssh->connect();
//	$ssh->fixDirectory("/tmp/omnom");
	if(($res=$ssh->upload("/home/sjh706/om.txt","/tmp/omm/om1.txt"))!=false) {
		print $res;
	} else {
		throw new Exception("Unable to execute Command");
	}
} catch (Exception $e) {

	var_dump($e->getMessage());
}
*/




?>
