<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;

/**
 * Model_EnquiryType
 *
 * @Table(name="enquiry_types")
 * @Entity
 */
class Model_EnquiryType extends Model_Entity
{
	/**
	 * @var string $title
	 *
	 * @Column(name="title", type="string", length=255, nullable=false)
	 */
	protected $title;

	/**
	 * @var text $description
	 *
	 * @Column(name="description", type="text", nullable=true)
	 */
	protected $description;

	/**
	 * @var boolean $email
	 *
         * @Column(name="email", type="string", length=255, nullable=false)
	 */
	protected $email;

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
		$str  = "EnquiryType: {$this->id}, title={$this->title}, description={$this->description}, email={$this->email}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='enquiry_type' id='enquiry_type_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Enquiry Type</th><td>{$this->id}</td></tr>";
		foreach(array('title', 'description', 'email') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($title, $description, $email)
	{
		$obj = new Model_EnquiryType();
		$obj->title = $title;
		$obj->description = $description;
		$obj->email = $email;
		return $obj;
	}
}
