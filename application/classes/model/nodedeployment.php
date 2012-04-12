<?php

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
	private $name;

	/**
	 * @var boolean $isDevelopment
	 *
	 * @Column(name="is_development", type="boolean", nullable=false)
	 */
	private $isDevelopment;

	/**
	 * @var boolean $isPrivate
	 *
	 * @Column(name="is_private", type="boolean", nullable=false)
	 */
	private $isPrivate;

	/**
	 * @var boolean $firewall
	 *
	 * @Column(name="firewall", type="boolean", nullable=false)
	 */
	private $firewall;

	/**
	 * @var boolean $advancedFirewall
	 *
	 * @Column(name="advanced_firewall", type="boolean", nullable=false)
	 */
	private $advancedFirewall;

	/**
	 * @var bigint $cap
	 *
	 * @Column(name="cap", type="bigint", nullable=false)
	 */
	private $cap;

	/**
	 * @var datetime $startDate
	 *
	 * @Column(name="start_date", type="datetime", nullable=false)
	 */
	private $startDate;

	/**
	 * @var datetime $endDate
	 *
	 * @Column(name="end_date", type="datetime", nullable=false)
	 */
	private $endDate;

	/**
	 * @var integer $range
	 *
	 * @Column(name="range", type="integer", nullable=false)
	 */
	private $range;

	/**
	 * @var string $allowedPorts
	 *
	 * @Column(name="allowed_ports", type="string", length=255, nullable=true)
	 */
	private $allowedPorts;

	/**
	 * @var nodedeploymenttype $type
	 *
	 * @Column(name="type", type="nodedeploymenttype", nullable=true)
	 */
	private $type;

	/**
	 * @var text $url
	 *
	 * @Column(name="url", type="text", nullable=true)
	 */
	private $url;

	/**
	 * @var decimal $longitude
	 *
	 * @Column(name="longitude", type="decimal", nullable=true)
	 */
	private $longitude;

	/**
	 * @var decimal $latitude
	 *
	 * @Column(name="latitude", type="decimal", nullable=true)
	 */
	private $latitude;

	/**
	 * @var text $address
	 *
	 * @Column(name="address", type="text", nullable=true)
	 */
	private $address;

	/**
	 * @var Model_Node
	 *
	 * @ManyToOne(targetEntity="Model_Node")
	 * @JoinColumns({
	 *   @JoinColumn(name="node_id", referencedColumnName="id")
	 * })
	 */
	private $node;

}
