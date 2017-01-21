<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_Deployment
 *
 * @Table(name="deployments")
 * @Entity(repositoryClass="Model_Repository_Deployment")
 */
class Model_Deployment extends Model_Entity
{
	/**
	 * @var string $name
	 *
	 * @Column(name="name", type="string", length=255, nullable=true)
	 */
	protected $name;

	/**
         * @var string $nfsenName
         *
         * @Column(name="nfsen_name", type="string", length=255, nullable=true)
         */
        protected $nfsenName;

	/**
	 * @var boolean $isDevelopment
	 *
	 * @Column(name="is_development", type="boolean", nullable=false)
	 */
	protected $isDevelopment;

	/**
	 * @var boolean $isPrivate
	 *
	 * @Column(name="is_private", type="boolean", nullable=false)
	 */
	protected $isPrivate;

	/**
	 * @var boolean $firewall
	 *
	 * @Column(name="firewall", type="boolean", nullable=false)
	 */
	protected $firewall;

	/**
	 * @var boolean $advancedFirewall
	 *
	 * @Column(name="advanced_firewall", type="boolean", nullable=false)
	 */
	protected $advancedFirewall;

	/**
	 * @var bigint $cap
	 *
	 * @Column(name="cap", type="bigint", nullable=false)
	 */
	protected $cap;

	/**
         * @var boolean $capExceeded
         *
         * @Column(name="cap_exceeded", type="boolean", nullable=false)
         */
        protected $capExceeded; // This is the current database value for whether the node deployment is over its cap.  This gets updated by the nodeexceedscap check and causes the node_deployment records last_modified to change so the node will pull down the new node config.

	/**
         * @var int $pingAttempts
         *
         * @Column(name="ping_attempts", type="integer", nullable=false)
         */
        protected $pingAttempts;

	/**
         * @var int $wifiDownAfter
         *
         * @Column(name="wifi_down_after", type="integer", nullable=false)
         */
        protected $wifiDownAfter;

	/**
	 * @var datetime $startDate
	 *
	 * @Column(name="start_date", type="datetime", nullable=false)
	 */
	protected $startDate;

	/**
	 * @var datetime $endDate
	 *
	 * @Column(name="end_date", type="datetime", nullable=false)
	 */
	protected $endDate;

	/**
	 * @var integer $range
	 *
	 * @Column(name="radius", type="integer", nullable=false)
	 */
	protected $range;

	/**
	 * @var string $allowedPorts
	 *
	 * @Column(name="allowed_ports", type="string", length=255, nullable=true)
	 */
	protected $allowedPorts;

	/**
	 * @var deploymenttype $type
	 *
	 * @Column(name="type", type="deploymenttype", nullable=true)
	 */
	protected $type;

	/**
	 * @var text $url
	 *
	 * @Column(name="url", type="text", nullable=true)
	 */
	protected $url;

	/**
	 * @var decimal $longitude
	 *
	 * @Column(name="longitude", type="decimal", nullable=true)
	 */
	protected $longitude;

	/**
	 * @var decimal $latitude
	 *
	 * @Column(name="latitude", type="decimal", nullable=true)
	 */
	protected $latitude;

	/**
	 * @var text $address
	 *
	 * @Column(name="address", type="text", nullable=true)
	 */
	protected $address;

	/**
	 * @OneToMany(targetEntity="Model_DeploymentAdmin", mappedBy="deployment", cascade={"persist", "remove"})
	 */
	protected $admins;

	 /**
         * @OneToMany(targetEntity="Model_NodeDeployment", mappedBy="deployment", cascade={"persist", "remove"})
         */
        protected $nodeDeployments;

