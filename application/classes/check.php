<?php

class Check
{
	const UNKNOWN = 3;
	const OK = 0;
	const WARNING = 1;
	const CRITICAL = 2;

	public $code = self::UNKNOWN;
	public $message = "UNKNOWN";

	public function format_icinga()
	{
		echo serialize(get_object_vars($this));
	}

	public function get_limits()
	{
		return Kohana::$config->load('system.default.check.limit.' . substr(get_class($this), 6));
	}
}
