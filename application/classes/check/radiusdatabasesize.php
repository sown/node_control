<?php
class Check_RadiusDatabaseSize extends Check
{
	public function Check_RadiusDatabaseSize($host)
	{
		$conn = Kohana::$config->load('database.radius.connection');
		mysql_connect($conn['hostname'], $conn['username'], $conn['password']);
		mysql_select_db($conn['database']);
		foreach(array('radacct', 'radpostauth') as $table)
		{
			$q = "SELECT count(*) FROM ".$table;
			$res = mysql_query($q);
			$row = mysql_fetch_row($res);
			$sizes[$table] = $row[0];
		}

		$this->code = Check::OK;
		foreach($sizes as $table => $size)
		{
			if($size > $this->getLimit($table, 'warning'))
			{
				$this->code = Check::WARNING;
			}
		}
		foreach($sizes as $table => $size)
		{
			if($size > $this->getLimit($table, 'critical'))
			{
				$this->code = Check::CRITICAL;
			}	
		}

		$this->message = "";
		foreach($sizes as $table => $size)
		{
			$this->message .= "Table " . $table . " has " . $size . " rows. ";
		}
	}

	private function getLimit($table, $type)
	{
		$limits = $this->get_limits();
		if(isset($limits[$table]))
		{
			$limits = $limits[$table];
		}
		else
		{
			$limits = $limits['default'];
		}
		return $limits[$type];
	}
}
