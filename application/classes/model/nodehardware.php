<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_NodeHardware
 *
 * @Table(name="node_hardwares")
 * @Entity
 */
class Model_NodeHardware extends Model_Entity
{
	/**
         * @var string $manufacturer
         *
         * @Column(name="manufacturer", type="string", length=255, nullable=true)
         */
        protected $manufacturer;
	
	/**
         * @var string $model
         *
         * @Column(name="model", type="string", length=255, nullable=true)
         */
        protected $model;

	/**
         * @var string $revision
         *
         * @Column(name="revision", type="string", length=255, nullable=true)
         */
        protected $revision;

	/**
         * @var string $systemOnChip
         *
         * @Column(name="soc", type="string", length=255, nullable=true)
         */
        protected $systemOnChip;

	 /**
         * @var integer $ram
         *
         * @Column(name="ram", type="integer", nullable=true)
         */
        protected $ram;
 
        /**
         * @var integer $flash
         *
         * @Column(name="flash", type="integer", nullable=true)
         */
        protected $flash;

        /**
         * @var string $wirelessProtocols
         *
         * @Column(name="wireless_protocols", type="string", length=255, nullable=true)
         */
        protected $wirelessProtocols;

        /**
         * @var string $ethernetPorts
         *
         * @Column(name="ethernet_ports", type="string", length=255, nullable=true)
         */
        protected $ethernetPorts;

        /**
         * @var string $power
         *
         * @Column(name="power", type="string", length=255, nullable=true)
         */
        protected $power;

	/**
         * @var string $fccid
         *
         * @Column(name="fccid", type="string", length=255, nullable=true)
         */
        protected $fccid;

        /**
         * @var string $openwrtPage
         *
         * @Column(name="openwrt_page", type="string", length=255, nullable=true)
         */
        protected $openwrtPage;

        /**
         * @var developmentstatus $developmentStatus
         *
         * @Column(name="development_status", type="developmentstatus", nullable=true)
         */
	protected $developmentStatus;

        /**
         * @var Model_Switch
         *
         * @OneToOne(targetEntity="Model_Switch", cascade={"persist", "remove"})
         * @JoinColumns({
         *   @JoinColumn(name="switch_id", referencedColumnName="id")
         * })
         */
	protected $switch;


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

	public static function build($manufacturer, $model, $revision, $systemOnChip, $ram, $flash, $wirelessProtocols, $ethernetPorts, $power, $fccid, $openwrtPage, $developmentStatus)
	{
		$nodeHardware = new Model_NodeHardware();
                $nodeHardware->manufacturer = $manufacturer;
		$nodeHardware->model = $model;
		$nodeHardware->revision = $revision;
		$nodeHardware->systemOnChip = $systemOnChip;
		$nodeHardware->ram = $ram;
		$nodeHardware->flash = $flash;
		$nodeHardware->wirelessProtocols = $wirelessProtocols;
		$nodeHardware->ethernetPorts = $ethernetPorts;
		$nodeHardware->power = $power;
	 	$nodeHardware->fccid = $fccid;
		$nodeHardware->openwrtPage = $openwrtPage;
		$nodeHardware->developmentStatus = $developmentStatus;
		return $nodeHardware;
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "NodeHardware: {$this->id}, manufacturer={$this->manufacturer}, model=$this->model, revision={$this->revision}, systemOnChip={$this->systemOnChip}, ram={$this->ram}, flash={$this->flash}, wirelessProtocols={$this->wirelessProtocols}, ethernetPorts={$this->ethernetPorts}, power={$this->power},  fccid={$this->fccid}, openwrtPage={$this->openwrtPage}, developmentStatus={$this->developmentStatus}";
		if (!empty($this->switch))
		{
			$str .= "switch={$this->switch->id}";
		}
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='nodeHardware' id='nodeHardware_{$this->id}'>";
                foreach(array('manufacturer', 'model', 'revision', 'systemOnChip', 'ram', 'flash', 'wirelessProtocols', 'ethernetPorts', 'power', 'fccid', 'openwrtPage', 'developmentStatus') as $field)
                {
                        $str .= $this->fieldHTML($field);
                }
		if (!empty($this->switch))
                {
                        $str .= "";
                }

		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function getNodeHardwareOptions()
	{
		
		$nodeHardwares = Doctrine::em()->getRepository('Model_NodeHardware')->findAll();		
		$options = array();
		foreach ($nodeHardwares as $nodeHardware)
		{
			$options[$nodeHardware->id] = $nodeHardware->manufacturer . " " . $nodeHardware->model;
		}
		return $options;
	}
}
