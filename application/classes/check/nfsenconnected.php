<?php
class Check_NfsenConnected extends Check_SocketConnected
{
	public function Check_NfsenConnected($host)
	{
		$this->name = "NFSEN";
		$this->file = "/srv/www/tmp/nfcapd";
		parent::__construct($host);
	}
}
