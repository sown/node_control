<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Model_Node
 *
 * @Table(name="nodes")
 * @Entity(repositoryClass="Model_Repository_Node")
 */
class Model_Node extends Model_Entity
{
	/**
	 * @var integer $boxNumber
	 *
	 * @Column(name="box_number", type="integer", nullable=true)
	 */
	protected $boxNumber;

	/**
         * @var Model_NodeHardware
         *
         * @ManyToOne(targetEntity="Model_NodeHardware")
         * @JoinColumns({
         *   @JoinColumn(name="node_hardware_id", referencedColumnName="id")
         * })
         */
	protected $nodeHardware;

	/**
         * @var string $firmwareVersion
         *
         * @Column(name="firmware_version", type="string", nullable=false)
         */
        protected $firmwareVersion;

	/**
	 * @var text $firmwareImage
	 *
	 * @Column(name="firmware_image", type="text", nullable=false)
	 */
	protected $firmwareImage;

	/**
         * @var string $passwordHash
         *
         * @Column(name="password_hash", type="string", nullable=false)
         */
        protected $passwordHash;

	/**
	 * @var boolean $undeployable
	 * 
	 * @Column(name="undeployable", type="integer", nullable=false)
         */
	protected $undeployable;

        /**
         * @var boolean $externalBuild
         * 
         * @Column(name="external_build", type="integer", nullable=false)
         */
        protected $externalBuild;

	/**
         * @var string $primaryDNSIPv4Addr
         *
         * @Column(name="primary_dns_ipv4_addr", type="ipv4address", nullable=false)
         */
        protected $primaryDNSIPv4Addr;

	/**
         * @var string $secondaryDNSIPv4Addr
         *
         * @Column(name="secondary_dns_ipv4_addr", type="ipv4address", nullable=false)
         */
        protected $secondaryDNSIPv4Addr;

	 /**
         * @var string $primaryDNSIPv6Addr
         *
         * @Column(name="primary_dns_ipv6_addr", type="ipv6address", nullable=false)
         */
        protected $primaryDNSIPv6Addr;

        /**
         * @var string $secondaryDNSIPv6Addr
         *
         * @Column(name="secondary_dns_ipv6_addr", type="ipv6address", nullable=false)
         */
        protected $secondaryDNSIPv6Addr;


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
	 * @var Model_VpnEndpoint
	 *
	 * @ManyToOne(targetEntity="Model_VpnEndpoint", cascade={"persist", "remove"})
	 * @JoinColumns({
	 *   @JoinColumn(name="vpn_endpoint_id", referencedColumnName="id")
	 * })
	 */
	protected $vpnEndpoint;

        /**
         * @var Model_Interface
	 *
         * @OneToOne(targetEntity="Model_Interface")
         * @JoinColumns({
         *   @JoinColumn(name="dns_interface_id", referencedColumnName="id")
         * })
         */
        protected $dnsInterface;

	/**
         * @var Model_Server
         *
         * @ManyToOne(targetEntity="Model_Server")
         * @JoinColumns({
         *   @JoinColumn(name="syslog_server_id", referencedColumnName="id")
         * })
         */
        protected $syslogServer;

 	/**
         * @var Model_Switch
         *
         * @OneToOne(targetEntity="Model_Switch", cascade={"persist", "remove"})
         * @JoinColumns({
         *   @JoinColumn(name="switch_id", referencedColumnName="id")
         * })
         */
        protected $switch;

	/**
	 * @OneToMany(targetEntity="Model_NodeDeployment", mappedBy="node")
	 */
	protected $nodeDeployments;

	/**
	 * @OneToMany(targetEntity="Model_Interface", mappedBy="node", cascade={"persist", "remove"})
	 */
	protected $interfaces;

        /**
         * @OneToMany(targetEntity="Model_NodeCname", mappedBy="node", cascade={"persist", "remove"})
         */
        protected $cnames;

	/**
         * @OneToMany(targetEntity="Model_NodeSetupRequest", mappedBy="node", cascade={"persist", "remove"})
         */
        protected $nodeSetupRequests;

	/**
         * @OneToMany(targetEntity="Model_Note", mappedBy="node", cascade={"persist", "remove"})
         */
        protected $notes;

