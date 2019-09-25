<?php
class Check_DHCPLeases extends Check
{
	public function Check_DHCPLeases($host)
	{
		$ip = gethostbyname($host->hostname);
		$this->message = shell_exec('/usr/local/sbin/check_dhcp_leases ' . $ip);
		if (strpos($this->message, "OK") !== FALSE)
		{
			$this->code = Check::OK;
		}
		else if (strpos($this->message, "CRITICAL") !== FALSE)
		{
			$this->code = Check::CRITICAL;
		}
		else if (strpos($this->message, "WARNING") !== FALSE)
		{
			$this->code = Check::WARNING;
		}
		else
		{
			$this->code = Check::UNKNOWN;
		}	
	}
}
