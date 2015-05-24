<?php
class Check_OpenvpnRunning extends Check
{
        protected $file;
        protected $name;


	public function Check_OpenvpnRunning($host)
	{
		$this->name = "OPENVPN";
		$this->file = "/srv/www/tmp/openvpn";

                $last = time() - (6 * 60);
                if (filemtime($this->file) < $last) {
                        $this->message = "File stale last modified: " . date("c",filemtime($this->file));
                        return;
                }

		$ip = $host->vpnEndpoint->vpnServer->externalIPv4;
		$port = $host->vpnEndpoint->port;

                $handle = fopen($this->file, "r");
                while(!feof($handle)) {
                        $line = fgets($handle);
                        if (strpos($line, $ip . ":" . $port) !== false) {
                                $this->code = Check::OK;
                                $this->message = "Openvpn Server Running on port ".$port;
                                fclose($handle);
                                return;
                        }
                }

                fclose($handle);


		$this->code = Check::CRITICAL;
		$this->message = "Openvpn Server NOT Running on port ".$port;


	}
}