	/*
	 * @ManyToMany(targetEntity="Model_CronJob")
         * @JoinTable(name="host_cron_jobs",
         *      joinColumns={@JoinColumn(name="node_id", referencedColumnName="id")},
         *      inverseJoinColumns={@JoinColumn(name="cron_job_id", referencedColumnName="id")}
         *      )
         */
        protected $cronJobs;

	public function __construct()
	{
		parent::__construct();
	 	$this->interfaces = new ArrayCollection();
		$this->nodeDeployments = new ArrayCollection();
		$this->nodeSetupRequests = new ArrayCollection();
	}

	public function __get($name)
	{
		$this->logUse();
		switch($name)
		{
			case "FQDN":
				return $this->getFQDN();
			case "name":
				return $this->getName();
			case "nfsenName":
				return $this->getNfsenName();
			case "hostname":
				return $this->getHostname();
			case "updatePoint":
				return $this->getUpdatePoint();
			case "currentDeployment":
				return $this->getCurrentDeployment();
			case "radiusSecret":
				return $this->getRadiusSecret();
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
			case "FQDN":
			case "name":
			case "nfsenName":
			case "hostname":
			case "updatePoint":
			case "currentDeployment":
			case "radiusSecret":
				parent::__throwReadOnlyException($name);
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

	protected function getFQDN()
	{
		return $this->hostname.'.'.Kohana::$config->load('system.default.domain');
	}

	protected function getHostname()
	{
		return 'node'.$this->boxNumber;
	}

	protected function getName()
	{
		if ($this->currentDeployment !== null)
		{
			return $this->currentDeployment->name;
		}
		else
		{
			return $this->hostname;
		}
	}

	protected function getNfsenName()
        {
                if ($this->currentDeployment !== null)
                {
                        return $this->currentDeployment->nfsenName;
                }
                else
                {
                        return substr(str_replace(' ', '_', $this->hostname), 0, 19);
                }
        }

	protected function getRadiusSecret()
	{
		return $this->certificate->privateKeyFingerprint;
	}
	
	protected function getUpdatePoint()
	{
		$now2 = time();
		$near = date("Y-m-d 04:00:00");
		$now = date("Y-m-d H:i:s",$now2);

		if ($near > $now) {
			$unix = date("U",$now2);
			$day = 60 * 60 * 24;
			$unix = $unix - $day;
			return date("Y-m-d 04:00:00",$unix);
		} else {
			return $near;
		}
	}

	protected function getCurrentDeployment()
	{
		$date = time();
		foreach($this->nodeDeployments as $nodeDeployment)
		{
			if($nodeDeployment->startDate->getTimestamp() <  $date && ($nodeDeployment->endDate === null || $nodeDeployment->endDate->getTimestamp() > $date))
			{
				return $nodeDeployment->deployment;
			}
		}
		return null;
	}

	public function getAllCronJobs()
	{
		$allCronJobs = $this->cronJobs;
		if (empty($allCronJobs)) 
		{
			$allCronJobs = array();
		}

		$qb = Doctrine::em()->createQueryBuilder();
		$qb->select(array('h','c'))->from('Model_HostCronJob', 'h')->innerJoin('h.cronJob', 'c');
		$qb->where("h.aggregate = 'all nodes'");
		$qb->orWhere("h.aggregate = 'bandwidth nodes'");
		$qb->orWhere("h.aggregate = 'openwrt nodes'");
		$qb->orWhere("h.aggregate = 'tunneled nodes'");
		$hostCronJobs = $qb->getQuery()->getResult();
		foreach ($hostCronJobs as $hostCronJob)
		{
			$allCronJobs[] = $hostCronJob->cronJob;
		}
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

	public function getWiredMac()
	{
		foreach($this->interfaces as $interface)
                {
			$na_type = $interface->networkAdapter->type;
			$wired_adapter_types = array_keys(Kohana::$config->load('system.default.wired_adapter_types'));
			if (in_array($na_type, $wired_adapter_types))
			{
				return $interface->networkAdapter->mac;
			}
                }
	}


	/**
	 * @PrePersist @PreUpdate
	 */
	public function validate()
	{
	}


	public static function getByMac($mac)
	{
		$networkAdapter = Doctrine::em()->getRepository('Model_NetworkAdapter')->findOneByMac(strtolower($mac));
		if (!empty($networkAdapter))
			return $networkAdapter->node;
		return NULL;
	}

	public static function getByHostname($hostname)
	{
		return Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber(static::getBoxNumberFromHostname($hostname));
	}

	public static function getBoxNumberFromHostname($hostname)
	{
		if(substr($hostname, 0, 4) == 'node')
		{
			return substr($hostname, 4);
		}
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "Node: {$this->id}, boxNumber={$this->boxNumber}, nodeHardware={$this->nodeHardware->manufacturer} {$this->nodeHardware->model},  firmwareVersion={$this->firmwareVersion}, firmwareImage={$this->firmwareImage}, undeployable={$this->undeployable}, externalBuild={$this->externalBuild}, primaryDNSIPv4Addr={$this->primaryDNSIPv4Addr}, secondaryDNSIPv4Addr={$this->secondaryDNSIPv4Addr}, primaryDNSIPv6Addr={$this->primaryDNSIPv6Addr}, secondaryDNSIPv6Addr={$this->secondaryDNSIPv6Addr}";
		$str .= "<br/>";
		$str .= "certificate={$this->certificate}";
		$str .= "<br/>";
		$str .= "vpnEndpoint={$this->vpnEndpoint->id}";
		if (!empty($this->dnsInterface))
		{
			$str .= "dnsInterface={$this->dnsInterface->name}";
			$str .= "<br/>";
		}
		if (!empty($this->syslogServer))
                {
                        $str .= "syslogServer={$this->syslogServer->name}";
                        $str .= "<br/>";
                }
		if (!empty($this->switch))
                {
                        $str .= "switch={$this->switch->id}";
			$str .= "<br/>";
                }
		foreach($this->interfaces as $interface)
		{
			$str .= "<br/>";
			$str .= "interface={$interface}";
		}
		foreach($this->cnames as $cname)
                {
                        $str .= "<br/>";
                        $str .= "cname={$cname}";
                }
		foreach($this->nodeSetupRequests as $nodeSetupRequest)
                {
                        $str .= "<br/>";
                        $str .= "nodeSetupRequest={$nodeSetupRequest}";
                }
		if($this->currentDeployment != null)
		{
			$str .= "<br/>";
			$str .= "currentDeployment={$this->currentDeployment}";
		}
		foreach($this->nodeDeployments as $nodeDeployment)
		{
			$str .= "<br/>";
			$str .= "nodeDeployment={$nodeDeployment}";
		}
		foreach($this->notes as $node)
                {
                        $str .= "<br/>";
                        $str .= "note={$note}";
                }
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='node' id='node_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Node</th><td>{$this->id}</td></tr>";
		foreach(array('boxNumber', 'firmwareVersion', 'firmwareImage', 'undeployable', 'externalBuild', 'primaryDNSIPv4Addr', 'secondaryDNSIPv4Addr', 'primaryDNSIPv6Addr', 'secondaryDNSIPv6Addr') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		foreach(array('certificate', 'vpnEndpoint', 'dnsInterface', 'syslogServer', 'nodeHardware') as $field)
		{
			if (!empty($this->$field))
			{
				$str .= $this->fieldHTML($field, $this->$field->toHTML());
			}
		}
		foreach($this->interfaces as $interface)
		{
			$str .= $this->fieldHTML('interface', $interface->toHTML());
		}
		foreach($this->cnames as $cname)
                {
                        $str .= $this->fieldHTML('cname', $cname->toHTML());
                }
		foreach($this->nodeSetupRequests as $nodeSetupRequest)
                {
                        $str .= $this->fieldHTML('nodeSetupRequest', $nodeSetupRequest->toHTML());
                }
		if($this->currentDeployment != null)
		{
			$str .= $this->fieldHTML('currentDeployment', $this->currentDeployment->toHTML());
		}
		foreach($this->nodeDeployments as $nodeDeployment)
		{
			$str .= $this->fieldHTML('nodeDeployment', $nodeDeployment->toHTML());
		}
		foreach($this->notes as $note)
                {
                        $str .= $this->fieldHTML('note', $note->toHTML());
                }
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function getNextBoxNumber()
	{
		$n = 0;
		$repository = Doctrine::em()->getRepository('Model_Node');
		foreach($repository->findAll() as $entity)
		{
			$n = max($n, $entity->boxNumber);
		}
		return $n + 1;
	}

	public static function nonUniqueBoxNumber($boxNumber)
	{
		if (empty($boxNumber)) 
		{
			return FALSE;
		}
		$result = Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber($boxNumber);
		return empty($result->id);
	}

	public static function build($boxNumber, $nodeHardwareId, $firmwareVersion, $firmwareImage, $externalBuild, $certificate, $vpnEndpoint)
	{
		$nodeHardware = Doctrine::em()->getRepository('Model_NodeHardware')->findOneById($nodeHardwareId);
		$obj = new Model_Node();
		$obj->boxNumber = $boxNumber;
		$obj->nodeHardware = $nodeHardware;
		$nodeHardwareSwitch = $nodeHardware->switch;
		if (!empty($nodeHardwareSwitch))
		{
			$obj->switch = $nodeHardwareSwitch->cloneSwitch();
		}
		$obj->firmwareVersion = $firmwareVersion;
		$obj->firmwareImage = $firmwareImage;
		$obj->externalBuild = $externalBuild;
		$obj->certificate = $certificate;
		$obj->vpnEndpoint = $vpnEndpoint;
		$obj->dnsInterface = null;
		$obj->syslogServer = null;
		$obj->passwordHash = "";
		$obj->undeployable = 0;
		$obj->primaryDNSIPv4Addr = "";
		$obj->secondaryDNSIPv4Addr = "";
		$obj->primaryDNSIPv6Addr = "";
                $obj->secondaryDNSIPv6Addr = "";
		return $obj;
	}

	public function save()
	{
		parent::save();
		foreach($this->interfaces as $interface)
		{
			$interface->save();
		}
	}

	public function delete()
	{
		foreach($this->interfaces as $interface)
		{
			$networkAdapter = $interface->networkAdapter;
			$interface->delete();
		}
		parent::delete();
	}

	public static function getFromDeviceIP($ip)
	{
		$nip = radutmp::get_node_ip_from_device_ip($ip);
		if(!is_null($nip))
		{
			$repository = Doctrine::em()->getRepository('Model_VpnEndpoint');
			foreach($repository->findAll() as $entity)
			{
				if($entity->IPv4->encloses_address($nip))
				{
					if(!is_null($entity->nodes))
					{
						return $entity->nodes[0];
					}
				}
			}
			return null;
		}
		else
		{
			return null;
		}
	}

	public static function getUndeployedNodes()
	{
		$undeployedNodes = array();
		$latest_end_datetime = Kohana::$config->load('system.default.admin_system.latest_end_datetime');
 		$query = Doctrine::em()->createQuery("SELECT n.id, n.boxNumber FROM Model_Node n WHERE n NOT IN (SELECT n2 FROM Model_NodeDeployment nd JOIN nd.node n2 WHERE nd.endDate = '$latest_end_datetime') AND n.undeployable != 1 ORDER BY n.boxNumber"); 
		$results = $query->getResult();
		foreach ($results as $result)
		{
			$undeployedNodes[$result['id']] = $result['boxNumber'];
		}
		return $undeployedNodes;
	}

	public static function getDeployableNodes()
        {
                $deployableNodes = array();
                $latest_end_datetime = Kohana::$config->load('system.default.admin_system.latest_end_datetime');
                $query = Doctrine::em()->createQuery("SELECT n.id, n.boxNumber FROM Model_Node n WHERE n.undeployable != 1");
                $results = $query->getResult();
                foreach ($results as $result)
                {
                        $deployableNodes[$result['id']] = $result['boxNumber'];
                }
                return $deployableNodes;
        }

	public function updateCnames($cnames)
        {
		if (!empty($cnames['newCname']))
		{
                        $nc = Model_NodeCname::build($this, $cnames['newCname']);
                }
		if (isset($cnames['currentCnames']) && is_array($cnames['currentCnames']))
		{
	                foreach ($cnames['currentCnames'] as $cname)
        	        {
				if (!empty($cname['delete']))
				{
	                	        $nc = Doctrine::em()->getRepository('Model_NodeCname')->find($cname['id']);
	                        	$nc->delete();
				}
                	}
		}
        }

}
