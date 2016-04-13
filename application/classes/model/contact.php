<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * Model_Contact
 *
 * @Table(name="contacts")
 * @Entity
 */
class Model_Contact extends Model_Entity
{
	/**
	 * @var string $name
	 *
	 * @Column(name="name", type="string", length=255, nullable=true)
	 */
	protected $name;

	/**
         * @var string $email
         *
         * @Column(name="email", type="string", length=255, nullable=true)
         */
        protected $email;


	/**
         * @var Model_Server $server
         *
         * @ManyToOne(targetEntity="Model_Server")
         * @JoinColumns({
         *   @JoinColumn(name="server_id", referencedColumnName="id")
         * })
         */
        protected $server;	

	/**
         * @var Model_OtherHost $otherHost
         *
         * @ManyToOne(targetEntity="Model_OtherHost")
         * @JoinColumns({
         *   @JoinColumn(name="other_host_id", referencedColumnName="id")
         * })
         */
        protected $otherHost;


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
		$str = "Contact: {$this->id}, name={$this->name}, email={$this->email}, ";
		if (isset($this->server->id))
		{
			$str .= "server={$this->server->name}";
		}
		else
		{
		 	$str .= "otherHost={$this->otherHost->name}";
		}
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='note' id='contact_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Contact</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('name', $this->name);
		$str .= $this->fieldHTML('email', $this->email);
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($entityType, $entity, $name, $email)
        {
                $obj = new Model_Contact();
		switch ($entityType) 
		{
			case 'Server':
				$obj->server = $entity;
				break;
			case 'OtherHost':
                                $obj->otherHost = $entity;
                                break;
			default:
		}
                $obj->name = $name;
		$obj->email = $email;
                return $obj;
        }
}
