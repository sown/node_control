<?php
class Check_SshPassword extends Check
{

	public function Check_SshPassword($host)
	{
		$ip = $host->vpnEndpoint->IPv4->get_address_in_network(2);
		
		$session = new SSHSession($host);
		try {
      			@$session->connect();
		}
		catch(Exception $e) {
      			$this->code = Check::CRITICAL;
                	$this->message = "Could not SSH into node.";
			return;
		}
		try {
      			$response = @$session->execute('cat /etc/passwd | /bin/grep root');
		}
		catch(Exception $e) {
      			$this->code = Check::CRITICAL;
                        $this->message = "Could not run command to examine SSH password on node.";
                        return;
		}
		if($response == '') {
			$this->code = Check::CRITICAL;
                        $this->message = "No entry for root user password.";
                        return;
		}
		$responseparts = split(':', $response,3);
		if($responseparts[1] == Kohana::$config->load('database.node_config.ssh.password_hash')) {	
			$this->code = Check::OK;
                	$this->message = "Password value is as expected.";
			return;
		}
		$this->code = Check::CRITICAL;
                $this->message = "Password value is incorrect.";
	}
}
