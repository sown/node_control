<?php
class Check_SyslogConnected extends Check_SocketConnected
{
	public function Check_SyslogConnected($host)
	{
		$this->name = "SYSLOG";
		$this->file = "/srv/www/tmp/syslog-ng";
		parent::__construct($host);
	}
}
