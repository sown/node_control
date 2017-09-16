<?php
class Check_SocketConnected extends Check
{
	protected $file;
	protected $name;

	public function Check_SocketConnected($host)
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
	
		# if file not modified in last 6 minutes go unknown;

		$last = time() - (6 * 60);
		if (filemtime($this->file) < $last) {
			$this->message = "File stale last modified: " . date("c",filemtime($this->file));
			return;			
		}

		$handle = fopen($this->file, "r");
		$line_count = 0;
		while(!feof($handle)) {
			$line = fgets($handle);
			if (strpos($line,">" . $ip . ":") !== false) {
				$line_count += 1;
			}
		}

		if($line_count == 1) {
			$this->code = Check::OK;
			$this->message = "Node has established connection to " . $this->name;
		}
		else if($line_count == 0) {
			$this->code = Check::CRITICAL;
			$this->message = "Node with IP $ip not connected to " . $this->name;
		}
		else {
			$this->code = Check::WARNING;
			$this->message = "Node connected multiple times to " . $this->name;
		}

		fclose($handle);
		return;
	}
}
