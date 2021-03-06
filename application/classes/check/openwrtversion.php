<?php
class Check_OpenwrtVersion extends Check
{

	public function Check_OpenwrtVersion($host)
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
			SOWN::notify_icinga($host->hostname, "OPENWRT-VERSION", 3, "UNKNOWN: Node cannot be logged into");	
			return;
		}
		try {
      			$response = $session->execute('/bin/cat /etc/openwrt_release | /bin/grep DISTRIB_DESCRIPTION | /usr/bin/awk \'BEGIN{FS="="}{print $2}\' | tr -d "\'"');
			$response = trim($response, "\n\r\t "); 
		}
		catch(Exception $e) {
      			$this->code = Check::CRITICAL;
                        $this->message = "Could not run command to examine OpenWRT version on node.";
                        return;
		}
		if(empty($response)) {
			$this->code = Check::CRITICAL;
                        $this->message = "No entry for OpenWRT version.";
                        return;
		}
		$this->code = Check::OK;
                $this->message = "The node is running $response.";
		$firmware_image = $response;
		$response_bits =  explode(" ", $response);
		$firmware_version = strtolower(implode("", array_slice($response_bits, 1, sizeof($response_bits)-2)));
		if ($firmware_image != $host->firmwareImage || $firmware_version != $host->firmwareVersion)
		{
			$host->firmwareImage = $response;
			$host->firmwareVersion = $firmware_version;
			$host->save();
		}
	}
}
