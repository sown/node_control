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
 * Model_Server
 *
 * @Table(name="servers")
 * @Entity
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"server" = "Model_Server", "vpn" = "Model_VpnServer"})
 */
class Model_Server extends Model_Entity
{
	/**
         * @var string $name
         *
         * @Column(name="name", type="string", length=255, nullable=true)
         */
        protected $name;

	/**
         * @var string $state
         *
         * @Column(name="state", type="string", length=50, nullable=true)
         */
        protected $state;

	 /**
         * @var string $purpose
         *
         * @Column(name="purpose", type="string", length=50, nullable=true)
         */
        protected $purpose;

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
	 * @var Model_Certificate
	 *
	 * @ManyToOne(targetEntity="Model_Certificate", cascade={"persist", "remove"})
	 * @JoinColumns({
	 *   @JoinColumn(name="certificate_id", referencedColumnName="id")
	 * })
	 */
	protected $certificate;

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
         * @var string $serverCase
         *
         * @Column(name="server_case", type="string", length=255, nullable=true)
         */
        protected $serverCase;

        /**
         * @var string $processor
         *
         * @Column(name="processor", type="string", length=255, nullable=true)
         */
        protected $processor;

	/**
         * @var string $memory
         *
         * @Column(name="memory", type="string", length=20, nullable=true)
         */
        protected $memory;

        /**
         * @var string $hardDrive
         *
         * @Column(name="hard_drive", type="string", length=255, nullable=true)
         */
        protected $hardDrive;

        /**
         * @var string $networkPorts
         *
         * @Column(name="network_ports", type="string", length=255, nullable=true)
         */
        protected $networkPorts;
	
	/**
         * @var string $wakeOnLan
         *
         * @Column(name="wake_on_lan", type="string", length=255, nullable=true)
         */
        protected $wakeOnLan;

	/**     
         * @var string $kernel
         *
         * @Column(name="kernel", type="string", length=255, nullable=true)
         */
        protected $kernel;

        /**     
         * @var string $os
         *
         * @Column(name="os", type="string", length=255, nullable=true)
         */
        protected $os;

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
         * @OneToMany(targetEntity="Model_ServerInterface", mappedBy="server", cascade={"persist", "remove"})
         */
        protected $interfaces;

	/**
         * @OneToMany(targetEntity="Model_Contact", mappedBy="server", cascade={"persist", "remove"})
         */
        protected $contacts;


       	/**
	* @ManyToMany(targetEntity="Model_CronJob")
	* @JoinTable(name="host_cron_jobs",
	*      joinColumns={@JoinColumn(name="server_id", referencedColumnName="id")},
	*      inverseJoinColumns={@JoinColumn(name="cron_job_id", referencedColumnName="id")}
	*      )
	*/ 
	protected $cronJobs;
	

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

	public function getAllCronJobs()
        {
                $allCronJobs = $this->cronJobs;

                return $allCronJobs;
        }

	public function getEnabledCronJobs()
	{
		$enabledCronJobs = array();
		$allCronJobs = $this->getAllCronJobs();
		foreach ($allCronJobs as $c => $cronJob) 
		{
			if ($cronJob->disabled == 0)
			{
				$enabledCronJobs[] = $cronJob;
			}
		}
		return $enabledCronJobs;
	}

	public static function getByName($name)
	{
		return Doctrine::em()->getRepository('Model_Server')->findOneByName($name);
	}

	public static function getByHostname($hostname)
	{
		
		$query = Doctrine::em()->createQuery("SELECT s.id FROM Model_Server s JOIN s.interfaces si WHERE si.hostname LIKE '$hostname' OR si.cname LIKE '$hostname'")->setMaxResults(1);
                $server_id = $query->getSingleScalarResult();
		if (!empty($server_id))
		{
                	return Doctrine::em()->getRepository('Model_Server')->find($server_id);
		}
	}

	public static function getByIPAddress($ip)
        {

                $query = Doctrine::em()->createQuery("SELECT s.id FROM Model_Server s JOIN s.interfaces si WHERE si.IPv4Addr LIKE '$ip' OR si.IPv6Addr LIKE '$ip'")->setMaxResults(1);
                $server_id = $query->getSingleScalarResult();
		if (!empty($server_id))
                {
	                return Doctrine::em()->getRepository('Model_Server')->find($server_id);
		}
        }

