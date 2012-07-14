<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Model_Node
 *
 * @Table(name="nodes")
 * @Entity
 */
class Model_Node extends Model_Entity
{
	/**
	 * @var integer $boxNumber
	 *
	 * @Column(name="box_number", type="integer", nullable=true)
	 */
	protected $boxNumber;

	/**
	 * @var text $firmwareImage
	 *
	 * @Column(name="firmware_image", type="text", nullable=false)
	 */
	protected $firmwareImage;

	/**
	 * @var text $notes
	 *
	 * @Column(name="notes", type="text", nullable=true)
	 */
	protected $notes;

	/**
	 * @var Model_Certificate
	 *
	 * @ManyToOne(targetEntity="Model_Certificate", cascade={"persist", "remove"})
	 * @JoinColumns({
	 *   @JoinColumn(name="certificate_id", referencedColumnName="id")
	 * })
	 */
	protected $certificate;

	/**
	 * @var Model_VpnEndpoint
	 *
	 * @ManyToOne(targetEntity="Model_VpnEndpoint", cascade={"persist", "remove"})
	 * @JoinColumns({
	 *   @JoinColumn(name="vpn_endpoint_id", referencedColumnName="id")
	 * })
	 */
	protected $vpnEndpoint;

	/**
	 * @OneToMany(targetEntity="Model_NodeDeployment", mappedBy="node")
	 */
	protected $deployments;

	/**
	 * @OneToMany(targetEntity="Model_Interface", mappedBy="node", cascade={"persist", "remove"})
	 */
	protected $interfaces;

	public function __construct()
	{
		parent::__construct();
		$this->deployments = new ArrayCollection();
		$this->interfaces = new ArrayCollection();
	}

	public function __get($name)
	{
		$this->logUse();
		switch($name)
		{
			case "FQDN":
				return $this->getFQDN();
			case "name":
				return $this->getName();
			case "hostname":
				return $this->getHostname();
			case "updatePoint":
				return $this->getUpdatePoint();
			case "currentDeployment":
				return $this->getCurrentDeployment();
			case "radiusSecret":
				return $this->getRadiusSecret();
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
			case "FQDN":
			case "name":
			case "hostname":
			case "updatePoint":
			case "currentDeployment":
			case "radiusSecret":
				parent::__throwReadOnlyException($name);
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

	protected function getFQDN()
	{
		return $this->hostname.'.'.Kohana::$config->load('system.default.domain');
	}

	protected function getHostname()
	{
		return 'node'.$this->boxNumber;
	}

	protected function getName()
	{
		if ($this->currentDeployment !== null)
		{
			return $this->currentDeployment->name;
		}
		else
		{
			return $this->hostname;
		}
	}

	protected function getRadiusSecret()
	{
		return $this->certificate->privateKeyFingerprint;
	}
	
	protected function getUpdatePoint()
	{
		$now2 = time();
		$near = date("Y-m-d 04:00:00");
		$now = date("Y-m-d H:i:s",$now2);

		if ($near > $now) {
			$unix = date("U",$now2);
			$day = 60 * 60 * 24;
			$unix = $unix - $day;
			return date("Y-m-d 04:00:00",$unix);
		} else {
			return $near;
		}
	}

	protected function getCurrentDeployment()
	{
		$date = time();
		foreach($this->deployments as $deployment)
		{
			if($deployment->startDate->getTimestamp() <  $date && ($deployment->endDate === null || $deployment->endDate->getTimestamp() > $date))
			{
				return $deployment;
			}
		}
		return null;
	}

	/**
	 * @PrePersist @PreUpdate
	 */
	public function validate()
	{
	}


	public static function getByMac($mac)
	{
		return Doctrine::em()->getRepository('Model_NetworkAdapter')->findOneByMac(strtolower($mac))->node;
	}

	public static function getByHostname($hostname)
	{
		return Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber(static::getBoxNumberFromHostname($hostname));
	}

	public static function getBoxNumberFromHostname($hostname)
	{
		if(substr($hostname, 0, 4) == 'node')
		{
			return substr($hostname, 4);
		}
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "Node: {$this->id}, boxNumber={$this->boxNumber}, firmwareImage={$this->firmwareImage}, notes={$this->notes}";
		$str .= "<br/>";
		$str .= "certificate={$this->certificate}";
		$str .= "<br/>";
		$str .= "vpnEndpoint={$this->vpnEndpoint}";
		foreach($this->interfaces as $interface)
		{
			$str .= "<br/>";
			$str .= "interface={$interface}";
		}
		if($this->currentDeployment != null)
		{
			$str .= "<br/>";
			$str .= "currentDeployment={$this->currentDeployment}";
		}
		foreach($this->deployments as $deployment)
		{
			$str .= "<br/>";
			$str .= "deployment={$deployment}";
		}
		return $str;
	}

	public static function getNextBoxNumber()
	{
		$n = 0;
		$repository = Doctrine::em()->getRepository('Model_Node');
		foreach($repository->findAll() as $entity)
		{
			$n = max($n, $entity->boxNumber);
		}
		return $n + 1;
	}

	public static function build($boxNumber, $firmwareImage, $notes, $certificate, $vpnEndpoint)
	{
		$obj = new Model_Node();
		$obj->boxNumber = $boxNumber;
		$obj->firmwareImage = $firmwareImage;
		$obj->notes = $notes;
		$obj->certificate = $certificate;
		$obj->vpnEndpoint = $vpnEndpoint;
		return $obj;
	}

	public function save()
	{
		parent::save();
		foreach($this->interfaces as $interface)
		{
			$interface->save();
		}
	}

	public function delete()
	{
		foreach($this->interfaces as $interface)
		{
			$networkAdapter = $interface->networkAdapter;
			$interface->delete();
		}
		parent::delete();
	}

	public static function getFromDeviceIP($ip)
	{
		$nip = radutmp::get_node_ip_from_device_ip($ip);
		if(!is_null($nip))
		{
			$repository = Doctrine::em()->getRepository('Model_VpnEndpoint');
			foreach($repository->findAll() as $entity)
			{
				if($entity->IPv4->encloses_address($nip))
				{
					if(!is_null($entity->nodes))
					{
						return $entity->nodes[0];
					}
				}
			}
			return null;
		}
		else
		{
			return null;
		}
	}
}
