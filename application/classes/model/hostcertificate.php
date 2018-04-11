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
 * Model_HostCertificate
 *
 * @Table(name="host_certificates")
 * @Entity
 */
class Model_HostCertificate extends Model_Entity
{

	/**
         * @var Model_Server
         *
         * @ManyToOne(targetEntity="Model_Server")
         * @JoinColumns({
         *   @JoinColumn(name="server_id", referencedColumnName="id", nullable=true)
         * })
         */
        protected $server;

	/**
         * @var Model_OtherHost
         *
         * @ManyToOne(targetEntity="Model_OtherHost")
         * @JoinColumns({
         *   @JoinColumn(name="other_host_id", referencedColumnName="id", nullable=true)
         * })
         */
        protected $otherHost;
	
	/**
         * @var Model_Certificate
         *
         * @ManyToOne(targetEntity="Model_Certificate")
         * @JoinColumns({
         *   @JoinColumn(name="certificate_id", referencedColumnName="id", nullable=false)
         * })
         */
        protected $certificate;
	
	 /**
         * @var string $hostname
         *
         * @Column(name="hostname", type="string", length=255, nullable=false)
         */
        protected $hostname;	

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
		$str  = "HostCertificate: {$this->id}, server={$this->server->name} otherHost={$this->otherHost->name}, certificate={$this->certificate->id}, hostname={$this->hostname}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='host_certificate' id='host_certificate_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>HostCertiticate</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('certificate', $this->certificate->toHTML());
		$str .= $this->fieldHTML('hostname');
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($server, $otherHost, $hostname)
        {
                $hc = new Model_HostCertificate();
		$hc->server = $server;
                $hc->otherHost = $otherHost;
		$hc->hostname = $hostname;
		$certificate = Model_Certificate::build();
		$certificate->save();
		$hc->certificate = $certificate;
		$hc->save();
                return $hc;
        }

}
