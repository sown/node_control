<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_NodeAdmin
 *
 * @Table(name="node_admins")
 * @Entity
 */
class Model_NodeAdmin extends Model_Entity
{
	/**
	 * @var Model_User
	 *
	 * @ManyToOne(targetEntity="Model_User", cascade={"persist"})
	 * @JoinColumns({
	 *   @JoinColumn(name="user_id", referencedColumnName="id")
	 * })
	 */
	protected $user;

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
	 * @var Model_NodeDeployment
	 *
	 * @ManyToOne(targetEntity="Model_NodeDeployment", cascade={"persist"})
	 * @JoinColumns({
	 *   @JoinColumn(name="node_deployment_id", referencedColumnName="id")
	 * })
	 */
	protected $nodeDeployment;

	public function __toString()
	{
		$this->logUse();
		$str  = "NodeAdmin: {$this->id}, user={$this->user->email}, startDate={$this->startDate->format('Y-m-d H:i:s')}, endDate={$this->endDate->format('Y-m-d H:i:s')}";
		return $str;
	}
}
