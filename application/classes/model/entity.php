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
	protected $id;
	
	/** @Column(name="last_modified", type="datetime") */
	protected $lastModified;

	private static $entities = array();

	public function __construct()
	{
	}
	
	public function __get($name)
	{
		$this->logUse();
		if (property_exists($this, $name))
			return $this->$name;
		else
			throw new OutOfBoundsException('Class \''.get_class($this).'\' does not have the property \''.$name.'\'.');
	}
	
	public function __set($name, $value)
	{
		if (property_exists($this, $name))
			$this->$name = $value;
		else
			throw new OutOfBoundsException('Class \''.get_class($this).'\' does not have the property \''.$name.'\'.');
	}

	protected function __throwReadOnlyException($name)
	{
		throw new RuntimeException('The property \''. $name .'\' of class \''.get_class($this).'\' is read only.');
	}

	public function __toString()
	{
		$this->logUse();
		$name = "";
		try {
			$name = ' name:'.$this->name;
		} catch (OutOfBoundsException $ex) {
		}
		return '['.get_class($this).' id:'.var_export($this->id,TRUE).$name.']';
	}

	public function fieldHTML($field, $value = null)
	{
		if(is_null($value))
		{
			$value = $this->$field;
		}
		if(trim($value) == '')
		{
			return "<tr class='empty'><th>$field</th><td>$value</td></tr>";
		}
		else
		{
			return "<tr><th>$field</th><td>$value</td></tr>";
		}
	}

	public function logUse()
	{
		Model_Entity::add_entity($this);
	}

	public static function add_entity($entity)
	{
		Model_Entity::$entities[] = $entity;
	}

	public static function reset_entities()
	{
		Model_Entity::$entities = array();
	}

	public static function get_entities()
	{
		return Model_Entity::$entities;
	}

	public function save()
	{
		Doctrine::em()->persist($this);
		Doctrine::em()->flush();
	}

	public function delete()
	{
		Doctrine::em()->remove($this);
		Doctrine::em()->flush();
	}

	public function latest_note() 
	{
		$note_unixtime = 0;
		$latest_note = null;
		foreach ($this->notes as $note) {
			if ($note_unixtime < $note->createdAt->format('U')) {
				$latest_note = $note;
			}
		}
		return $latest_note;
	}


}
