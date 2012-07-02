<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_NodeDeployment
 *
 * @Table(name="node_deployments")
 * @Entity(repositoryClass="Model_Repository_NodeDeployment")
 */
class Model_NodeDeployment extends Model_Entity
{
	/**
	 * @var string $name
	 *
	 * @Column(name="name", type="string", length=255, nullable=true)
	 */
	protected $name;

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
	 * @Column(name="range", type="integer", nullable=false)
	 */
	protected $range;

	/**
	 * @var string $allowedPorts
	 *
	 * @Column(name="allowed_ports", type="string", length=255, nullable=true)
	 */
	protected $allowedPorts;

	/**
	 * @var nodedeploymenttype $type
	 *
	 * @Column(name="type", type="nodedeploymenttype", nullable=true)
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
	 * @var Model_Node
	 *
	 * @ManyToOne(targetEntity="Model_Node")
	 * @JoinColumns({
	 *   @JoinColumn(name="node_id", referencedColumnName="id")
	 * })
	 */
	protected $node;

	/**
	 * @OneToMany(targetEntity="Model_NodeAdmin", mappedBy="nodeDeployment")
	 */
	protected $admins;
	
	public function __get($name)
	{
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
		$path = Kohana::$config->load('system.default.rrd_deployment_path'); 
		if (substr($path,-1) != "/") 
		{
			$path .= "/";
		}

		$rrd_file = $path .  "deployment" . $this->id;
		
		/* TODO LINK THIS TO /src/www/radacct-tg/functions.php */

		# return get_bandwidth_usage($rrd_file,30);

		return 0;
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

	public function toString()
	{
		$str  = "NodeDeployment: {$this->id}, name={$this->name}, isDevelopment={$this->isDevelopment}, isPrivate={$this->isPrivate}, firewall={$this->firewall}, advancedFirewall={$this->advancedFirewall}, cap={$this->cap}, startDate={$this->startDate->format('Y-m-d H:i:s')}, endDate={$this->endDate->format('Y-m-d H:i:s')}, range={$this->range}, allowedPorts={$this->allowedPorts}, type={$this->type}, url={$this->url}, latitude={$this->latitude}, longitude={$this->longitude}, address={$this->address}, consumption={$this->consumption}, exceedsCap={$this->exceedsCap}";
		foreach($this->admins as $admin)
		{
			$str .= "<br/>";
			$str .= "admin={$admin->toString()}";
		}
		foreach($this->privilegedDevices as $device)
		{
			$str .= "<br/>";
			$str .= "privilegedDevice={$device->toString()}";
		}
		return $str;
	}
}
