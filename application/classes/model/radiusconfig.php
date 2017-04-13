<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_RadiusConfig
 *
 * @Table(name="radius_configs")
 * @Entity
 */
class Model_RadiusConfig extends Model_Entity
{

	/**
         * @var string $name
         *
         * @Column(name="name", type="string", length=255, nullable=false)
         */
        protected $name;
	
	/**
	 * @var string $authIPv4Addr
	 *
	 * @Column(name="auth_ipv4_addr", type="ipv4address", nullable=false)
	 */
	protected $authIPv4Addr;

	/**
	 * @var string $authIPv6Addr
	 *
	 * @Column(name="auth_ipv6_addr", type="ipv6address", nullable=false)
	 */
	protected $authIPv6Addr;

	/**
	 * @var integer $authPort
	 *
	 * @Column(name="auth_port", type="integer", nullable=true)
	 */
	protected $authPort;

	/**
         * @var string $acctIPv4Addr
         *
         * @Column(name="acct_ipv4_addr", type="ipv4address", nullable=false)
         */
        protected $acctIPv4Addr;

        /**
         * @var string $acctIPv6Addr
         *
         * @Column(name="acct_ipv6_addr", type="ipv6address", nullable=false)
         */
        protected $acctIPv6Addr;

	/**
	 * @var integer $acctPort
	 *
	 * @Column(name="acct_port", type="integer", nullable=true)
	 */
	protected $acctPort;

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

	public function __toString()
	{
		$this->logUse();
		$str  = "RadiusConfig: {$this->id}, name={$this->name}, authIPv4Addr={$this->authIPv4Addr}, authIPv6Addr={$this->authIPv6Addr}, authPort={$this->authPort}, acctIPv4Addr={$this->authIPv4Addr}, acctIPv6Addr={$this->acctIPv6Addr}, acctPort={$this->acctPort}";
		$str .= "<br/>";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='radiusConfig' id='radiusConfig_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>RADIUS Config</th><td>{$this->id}</td></tr>";
		foreach(array('name', 'authIPv4addr', 'authIPv6addr', 'authPort', 'acctIPv4addr', 'acctIPv6addr', 'acctPort') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function getRadiusConfigNames($and_none = false)
	{
		$radiusConfigs = Doctrine::em()->getRepository('Model_RadiusConfig')->findAll();
		$radiusConfigNames = array();
		if ($and_none)
		{
			$radiusConfigNames[] = "";
		}
		foreach ($radiusConfigs as $radiusConfig)
		{
        		$radiusConfigNames[$radiusConfig->id] = $radiusConfig->name;
		}
		return $radiusConfigNames;
	}

}
