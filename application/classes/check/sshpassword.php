<?php
class Check_SshPassword extends Check
{

	public function Check_SshPassword($host)
	{
		$ip = $host->vpnEndpoint->IPv4->get_address_in_network(2);
		$session = new SSHSession($ip);
		try {
      			$session->connect();
		}
		catch(Exception $e) {
      			$this->code = Check::CRITICAL;
                	$this->message = "Could not SSH into node.";
			return;
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
			$this->code = Check::OK;
                	$this->message = "Password hash is as expected.";
			return;
		}
		$this->code = Check::CRITICAL;
                $this->message = "Password hash has changed.";
	}
}