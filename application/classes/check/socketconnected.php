<?php
class Check_SocketConnected extends Check
{
	protected $file;
	protected $name;

	public function Check_SocketConnected($host)
	{
		$ip = $host->vpnEndpoint->IPv4->get_address_in_network(2);
		
		# if file not modified in last 6 minutes go unknown;

		$last = time() - (6 * 60);
		if (filemtime($this->file) < $last) {
			$this->message = "File stale last modified: " . date("c",filemtime($this->file));
			return;			
		}

		$handle = fopen($this->file, "r");
		while(!feof($handle)) {
			$line = fgets($handle);
			if (strpos($line,">" . $ip . ":") !== false) {
				$this->code = Check::OK;
				$this->message = "Node has established connection to " . $this->name;
				fclose($handle);
				return;
			}
		}
		fclose($handle);
		
		$this->code = Check::CRITICAL;
		$this->message = "Node not connected to " . $this->name;
	}
}
