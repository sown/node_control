<?php
class Check_SshPassword extends Check
{

	public function Check_SshPassword($host)
	{
		$dnsInterface = $host->dnsInterface;
                if (!empty($dnsInterface))
                {
                        $ip = $dnsInterface->IPv4Addr;
                }
                else
                {
                        $ip = $host->vpnEndpoint->IPv4->get_address_in_network(2);
                }
		$session = new SSHSession($ip);
		try {
      			$session->connect();
		}
		catch(Exception $e) {
      			$this->code = Check::CRITICAL;
                	$this->message = "Could not SSH into node.";
			SOWN::notify_icinga($host->hostname, "SSH", 2, "SSH CRITICAL: Node cannot be logged into");	
			return;
		}
		SOWN::notify_icinga($host->hostname, "SSH", 0, "SSH OK: Node can be logged into");
		try {
			$response = $session->execute('/bin/ps | /bin/grep vpn | /bin/grep -v grep | /usr/bin/wc -l | tr -d "\n"');
			if ( $response != 1 )
			{
				SOWN::notify_icinga($host->hostname, "VPN-PROCS", 1, "VPN-PROCS WARNING: There should be only 1 VPN process running, (currently $response).");
			}
			else 
			{
				SOWN::notify_icinga($host->hostname, "VPN-PROCS", 0, "VPN-PROCS OK: There is only 1 VPN process running.");
			}
		}
		catch(Exception $e) {
			error_log("Reporting VPN-PROCS critical for ".$host->hostname);
			SOWN::notify_icinga($host->hostname, "VPN-PROCS", 2, "VPN-PROCS: Could not run command to count number of VPN processes.");
		}
		try {
      			$response = $session->execute('/bin/cat /etc/shadow | /bin/grep root');
		}
		catch(Exception $e) {
      			$this->code = Check::CRITICAL;
                        $this->message = "Could not run command to examine SSH password on node.";
                        return;
		}
		if(empty($response)) {
			$this->code = Check::CRITICAL;
                        $this->message = "No entry for root user password.";
                        return;
		}
		$responseparts = explode(':', $response, 3);
		$pwhash = $host->passwordHash;
		if (empty($pwhash) && !empty($responseparts[1])) {
			$host->passwordHash = $responseparts[1];
			$host->save();
		}
		if($responseparts[1] == $host->passwordHash) {
			$pwsession = new SSHSession($ip);
			try {
				$defpassword =  Kohana::$config->load('database.node_config.ssh_default_password');
                        	$pwsession->connect('root', $defpassword);
				$this->code = Check::CRITICAL;
                		$this->message = "Still using default setup password.";
				return;
                	}
                	catch(Exception $e) {}
			$this->code = Check::OK;
                	$this->message = "Password hash is as expected.";
			return;
		}
		$this->code = Check::CRITICAL;
                $this->message = "Password hash has changed.";
	}
}
