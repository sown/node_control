<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_DeploymentAdmin
 *
 * @Table(name="deployment_admins")
 * @Entity
 */
class Model_DeploymentAdmin extends Model_Entity
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
	 * @var Model_Deployment
	 *
	 * @ManyToOne(targetEntity="Model_Deployment")
	 * @JoinColumns({
	 *   @JoinColumn(name="deployment_id", referencedColumnName="id")
	 * })
	 */
	protected $deployment;

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

	public static function build($deploymentId, $userId)
	{
		$deploymentAdmin = new Model_DeploymentAdmin();
		$deploymentAdmin->deployment = Doctrine::em()->getRepository('Model_Deployment')->find($deploymentId);
		$deploymentAdmin->user = Doctrine::em()->getRepository('Model_User')->find($userId);
		$deploymentAdmin->startDate = new \DateTime();
                $deploymentAdmin->endDate = new \DateTime(Kohana::$config->load('system.default.admin_system.latest_end_datetime'));
		return $deploymentAdmin;
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "DeploymentAdmin: {$this->id}, user={$this->user->username}, startDate={$this->startDate->format('Y-m-d H:i:s')}, endDate={$this->endDate->format('Y-m-d H:i:s')}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='deploymentAdmin' id='deploymentAdmin_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>DeploymentAdmin</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('date', $this->startDate->format('Y-m-d H:i:s').' - '.$this->endDate->format('Y-m-d H:i:s'));
		$str .= $this->fieldHTML('user', $this->user->username);
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}
}
