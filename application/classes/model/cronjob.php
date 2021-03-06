<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * Model_CronJob
 *
 * @Table(name="cron_jobs")
 * @Entity
 */
class Model_CronJob extends Model_Entity
{
	/**
         * @var string $creator
         *
         * @Column(name="creator", type="string", length=255, nullable=false)
         */
        protected $creator;

	/**
         * @var string $username
         *
         * @Column(name="username", type="string", length=255, nullable=false)
         */
        protected $username;

	/**
         * @var string $command
         *
         * @Column(name="command", type="string", length=255, nullable=false)
         */
        protected $command;

	/**
         * @var string $description
         *
         * @Column(name="description", type="string", length=255, nullable=false)
         */
        protected $description;

	/**
         * @var string $misc
         *
         * @Column(name="misc", type="string", length=8191, nullable=true)
         */
        protected $misc;

	/**
         * @var integer $disabled
         *
         * @Column(name="disabled", type="integer", nullable=false)
         */
        protected $disabled;

	/**
         * @var integer $required
         *
         * @Column(name="required", type="integer", nullable=true)
         */
        protected $required;

	/**
         * @var datetime $createdAt
         *
         * @Column(name="created_at", type="datetime", nullable=false)
         */
        protected $createdAt;

 	/**
         * @var datetime $updatedAt
         *
         * @Column(name="updated_at", type="datetime", nullable=false)
         */
        protected $updatedAt;

	/**
         * @OneToMany(targetEntity="Model_HostCronJob", mappedBy="cronJob", cascade={"persist", "remove"})
         */
        protected $onHosts;

	// Hack to support different links for OnHosts in Cron Jobs table
	protected $onHostsEnabled;


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

	public function __set($name, $value)
	{
		switch($name)
		{
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
		$str  = "CronJob: {$this->id}, onHosts='{$this->onHostsList()}', creator={$this->creator}, username={$this->username}, command={$this->command}, description={$this->description}, misc={$this->misc}, disabled={$this->disabled}, required={$this->required}, createdAt={$this->createdAt}, updatedAt={$this->updatedAt}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='CronJob' id='CronJob_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Cron Job</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML("onHosts", $this->onHostsList());
		foreach(array('creator', 'username', 'command', 'description', 'misc', 'disabled', 'required', 'createdAt', 'updatedAt') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	protected function onHostsList()
	{
		foreach ($this->onHosts as $hostCronJob)
                {
                        $hosts[] = $hostCronJob->get_host_name();
                }
		return implode(", ", $hosts);
	}

	public static function build($description, $username, $onHosts, $creator, $command, $disabled, $required, $misc)
	{
		$obj = new Model_CronJob();
		$obj->creator = $creator;
		$obj->username = $username;
		$obj->command = $command;
		$obj->description = $description;
		$obj->disabled = $disabled;
		$obj->required = $required;
		$obj->misc = $misc;
		$obj->createdAt = new \DateTime();
		$obj->updatedAt = new \DateTime();
		$obj->save();
		foreach ($onHosts as $onHost)
		{
			$obj2 = Model_HostCronJob::build($obj, $onHost);
			$obj2->save();
		}
		return Doctrine::em()->getRepository('Model_CronJob')->find($obj->id);
	}
	
}
