<?php

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
/**
 * Model_Radpostauth
 *
 * @Table(name="radius.radpostauth")
 * @Entity
 */
class Model_Radpostauth
{

	protected $_db = 'radius';
	
        /**
         * @Id @Column(type="integer")
         * @GeneratedValue
         */
	protected $id;

	/**
	 * @var string $username
	 *
	 * @Column(name="username", type="string", length=64, nullable=false)
	 */
	protected $username;

	/**
         * @var string $pass
         *
         * @Column(name="pass", type="string", length=64, nullable=false)
         */
        protected $pass;

	/**
         * @var string $reply
         *
         * @Column(name="reply", type="string", length=32, nullable=false)
         */
        protected $reply;

	/**
         * @var string $authdate
         *
         * @Column(name="authdate", type="datetime", nullable=false)
         */
        protected $authdate;

 	public function __construct()
        {
        }

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
					return "ERROR: $name";
				}
		}
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
		$str  = "Radpostauth: {$this->id}, username={$this->username}, pass={$this->pass}, reply={$this->reply}, authdate={$this->authdate->format('Y-m-d H:i:s')}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='radpostauth' id='radpostauth_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Radpostauth</th><td>{$this->id}</td></tr>";
		$fields = array('username', 'pass', 'reply', 'authdate');
		foreach($fields as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
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

}
