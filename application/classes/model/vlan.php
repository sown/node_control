<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
/**
 * Model_Vlan
 *
 * @Table(name="vlans")
 * @Entity
 */
class Model_Vlan extends Model_Entity
{

	/**
         * @var string $name
         *
         * @Column(name="name", type="string", length=20, nullable=true)
         */
        protected $name;

	 /**
         * @var string $prefix
         *
         * @Column(name="prefix", type="string", length=120, nullable=true)
         */
        protected $prefix;


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
		$str  = "Vlan: {$this->id}, name={$this->name}, prefix={$this->prefix}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='vlan' id='vlan_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Vlan</th><td>{$this->id}</td></tr>";
		foreach(array('name', 'prefix') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($name)
        {
                $vlan = new Model_Vlan();
		$vlan->name = $name;
		$vlan->save();
                return $vlan;
        }

}
