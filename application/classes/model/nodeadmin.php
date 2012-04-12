<?php

/**
 * Model_NodeAdmin
 *
 * @Table(name="node_admins")
 * @Entity
 */
class Model_NodeAdmin extends Model_Entity
{
	/**
	 * @var integer $userId
	 *
	 * @Column(name="user_id", type="integer", nullable=false)
	 */
	private $userId;

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
	 * @var Model_NodeDeployment
	 *
	 * @ManyToOne(targetEntity="Model_NodeDeployment")
	 * @JoinColumns({
	 *   @JoinColumn(name="node_deployment_id", referencedColumnName="id")
	 * })
	 */
	private $nodeDeployment;

}