	/**
         * @OneToMany(targetEntity="Model_Note", mappedBy="deployment", cascade={"persist", "remove"})
         */
        protected $notes;

	
	public function __get($name)
	{
		$this->logUse();
		switch($name)
		{
			case "consumption":
				return $this->getConsumption();
			case "exceedsCap":
				return $this->getExceedsCap();
			case "privilegedDevices":
				return $this->getPrivilegedDevices();
			case "privilegedUsers":
				return $this->getPrivilegedUsers();
			case "currentAdmins":
				return $this->getCurrentAdmins();
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
			case "consumption":
			case "exceedsCap":
			case "privilegedDevices":
			case "privilegedUsers":
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

	public function getConsumption() 
	{
		$path = Kohana::$config->load('system.default.rrd.deployment_path'); 
		if (substr($path,-1) != "/") 
		{
			$path .= "/";
		}

		$usage = 0;
		foreach($this->nodeDeployments as $nodeDeployment)
		{
			$rrd_file = $path .  "node_deployment" . $nodeDeployment->id . ".rrd";
			$usage += RadAcctUtils::getBandwidthUsage($rrd_file,30)/1024/1024;
		}
		return $usage;
	}

	public function getExceedsCap()
	{
		return $this->cap > 0 && $this->consumption > $this->cap;
	}

	public function getPrivilegedUsers()
	{
		$users = array();
		foreach($this->admins as $admin)
		{
			$users[] = $admin->user;
		}
		return $users;
	}

	public function getCurrentAdmins()
	{
		$currentAdmins = array();
		foreach($this->admins as $admin)
                {
                        if ($admin->endDate->getTimestamp() > time())
			{
                        	$currentAdmins[] = $admin;       
                        }
                }
		return $currentAdmins;
	}

	public function hasCurrentDeploymentAdmin($user_id)
	{	
		foreach($this->admins as $admin)
		{
			if ($admin->user->id == $user_id)
			{
				if ($admin->endDate->getTimestamp() > time())
					return TRUE;
				return FALSE;
			}
                }
		return FALSE;
	}

	public function getPrivilegedDevices()
	{
		$devices = array();
		foreach($this->privilegedUsers as $user)
		{
			foreach($user->devices as $device)
			{
				$devices[] = $device;
			}
		}
		return $devices;
	}

	public function getCurrentNodeDeployment()
	{
		foreach ($this->nodeDeployments as $nodeDeployment) 
		{
			if ($nodeDeployment->endDate->getTimestamp() > time())
			{
				return $nodeDeployment;
			}
		}
	}

	public function getLastNodeDeployment()
	{
		$latest_time = 0;
		$lastNodeDeployment = null;
		foreach ($this->nodeDeployments as $nodeDeployment)
                {
                        if ($nodeDeployment->endDate->getTimestamp() > $latest_time)
                        {
                                $lastNodeDeployment = $nodeDeployment;
                        }
                }
		return $lastNodeDeployment;
	}

	public static function build($name, $latitude, $longitude, $cap = 5120)
	{
		$deployment = new Model_Deployment();
		$deployment->name = $name;
		$deployment->nfsenName = Model_Deployment::getUniqueNfsenName($name);
		$deployment->isDevelopment = 0;
		$deployment->isPrivate = 0;
		$deployment->firewall = 0;
		$deployment->advancedFirewall = 0;
		$deployment->longitude = $longitude;
		$deployment->latitude = $latitude;
		$deployment->range = 20;
		$deployment->cap = $cap;
		$deployment->capExceeded = False;
		$deployment->pingAttempts = 4;
		$deployment->wifiDownAfter = 1;
		$deployment->type = 'home';
		$deployment->startDate = new \DateTime();
		$deployment->endDate = new \DateTime(Kohana::$config->load('system.default.admin_system.latest_end_datetime'));
		return $deployment;
	}
	public function __toString()
	{
		$this->logUse();
		$str  = "Deployment: {$this->id}, name={$this->name}, nfsenName={$this->nfsenName}, isDevelopment={$this->isDevelopment}, isPrivate={$this->isPrivate}, firewall={$this->firewall}, advancedFirewall={$this->advancedFirewall}, cap={$this->cap}, pingAttempts={$this->pingAttempts}, wifiDownAfter={$this->wifiDownAfter}, startDate={$this->startDate->format('Y-m-d H:i:s')}, endDate={$this->endDate->format('Y-m-d H:i:s')}, range={$this->range}, allowedPorts={$this->allowedPorts}, type={$this->type}, url={$this->url}, latitude={$this->latitude}, longitude={$this->longitude}, address={$this->address}, consumption={$this->consumption}, exceedsCap={$this->exceedsCap}";
		foreach($this->admins as $admin)
		{
			$str .= "<br/>";
			$str .= "admin={$admin}";
		}
		foreach($this->privilegedDevices as $device)
		{
			$str .= "<br/>";
			$str .= "privilegedDevice={$device}";
		}
		foreach($this->notes as $note)
                {
                        $str .= "<br/>";
                        $str .= "note={$note}";
                }
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='deployment' id='deployment_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Deployment</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('date', $this->startDate->format('Y-m-d H:i:s').' - '.$this->endDate->format('Y-m-d H:i:s'));
		foreach(array('name', 'nfsenName', 'isDevelopment', 'isPrivate', 'firewall', 'advancedFirewall', 'cap', 'pingAttempts', 'wifiDownAfter', 'range', 'allowedPorts', 'type', 'url', 'latitude', 'longitude', 'address', 'consumption', 'exceedsCap') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		foreach($this->admins as $admin)
		{
			$str .= $this->fieldHTML('admin', $admin->toHTML());
		}
		foreach($this->privilegedDevices as $device)
		{
			$str .= $this->fieldHTML('privilegedDevice', $device->toHTML());
		}
		foreach($this->notes as $note)
                {
                        $str .= $this->fieldHTML('note', $note->toHTML());
                }
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function getUniqueNfsenName($name, $id = 0)
        {
		$nfsen_name = substr(str_replace(' ', '_', $name), 0, 19);
		$use_nfsen_name = $nfsen_name;
		$unique = FALSE;
		$i = 0;
		while ($unique == FALSE && $i < 100)
		{
                	$result = Doctrine::em()->getRepository('Model_Deployment')->findOneByNfsenName($use_nfsen_name);
			if (empty($result->id) || $result->id == $id)
			{
				return $use_nfsen_name;
			}
			$i++;
			$over = strlen($nfsen_name . $i) - 19;
			$use_nfsen_name = ($over > 0 ? substr($nfsen_name, 0, -$over) . $i : $nfsen_name . $i);
                }
		// Should never happen unless we have a lot of deployments with the same name
		die("ERROR: Could not get unique name for NFSEN.");
        }
}
