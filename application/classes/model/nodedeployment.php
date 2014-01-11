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
			case 'capExceeded': 
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
			case 'capExceeded':
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

	public static function build($nodeId, $deploymentId)
	{
		$nodeDeployment = new Model_NodeDeployment();
		$nodeDeployment->node = Doctrine::em()->getRepository('Model_Node')->find($nodeId);
		$nodeDeployment->deployment = Doctrine::em()->getRepository('Model_Deployment')->find($deploymentId);
		$nodeDeployment->startDate = new \DateTime();
		$nodeDeployment->endDate = new \DateTime(Kohana::$config->load('system.default.admin_system.latest_end_datetime'));
		return $nodeDeployment;
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "NodeDeployment: {$this->id}, startDate={$this->startDate->format('Y-m-d H:i:s')}, endDate={$this->endDate->format('Y-m-d H:i:s')}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='nodeDeployment' id='nodeDeployment_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Node Deployment</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('date', $this->startDate->format('Y-m-d H:i:s').' - '.$this->endDate->format('Y-m-d H:i:s'));
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}
}
