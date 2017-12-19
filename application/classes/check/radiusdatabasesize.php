<?php
class Check_RadiusDatabaseSize extends Check
{
	public function Check_RadiusDatabaseSize($host)
	{
		$conn = Kohana::$config->load('database.radius.connection');
		$pdo = new PDO("mysql:hostname=" . $conn['hostname'] . ";dbname=" . $conn['database'], $conn['username'], $conn['password']);
		#mysql_select_db($conn['database']);
		foreach(array('radacct', 'radpostauth') as $table)
		{
			$q = "SELECT count(*) AS nrows FROM ".$table;
			$res = $pdo->query($q);
			$row = $res->fetchObject();
			$sizes[$table] = $row->nrows;
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
