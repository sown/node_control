<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;

/**
 * Model_LoginStatistic
 *
 * @Table(name="stats_login")
 * @Entity
 */
class Model_LoginStatistic extends Model_Entity
{
	 /**
         * @var string $remoteIp
         *
         * @Column(name="remote_ip", type="string", length=255, nullable=false)
         */
        protected $remoteIp;

	 /**
         * @var string $domain
         *
         * @Column(name="domain", type="string", length=255, nullable=false)
         */
        protected $domain;

	 /**
         * @var string $result
         *
         * @Column(name="result", type="string", length=7, nullable=false)
         */
        protected $result;

	/**
         * @var datetime $timeLogged
         *
         * @Column(name="time_logged", type="datetime", nullable=false)
         */
	protected $timeLogged;

	/**
	 * @var string $userAgent
	 *
	 * @Column(name="user_agent", type="text", nullable=true)
	 */
	protected $userAgent;

 	/**
         * @var string $userAgent
         *
         * @Column(name="details", type="text", nullable=true)
         */
        protected $details;


	public function __get($name)
	{
		$this->logUse();
		switch($name)
		{
			default:
				if (property_exists($this, $name))
				{
					return $this->$name;
				}
				else
				{
					return parent::__get($name);
				}
		}
	}
	
	public function __set($name, $value)
	{
		switch($name)
		{
			default:
				if (property_exists($this, $name))
				{
					$this->$name = $value;
				}
				else
				{
					parent::__set($name, $value);
				}
		}
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "LoginStatistic: {$this->id}, remoteIp={$this->remoteIp}, domain={$this->domain}, result={$this->result}, timeLogged={$this->timeLogged->format('Y-m-d H:i:s')}, userAgent={$this->uesrAgent}, details={$this->details}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='loginstatistic' id='loginstatistic_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>LoginStatistic</th><td>{$this->id}</td></tr>";
		foreach(array('remoteIp', 'domain', 'result', 'timeLogged', 'userAgent', 'details') as $field)
		{
			if ($field == 'timeLogged') $str .= $this->fieldHTML($this->timeLogged->format('Y-m-d H:i:s'));
			else $this->fieldHTML($field);
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($remoteIp, $fq_username, $result, $userAgent, $details)
	{
		$fqu_bits = explode("@", $fq_username);
		$domain = "";
		if (isset($fqu_bits[1])) $domain = $fqu_bits[1];
		$obj = new Model_LoginStatistic();
		$obj->remoteIp = $remoteIp;
		$obj->domain = $domain;
		$obj->result = $result;
		$obj->timeLogged = new \DateTime();
		$obj->userAgent = $userAgent;
		$obj->details = $details;
		return $obj;
	}
}
