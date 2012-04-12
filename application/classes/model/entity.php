<?php

use Doctrine\ORM\Mapping\MappedSuperClass;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/** @MappedSuperclass */
abstract class Model_Entity
{
	/**
	 * @Id @Column(type="integer")
	 * @GeneratedValue
	 */
	private $id;
	
	/** @Column(name="last_modified", type="datetime") */
	private $lastModified;
	
	public function __get($name)
	{
		//if (property_exists($this, $name))
			return $this->$name;
		//else
		//	throw new OutOfBoundsException('Class '.get_class($this).' does not have the property \''.$name.'\'.');
	}
	
	public function __set($name, $value)
	{
		//if (property_exists($this, $name))
			$this->$name = $value;
		//else
		//	throw new OutOfBoundsException('Class \''.get_class($this).'\' does not have the property \''.$name.'\'.');
	}

	protected function __throwReadOnlyException($name)
	{
		throw new RuntimeException('The property \''. $name .'\' of class \''.get_class($this).'\' is read only.');
	}

	public function __toString()
	{
		$name = "";
		try {
			$name = ' name:'.$this->name;
		} catch (OutOfBoundsException $ex) {
		}
		return '['.get_class($this).' id:'.var_export($this->id,TRUE).$name.']';
	}
}
