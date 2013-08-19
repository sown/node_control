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
 * Model_Radacct
 *
 * @Table(name="radius.radacct")
 * @Entity
 */
class Model_Radacct
{

	protected $_db = 'radius';
	protected $_primary_key = 'radacctid';
	
        /**
         * @Id @Column(type="integer")
         * @GeneratedValue
         */
	protected $radacctid;

	/**
	 * @var string $acctsessionid
	 *
	 * @Column(name="acctsessionid", type="string", length=64, nullable=false)
	 */
	protected $acctsessionid;

	/**
         * @var string $acctuniqueid
         *
         * @Column(name="acctuniqueid", type="string", length=32, nullable=false)
         */
        protected $acctuniqueid;

	/**
         * @var string $username
         *
         * @Column(name="username", type="string", length=64, nullable=false)
         */
        protected $username;

	/**
         * @var string $groupname
         *
         * @Column(name="groupname", type="string", length=64, nullable=false)
         */
        protected $groupname;

	/**
         * @var string $realm
         *
         * @Column(name="realm", type="string", length=64, nullable=true)
         */
        protected $realm;

	/**
         * @var string $nasipaddress
         *
         * @Column(name="nasipaddress", type="string", length=15, nullable=false)
         */
        protected $nasipaddress;
	
	/**
         * @var string $nasportid
         *
         * @Column(name="nasportid", type="string", length=15, nullable=true)
         */
        protected $nasportid;
	
	/**
         * @var string $nasporttype
         *
         * @Column(name="nasporttype", type="string", length=32, nullable=true)
         */
        protected $nasporttype;

	/**
         * @var datetime $acctstarttime
         *
         * @Column(name="acctstarttime", type="datetime", nullable=true)
         */
        public $acctstarttime;

	/**
         * @var datetime $acctstoptime
         *
         * @Column(name="acctstoptime", type="datetime", nullable=true)
         */
        public $acctstoptime;

	/**
         * @var integer $acctsessiontime
         *
         * @Column(name="acctsessiontime", type="integer", nullable=true)
         */
        protected $acctsessiontime;

        /**
         * @var string $acctauthentic
         *
         * @Column(name="acctauthentic", type="string", length=32, nullable=true)
         */
        protected $acctauthentic;

	/**
         * @var string $connectinfo_start
         *
         * @Column(name="connectinfo_start", type="string", length=50, nullable=true)
         */
        protected $connectinfo_start;

	/**
         * @var string $connectinfo_stop
         *
         * @Column(name="connectinfo_stop", type="string", length=50, nullable=true)
         */
        protected $connectinfo_stop;

	/**
         * @var integer $acctinputoctets
         *
         * @Column(name="acctinputoctets", type="integer", nullable=true)
         */
        protected $acctinputoctets;

        /**
         * @var integer $acctoutputoctets
         *
         * @Column(name="acctoutputoctets", type="integer", nullable=true)
         */
        protected $acctoutputoctets;

	/**
         * @var string $calledstationid
         *
         * @Column(name="calledstationid", type="string", length=50, nullable=false)
         */
        protected $calledstationid;

	/**
         * @var string $callingstationid
         *
         * @Column(name="callingstationid", type="string", length=50, nullable=false)
         */
        protected $callingstationid;

	/**
         * @var string $acctterminatecause
         *
         * @Column(name="acctterminatecause", type="string", length=32, nullable=false)
         */
        protected $acctterminatecause;

	/**
         * @var string $servicetype
         *
         * @Column(name="servicetype", type="string", length=32, nullable=true)
         */
        protected $servicetype;

	/**
         * @var string $framedprotocol
         *
         * @Column(name="framedprotocol", type="string", length=32, nullable=true)
         */
        protected $framedprotocol;

	/**
         * @var string $framedipaddress
         *
         * @Column(name="framedipaddress", type="string", length=15, nullable=false)
         */
        protected $framedipaddress;

	/**
         * @var integer $acctstartdelay
         *
         * @Column(name="acctstartdelay", type="integer", nullable=true)
         */
        protected $acctstartdelay;

	/**
         * @var integer $acctstopdelay
         *
         * @Column(name="acctstopdelay", type="integer", nullable=true)
         */
        protected $acctstopdelay;

	/**
         * @var string $xascendsessionsvrkey
         *
         * @Column(name="xascendsessionsvrkey", type="string", length=10, nullable=true)
         */
        protected $xascendsessionsvrkey;

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
		$str  = "Radacct: {$this->radacctid}, acctsessionid={$this->acctsessionid}, acctuniqueid={$this->acctuniqueid}, username={$this->username}, groupname={$this->groupname}, realm={$this->realm}, nasipaddress={$this->nasipaddress}, nasportid={$this->nasportid}, nasporttype={$this->nasporttype}, acctstarttime={$this->acctstarttime}, acctstoptime={$this->acctstoptime}, acctsessiontime={$this->acctsessiontime}, acctauthentic={$this->acctauthentic}, connectinfo_start={$this->connectinfo_start}, connectinfo_stop={$this->connectinfo_stop}, acctinputoctets={$this->acctinputoctets}, acctoutputoctets={$this->acctoutputoctets}, calledstationid={$this->calledstationid}, callingstationid={$this->callingstationid}, acctterminatecause={$this->acctterminatecause}, servicetype={$this->servicetype}, framedprotocol={$this->framedprotocol}, framedipaddress={$this->framedipaddress}, acctstartdelay={$this->acctstartdelay}, acctstopdelay={$this->acctstopdelay}, xascendsessionsvrkey={$this->xascendsessionsvrkey}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='radacct' id='radacct_{$this->radacctid}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Radacct</th><td>{$this->radacctid}</td></tr>";
		$fields = array('acctsessionid', 'acctuniqueid', 'username', 'groupname', 'realm', 'nasipaddress', 'nasportid', 'nasporttype', 'acctstarttime', 'acctstoptime', 'acctstoptime', 'acctsessiontime', 'acctauthentic', 'connectinfo_start', 'connectinfo_stop', 'acctinputoctets', 'acctoutputoctets', 'calledstationid',' callingstationid', 'acctterminatecause', 'acctterminatecause', 'servicetype', 'framedprotocol', 'framedipaddress', 'acctstartdelay', 'acctstopdelay', 'xascendsessionsvrkey');	
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
