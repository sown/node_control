<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
/**
 * Model_HostService
 *
 * @Table(name="host_services")
 * @Entity
 */
class Model_HostService extends Model_Entity
{

	/**
         * @var Model_Server
         *
         * @ManyToOne(targetEntity="Model_Server")
         * @JoinColumns({
         *   @JoinColumn(name="server_id", referencedColumnName="id")
         * })
         */
        protected $server;

	/**
         * @var Model_OtherHost
         *
         * @ManyToOne(targetEntity="Model_OtherHost")
         * @JoinColumns({
         *   @JoinColumn(name="other_host_id", referencedColumnName="id")
         * })
         */
        protected $otherHost;
	
	/**
         * @var Model_Service
         *
         * @ManyToOne(targetEntity="Model_Service")
         * @JoinColumns({
         *   @JoinColumn(name="service_id", referencedColumnName="id")
         * })
         */
        protected $service;

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

	public function __toString()
	{
		$this->logUse();
		$str  = "ServerInterface: {$this->id}, server={$this->server->name} otherHost={$this->otherHost->name}, service={$this->service->name}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='server_interface' id='server_interface_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>HostService</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('service', $this->service->toHTML());
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($server, $otherHost, $service)
        {
                $hs = new Model_HostService();
		$hs->server = $server;
                $hs->otherHost = $otherHost;
		$hs->service = $service;
		$hs->save();
                return $hs;
        }

}
