<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_Service
 *
 * @Table(name="services")
 * @Entity
 */
class Model_Service extends Model_Entity
{
	/**
	 * @var string $name
	 *
	 * @Column(name="name", type="string", length=255, nullable=false)
	 */
	protected $name;

	/**
	 * @var string $label
	 *
	 * @Column(name="label", type="string", length=255, nullable=false)
	 */
	protected $label;

	/**
	 * @var text $description
	 *
	 * @Column(name="description", type="string", nullable=false)
	 */
	protected $description;

	public function __get($name)
	{
		$this->logUse();
		if (property_exists($this, $name))
		{
			return $this->$name;
		}
		else
		{
			return parent::__get($name);
		}
	}

	public function __set($name, $value)
	{
		if (property_exists($this, $name))
		{
			$this->$name = $value;
		}
		else
		{
			parent::__set($name, $value);
		}
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "Service: {$this->id}, name={$this->name}, label={$this->label}, type={$this->description}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='service' id='service_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Service</th><td>{$this->id}</td></tr>";
		foreach(array('name', 'label', 'description') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($name, $label, $description)
	{
		$obj = new Model_Service();
		$obj->name = $name;
		$obj->label = $label;
		$obj->description = $description;
		return $obj;
	}

}
