<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
//use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_Device
 *
 * @Table(name="devices")
 * @Entity
 */
class Model_Device extends Model_Entity
{
	/**
	 * @var text $mac
	 *
	 * @Column(name="mac", type="text", nullable=false)
	 */
	protected $mac;

	/**
	 * @var Model_User
	 *
	 * @ManyToOne(targetEntity="Model_User", cascade={"persist"})
	 * @JoinColumns({
	 *   @JoinColumn(name="user_id", referencedColumnName="id")
	 * })
	 */
	protected $user;

	public function __get($name)
	{
		$this->logUse();
		switch($name)
		{
//			case "bandwidth":
//				return $this->getBandwidth();
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
//			case "bandwidth":
//				parent::__throwReadOnlyException($name);
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
		$str  = "Device: {$this->id}, user={$this->user->username}, mac={$this->mac}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='device' id='device_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Device</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('user', $this->user->username);
		$str .= $this->fieldHTML('mac');
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function getFromDeviceIP($ip)
	{
		$mac = radutmp::get_mac_from_device_ip($ip);
		if(!is_null($mac))
		{
			$obj = Doctrine::em()->getRepository('Model_Device')->findOneByMac($mac);
			if(is_null($obj))
			{
				$obj = new Model_Device();
				$obj->mac = $mac;
			}
			return $obj;
		}
		else
		{
			return null;
		}
	}
}
