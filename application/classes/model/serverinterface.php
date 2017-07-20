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

	/**
         * @var integer $subordinate
         *
         * @Column(name="subordinate", type="integer", nullable=true)
         */
        protected $subordinate;

	/**
         * @OneToMany(targetEntity="Model_ServerInterfaceCname", mappedBy="serverInterface", cascade={"persist", "remove"})
         */
        protected $cnames;
	

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
		$str  = "ServerInterface: {$this->id}, name={$this->server->name} vlan={$this->vlan->name}, name={$this->name}, hostname={$this->hostname},  mac={$this->mac}, switchport={$this->switchport}, cable={$this->cable}, IPv4Addr={$this->IPv4Addr}, IPv6Addr={$this->IPv6Addr}, subordinate={$this->subordinate}";
		foreach($this->cnames as $cname)
                {
                        $str .= "<br/>";
                        $str .= "cname={$cname}";
                }
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='server_interface' id='server_interface_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>ServerInterface</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('vlan', $this->vlan->toHTML());
		foreach(array('name', 'hostname', 'mac', 'switchport', 'cable', 'IPv4Addr', 'IPv6Addr', 'subordinate') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		foreach($this->cnames as $cname)
                {
                        $str .= $this->fieldHTML('cname', $cname->toHTML());
                }	
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($server, $vlan, $name, $hostname, $cnames, $mac, $switchport, $cable, $IPv4Addr, $IPv6Addr, $subordinate)
        {
                $si = new Model_ServerInterface();
		$si->server = $server;
                $si->vlan = $vlan;
		$si->name = $name;
		$si->hostname = $hostname;
		
		$si->mac = $mac;
		$si->switchport = $switchport;
		$si->cable = $cable;
		$si->IPv4Addr = $IPv4Addr;
		$si->IPv6Addr = $IPv6Addr;
		$si->subordinate = $subordinate;
		$si->save();
		$cname_bits = explode(',', $cnames);
                foreach ($cname_bits as $acname)
                {
                        if (!empty($acname))
                        {
                                $sic = Model_ServerInterfaceCname::build($si, $acname);
                        }
		}
                return $si;
        }

	public function updateCnames($cnameString)	
	{
		$subCnames = explode(',', $cnameString);
		sort($subCnames);
		$curCnames = array();
		foreach ($this->cnames as $cname)
		{
			$curCnames[] = $cname->cname;
		}
		sort($curCnames);
		$newCnames = array_diff($subCnames, $curCnames);
		$oldCnames = array_diff($curCnames, $subCnames);
		foreach ($newCnames as $newCname)
		{
			$sic = Model_ServerInterfaceCname::build($this, $newCname);
		}
		foreach ($oldCnames as $oldCname)
		{
			$sic = Doctrine::em()->getRepository('Model_ServerInterfaceCname')->findOneBy(array('serverInterface' => $this, 'cname' => $oldCname));
			$sic->delete();			
		}
	}
}