	public function __toString()
	{
		$this->logUse();
		$acquiredDate = (is_object($this->acquiredDate) ? $this->acquiredDate->format('Y-m-d H:i:s') : '');
		$str  = "Server: {$this->id}, name={$this->name}, state={$this->sate}, purpose={$this->purpose}, parent={$this->parent}, description={$this->description}, acquiredDate={$acquiredDate}, retired={$this->retired}, serverCase={$this->serverCase}, processor={$this->processor}, memory={$this->memory}, hardDrive={$this->hardDrive}, networkPorts={$this->networkPorts}, wakeOnLan={$this->wakeOnLan},` kernel={$this->kernel}, os={$this->os}";
		$str .= "<br/>";
		$str .= "certificate={$this->certificate}";
		$str .= "<br/>";
		$str .= "location={$this->location}";
		foreach($this->interfaces as $interface)
                {
                        $str .= "<br/>";
                        $str .= "interface={$interface}";
                }
		foreach($this->contacts as $contact)
                {
                        $str .= "<br/>";
                        $str .= "contact={$contact}";
                }
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='server' id='server_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Server</th><td>{$this->id}</td></tr>";
		foreach(array('name', 'state', 'purpose', 'parent', 'description') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
  		
		foreach(array('certificate', 'location') as $field)
		{
			if(is_object($this->$field))
			{
				$str .= $this->fieldHTML($field, $this->$field->toHTML());
			}
		}
		$acquiredDate = (is_object($this->acquiredDate) ? $this->acquiredDate->format('Y-m-d H:i:s') : '');
		$str .= $this->fieldHTML('acquiredDate', $acquiredDate);
		foreach(array('retired', 'serverCase', 'processor', 'memory', 'hardDrive', 'networkPorts', 'wakeOnLan', 'kernel', 'os') as $field)
                {
                        $str .= $this->fieldHTML($field);
                }
		foreach($this->interfaces as $interface)
                {
                        $str .= $this->fieldHTML('interface', $interface->toHTML());
               	}
		foreach($this->contacts as $contact)
                {
                        $str .= $this->fieldHTML('contact', $contact->toHTML());
                }
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public function toWikiMarkup()
	{
		$wm = "";
		$wm .= $this->name;
		$wm .= (empty($this->description) ? ' is a '.Kohana::$config->load('system.default.name').' server' : " is ".$this->description);
		$wm .= ".\n\n";
		$hwphrases = array();
		if (!empty($this->processor)) $hwphrases[] = "a [[cpu::{$this->processor}]] processor";
		if (!empty($this->memory)) $hwphrases[] = "[[memory::{$this->memory}]] of memory";
		if (!empty($this->hardDrive)) $hwphrases[] = "[[harddrive::{$this->hardDrive}]] of memory";
		if (!empty($this->networkPorts)) $hwphrases[] = "[[networkinterfaces::{$this->networkPorts}]] network intefaces";
		$wm .= (sizeof($hwphrases) > 0 ? "It has {$hwphrases[0]}" : '');
		for ($h = 1; $h < sizeof($hwphrases); $h++) 
		{
			$wm .= ($h == sizeof($hwphrases)-1 ? ' and ' : ', ');
			$wm .= $hwphrases[$h];
		}	
		$wm .= (sizeof($hwphrases) > 0 ? '. ' : '');
		if (!empty($this->serverCase) || !empty($this->location)) {
			$wm .= "It is ";
			$wm .= (!empty($this->serverCase) ? "housed within a [[case::{$this->serverCase}]] case " : '');
			$wm .= (!empty($this->location) ? "located in [[location::{$this->location->longName} ({$this->location->name})]] ([[coordinates::{$this->location->latitude}, {$this->location->longitude}]])" : '');
			$wm .= ".";
		}
		$wm .= (sizeof($hwphrases) > 0 ? "\n\n" : "\n");
		$wm .= (!empty($this->acquiredDate) ? '' : "It was accquired on [[acquired_date::".$this->acquiredDate->format('jS F Y')."]].\n\n");
		$wm .= "= Network =\n\n";
		foreach ($this->interfaces as $interface)
		{
			$intfIPv4Addr = $interface->IPv4Addr;
			$intfIPv6Addr = $interface->IPv6Addr;
			$intfHostname = $interface->hostname;
                        $intfCname = $interface->cname;
			$intfMac = $interface->mac;
                        $intfSwitchport = $interface->switchport;
			$intfName = $interface->name;
                        $intfCable = $interface->cable;
			if (!empty($intfIPv4Addr))
			{
				$domain = "";
				$prefix = $interface->vlan->prefix;
				if ($interface->vlan->name == Kohana::$config->load('system.default.vlan.local'))
				{
					$domain = ".".Kohana::$config->load('system.default.domain');
				}
				if (!empty($intfIPv4Addr) || !empty($intfIPv6Addr))
				{
					$wm .= "This server is connected to the ".$interface->vlan->name." VLAN with the IP addresses:\n";
					$wm .= (empty($intfIPv4Addr) ? '' : "* [[{$prefix}ipv4::{$intfIPv4Addr}]]\n");
					$wm .= (empty($intfIPv6Addr) ? '' : "* [[{$prefix}ipv6::".str_replace("::", ":&#58;", $intfIPv6Addr)."]]\n");
				}
				$wm .= "It has the DNS names:\n* [[{$prefix}dns::{$interface->hostname}{$domain}]]\n";
				$wm .= (empty($intfCname) ? '' : "[[has_cname::{$intfCname}{$domain}]]\n");
				if (!empty($intfMac) || !empty($infName)  || !empty($intfSwitchport) || !empty($intfCable)) 
				{
					$wm .= "Its ";
					$wm .= (empty($intfMac) ? '' : "MAC address is [[{$prefix}mac::{$intfMac}]]");
					$wm .= (!empty($intfMac) && !empty($intfName) ? ' on its ' : ' ');
					$wm .= (empty($intfName) ? '' : "[[{$prefix}interface::{$intfName}]] interface");
					$wm .= ((!empty($intfMac) || !empty($intfName)) && ((!empty($intfSwitchport) || !empty($intfCable))) ? ' and' : '');
					$wm .= (!empty($intfSwitchport) || !empty($intfCable) ? ' connected' : '');
					$wm .= (empty($intfSwitchport) ? '' : " to port [[{$prefix}port::{$intfSwitchport}]]");
					$wm .= (empty($intfCable) ? '' : " with a {$intfCable} network cable");
					$wm .= ".\n";
				}
				$wm .= "\n";
			}
		}
		if (!empty($this->wakeOnLan))
		{
			$wm .= "The WakeOnLAN capability of this server is [[WakeOnLAN::{$this->wakeOnLan}]].\n\n";
		}
		$wm .= "[[Category:Server]]";	
		return $wm;
	}

	public function getIPAddresses($version = NULL, $vlan = NULL, $subordinate = NULL)
	{
		$versions = array();
                if ($version === NULL)
                {
			$versions = array('IPv4Addr','IPv6Addr');
		}
		else
		{
			$versions[] = "IPv${version}Addr";
		}
		if ($vlan = "LOCAL") 
		{
			$vlan = Kohana::$config->load('system.default.vlan.local');
		}

		$ip_addrs = array();
		foreach ($this->interfaces as $i)
                {
			if ($vlan !== NULL && is_object($i->vlan) && $i->vlan->name == $vlan) 
			{
				$ip_addrs = Model_Server::getIPsVersionAndSubordinate($i, $versions, $subordinate, $ip_addrs);
                        }
			elseif ($vlan === NULL)
			{
				$ip_addrs = Model_Server::getIPsVersionAndSubordinate($i, $versions, $subordinate, $ip_addrs);	
			}
                }
                return $ip_addrs;
	}

	private static function getIPsVersionAndSubordinate($interface, $versions, $subordinate, $ip_addrs)
	{
		if ($subordinate === NULL)
                {
                	foreach ($versions as $v)
                        {
				$ip_addr = $interface->$v;
				if (!empty($ip_addr))
				{
                        		$ip_addrs[] = $interface->$v;
				}
                        }
                        return $ip_addrs;
		}
                foreach ($versions as $v)
                {
			$ip_addr = $interface->$v;
			if (!empty($ip_addr) && $interface->subordinate == $subordinate)
			{
                        	$ip_addrs[] = $interface->$v;
                        }
		}
                return $ip_addrs;
	}

	public function hasLocalInterface()
        {
		$local_vlan = Kohana::$config->load('system.default.vlan.local');
		foreach ($this->interfaces as $i)
		{
			if (is_object($i->vlan) && $i->vlan->name == $local_vlan) {
				return true;
			}
		}
		return false;
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

	public static function build($name, $description, $state, $purpose, $parent)
        {
                $obj = new Model_Server();
                $obj->name = $name;
		$obj->description = $description;
		$obj->state = $state;
		$obj->purpose = $purpose;
		$obj->parent = $parent;		
		$obj->save();
                return $obj;
        }

}
