<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
/**
 * Model_ServerInterface
 *
 * @Table(name="server_interfaces")
 * @Entity
 */
class Model_ServerInterface extends Model_Entity
{

	/**
         * @var Model_Server
         *
         * @ManyToOne(targetEntity="Model_Server")
         * @JoinColumns({
         *   @JoinColumn(name="server_id", referencedColumnName="id")
         * })
         */
        protected $server;

	/**
         * @var Model_Vlan
         *
         * @ManyToOne(targetEntity="Model_Vlan")
         * @JoinColumns({
         *   @JoinColumn(name="vlan_id", referencedColumnName="id")
         * })
         */
        protected $vlan;
	
	/**
         * @var string $name
         *
         * @Column(name="name", type="string", length=20, nullable=true)
         */
        protected $name;

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
         * @var string $mac
         *
         * @Column(name="mac", type="string", length=17, nullable=true)
         */
        protected $mac;

	 /**
         * @var string $switchport
         *
         * @Column(name="switchport", type="string", length=255, nullable=true)
         */
        protected $switchport;

	 /**
         * @var string $cable
         *
         * @Column(name="cable", type="string", length=50, nullable=true)
         */
        protected $cable;

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
		$str  = "ServerInterface: {$this->id}, icingaName={$this->server->icingaName} vlan={$this->vlan->name}, name={$this->name}, hostname={$this->hostname}, cname={$this->cname}, mac={$this->mac}, switchport={$this->switchport}, cable={$this->cable}, IPv4Addr={$this->IPv4Addr}, IPv6Addr={$this->IPv6Addr}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='server_interface' id='server_interface_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>ServerInterface</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('vlan', $this->vlan->toHTML());
		foreach(array('name', 'hostname', 'cname', 'mac', 'switchport', 'cable', 'IPv4Addr', 'IPv6Addr') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($server, $vlan, $name, $hostname, $cname, $mac, $switchport, $cable, $IPv4Addr, $IPv6Addr)
        {
                $si = new Model_ServerInterface();
		$si->server = $server;
                $si->vlan = $vlan;
		$si->name = $name;
		$si->hostname = $hostname;
		$si->cname = $cname;
		$si->mac = $mac;
		$si->switchport = $switchport;
		$si->cable = $cable;
		$si->IPv4Addr = $IPv4Addr;
		$si->IPv6Addr = $IPv6Addr;
		$si->save();
                return $si;
        }

}
