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
         * @var Model_Node
         *
         * @ManyToOne(targetEntity="Model_Node")
         * @JoinColumns({
         *   @JoinColumn(name="node_id", referencedColumnName="id")
         * })
         */
        protected $node;

	/**
         * @var Model_Deployment
         *
         * @ManyToOne(targetEntity="Model_Deployment")
         * @JoinColumns({
         *   @JoinColumn(name="deployment_id", referencedColumnName="id")
         * })
         */
        protected $deployment;

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

	public function __get($name)
	{
		$this->logUse();
		switch($name)
		{
			case "consumption":
			case "exceedsCap":
			case "privilegedDevices":
			case "privilegedUsers":
			case 'isDevelopment':
			case 'isPrivate':
			case 'firewall':
			case 'advancedFirewall':
			case 'cap':
			case 'range':
			case 'allowedPorts':
			case 'type':
			case 'url':
			case 'longitude':
			case 'latitude':
			case 'address':
			case 'admins':
				trigger_error("Deprecated property getter for $name called in ".get_class().".", E_USER_NOTICE);
				return $this->deployment->$name;
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
			case 'isDevelopment':
			case 'isPrivate':
			case 'firewall':
			case 'advancedFirewall':
			case 'cap':
			case 'range':
			case 'allowedPorts':
			case 'type':
			case 'url':
			case 'longitude':
			case 'latitude':
			case 'address':
			case 'admins':
				trigger_error("Deprecated property setter for $name called in ".get_class().".", E_USER_NOTICE);
				$this->deployment->$name = $value;
				break;
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
		$str  = "NodeDeployment: {$this->id}, startDate={$this->startDate->format('Y-m-d H:i:s')}, endDate={$this->endDate->format('Y-m-d H:i:s')}";
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
		return $str;
	}
}
