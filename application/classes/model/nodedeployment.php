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

	public function toString()
	{
		$str  = "NodeDeployment: {$this->id}, name={$this->name}, isDevelopment={$this->isDevelopment}, isPrivate={$this->isPrivate}, firewall={$this->firewall}, advancedFirewall={$this->advancedFirewall}, cap={$this->cap}, startDate={$this->startDate->format('Y-m-d H:i:s')}, endDate={$this->endDate->format('Y-m-d H:i:s')}, range={$this->range}, allowedPorts={$this->allowedPorts}, type={$this->type}, url={$this->url}, latitude={$this->latitude}, longitude={$this->longitude}, address={$this->address}";
		foreach($this->admins as $admin)
		{
			$str .= "<br/>";
			$str .= "admin={$admin->toString()}";
		}
		return $str;
	}
}
