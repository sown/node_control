<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
/**
 * Model_Location
 *
 * @Table(name="locations")
 * @Entity
 */
class Model_Location extends Model_Entity
{
	/**
	 * @var string $name
	 *
	 * @Column(name="name", type="string", length=255, nullable=true)
	 */
	protected $name;

	/**
         * @var string $longName
         *
         * @Column(name="long_name", type="string", length=255, nullable=true)
         */
        protected $longName;
	
	/**
	 * @var decimal $longitude
	 *
	 * @Column(name="longitude", type="decimal", nullable=true)
	 */
	protected $longitude;

	/**
	 * @var decimal $latitude
	 *
	 * @Column(name="latitude", type="decimal", nullable=true)
	 */
	protected $latitude;

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

	public static function build($name, $latitude, $longitude, $cap = 5120)
	{
		$location = new Model_Location();
		$location->name = $name;
		$location->longitude = $longitude;
		$location->latitude = $latitude;
		return $location;
	}
	public function __toString()
	{
		$this->logUse();
		$str  = "Location: {$this->id}, name={$this->name}, latitude={$this->latitude}, longitude={$this->longitude}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='location' id='location_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Location</th><td>{$this->id}</td></tr>";
		foreach(array('name', 'latitude', 'longitude') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}
}
