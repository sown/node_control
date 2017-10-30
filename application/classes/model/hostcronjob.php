<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * Model_HostCronJob
 *
 * @Table(name="host_cron_jobs")
 * @Entity
 */
class Model_HostCronJob extends Model_Entity
{

	/**
         * @var Model_CronJob $cronJob
         *
         * @ManyToOne(targetEntity="Model_CronJob")
         * @JoinColumns({
         *   @JoinColumn(name="cron_job_id", referencedColumnName="id", nullable=false)
         * })
         */
        protected $cronJob;

 	/**
         * @var Model_Server $server
         *
         * @ManyToOne(targetEntity="Model_Server")
         * @JoinColumns({
         *   @JoinColumn(name="server_id", referencedColumnName="id", nullable=true)
         * })
         */
        protected $server;

	 /**
         * @var Model_Node $node
         *
         * @ManyToOne(targetEntity="Model_Node")
         * @JoinColumns({
         *   @JoinColumn(name="node_id", referencedColumnName="id", nullable=true)
         * })
         */
        protected $node;

	/**
	 * @var string $aggregate
	 *
	 * @Column(name="aggregate", type="string", length=255, nullable=true)
	 */
	protected $aggregate;

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
		$str  = "HostCronJob: {$this->id}, cronJob={$this->cronJob->id}, server={$this->server->name}, node=node{$this->node->boxNumber}, aggregate={$this->aggregate}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='HostCronJob' id='HostCronJob_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Host Cron Job</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML($this->cronJob->id);
		$str .= $this->fieldHTML($this->server->name);
		$str .= $this->fieldHTML($this->node->boxNumber);
		$str .= $this->fieldHTML($this->aggregate);
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($cronJob, $onHost)
	{
		$obj = new Model_HostCronJob();
                $hostbits = explode(':', $onHost);
                switch($hostbits[0])
                {
                	case 'server':
                	        $obj->server = Doctrine::em()->getRepository('Model_Server')->find($hostbits[1]);
				break;
                        case 'aggregate':
                                $obj->aggregate = $hostbits[1];
				break;
                        case 'node':
                                $obj->node = Doctrine::em()->getRepository('Model_Node')->find($hostbits[1]);
				break;
                        default:
                                return NULL;
                }
                $obj->cronJob = $cronJob;
		return $obj;
	}
	
	public function get_host_name()
	{
		if (!empty($this->server))
                {	
			$server = $this->server;
                        Doctrine::em()->refresh($server);
                	return $this->server->name;
                }
                if (!empty($this->node))
		{
			$node = $this->node;
                        Doctrine::em()->refresh($node);
                        return "node" . $this->node->boxNumber;
		}
		if (!empty($this->aggregate))
		{
			return $this->aggregate;
		}
	}

	public function get_host_id()
	{
		if (!empty($this->server))
                {
			$server = $this->server;
			Doctrine::em()->refresh($server);
                        return "server:" . $server->id;
                }
                if (!empty($this->node))
                {
			$node = $this->node;
			Doctrine::em()->refresh($node);
			return "node:" . $node->id;
                }
                if (!empty($this->aggregate))
                {
                        return "aggregate:" . $this->aggregate;
                }
	}
}
