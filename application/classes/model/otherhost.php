<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
/**
 * Model_OtherHost
 *
 * @Table(name="other_hosts")
 * @Entity
 */
class Model_OtherHost extends Model_Entity
{
	/**
         * @var string $name
         *
         * @Column(name="name", type="string", length=255, nullable=true)
         */
        protected $name;

	/**
         * @var string $type
         *
         * @Column(name="type", type="string", length=255, nullable=true)
         */
        protected $type;

	/**
         * @var string $parent
         *
         * @Column(name="parent", type="string", length=255, nullable=true)
         */
        protected $parent;

	 /**
         * @var string $description
         *
         * @Column(name="description", type="text", nullable=true)
         */
        protected $description;

	/**
         * @var datetime $acquiredDate
         *
         * @Column(name="acquired_date", type="datetime", nullable=false)
         */
        protected $acquiredDate;

        /**
         * @var integer $retired
         *
         * @Column(name="retired", type="integer", nullable=true)
         */
        protected $retired;

	/**
         * @var integer $internal
         *
         * @Column(name="internal", type="integer", nullable=true)
         */
        protected $internal;

	/**
         * @var string $case
         *
         * @Column(name="host_case", type="string", length=255, nullable=true)
         */
        protected $case;

	/**
         * @var Model_Location
         *
         * @ManyToOne(targetEntity="Model_Location")
         * @JoinColumns({
         *   @JoinColumn(name="location_id", referencedColumnName="id")
         * })
         */
        protected $location;
	
	/**
         * @var string $hostname
         *
         * @Column(name="hostname", type="string", length=255, nullable=true)
         */
        protected $hostname;

        /**
         * @var string $cname
         *
         * @Column(name="cname", type="string", length=255, nullable=true)
         */
        protected $cname;

	/**
         * @var string $IPv4Addr
         *
         * @Column(name="ipv4_addr", type="ipv4address", nullable=true)
         */
        protected $IPv4Addr;

        /**
         * @var string $IPv6Addr
         *
         * @Column(name="ipv6_addr", type="ipv6address", nullable=true)
         */
        protected $IPv6Addr;	

	 /**
         * @var string $alias
         *
         * @Column(name="alias", type="string", length=255, nullable=true)
         */
        protected $alias;

	 /**
         * @var string $checkCommand
         *
         * @Column(name="check_command", type="string", length=255, nullable=true)
         */
        protected $checkCommand;



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

	public static function getByName($name)
	{
		return Doctrine::em()->getRepository('ModelOtherHost')->findOneByName($name);
	}

	public static function getByHostname($hostname)
	{
                return Doctrine::em()->getRepository('Model_OtherHost')->findOneByHostnameOrCname($hostname);
	}

	public static function getByIPAddress($ip)
        {
		return Doctrine::em()->getRepository('Model_OtherHost')->findOneByIPv4AddrOrIPv6Addr($ip);
        }

	public function __toString()
	{
		$this->logUse();
		$str  = "OtherHost: {$this->id}, name={$this->name}, type={$type}, parent={$this->parent}, description={$this->description}, location={$this->location}, acquiredDate={$this->acquiredDate->format('Y-m-d H:i:s')}, retired={$this->retired}, case={$this->case}, hostname={$this->hostname}, cname={$this->cname}, IPv4Addr={$this->IPv4Addr}, IPv6Addr={$this->IPv6Addr}, alias={$this->alias}, checkCommand->{$this->checkCommand}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='server' id='server_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Server</th><td>{$this->id}</td></tr>";
		foreach(array('name', 'type', 'parent', 'description',) as $eield)
		{
			$str .= $this->fieldHTML($field);
		}
		if($this->location)
		{
			$str .= $this->fieldHTML('location', $this->location->toHTML());
		}
		$str .= $this->fieldHTML('acquiredDate', $this->acquiredDate->format('Y-m-d H:i:s'));
		foreach(array('retired', 'case', 'hostname', 'cname', 'IPv4Addr', 'IPv6Addr', 'alias' ,'checkCommand') as $field)
                {
                        $str .= $this->fieldHTML($field);
                }
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public function hasOnlyLocalCName()
	{
		foreach ($this->interfaces as $i)
                {
			$hostname_bits = explode('.', $i->hostname);
			$cname_bits = explode('.', $i->cname);
			if (strlen($cname_bits[0]) > 0 && sizeof($hostname_bits) > 1 && sizeof($cname_bits) == 1)
			{
                                return true;
                        }
                }
		return false;
	}

	public static function uniqueName($name, $id = 0)
        {
		if (empty($name))
		{
			return FALSE;
		}
                $result1 = Doctrine::em()->getRepository('Model_OtherHost')->findOneByName($name);
		$result2 = Doctrine::em()->getRepository('Model_Server')->findOneByName($name);
                if (!empty($result1->id) && $result1->id == $id)
                {
                        return TRUE;
                }
		if (!empty($result2->id) && $result2->id == $id)
                {
                        return TRUE;
                }
                return empty($result1->id) && empty($result2->id);
        }

	public static function build($name, $description, $type)
        {
                $other_host = new Model_OtherHost();
                $other_host->name = $name;
		$other_host->description = $description;
		$other_host->type = $type;
		$other_host->save();
                return $other_host;
        }

}