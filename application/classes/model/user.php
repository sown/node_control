<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_User
 *
 * @Table(name="users")
 * @Entity
 */
class Model_User extends Model_Entity
{
	/**
	 * @var text $email
	 *
	 * @Column(name="email", type="text", nullable=false)
	 */
	protected $email;

	/**
	 * @var boolean $isSystemAdmin
	 *
	 * @Column(name="is_system_admin", type="boolean", nullable=false)
	 */
	protected $isSystemAdmin;
	
	/**
	 * @OneToMany(targetEntity="Model_NodeAdmin", mappedBy="user")
	 */
	protected $admins;

	/**
	 * @OneToMany(targetEntity="Model_Device", mappedBy="user")
	 */
	protected $devices;

	public function __get($name)
	{
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

	public function toString()
	{
		$str  = "User: {$this->id}, email={$this->email}, isSystemAdmin={$this->isSystemAdmin}";
		foreach($this->admins as $admin)
		{
			$str .= "<br/>";
			$str .= "admin={$admin->toString()}";
		}
		foreach($this->devices as $device)
		{
			$str .= "<br/>";
			$str .= "device={$device->toString()}";
		}
		return $str;
	}
}
