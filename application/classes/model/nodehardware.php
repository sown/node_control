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
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='nodeHardware' id='nodeHardware_{$this->id}'>";
		$str .= "<table>";
                $str .= $this->fieldHTML('manufacturer', $this->$manufacturer->toHTML());
		$str .= $this->fieldHTML('model', $this->$model->toHTML());
		$str .= $this->fieldHTML('revision', $this->$revision->toHTML());
		$str .= $this->fieldHTML('systemOnChip', $this->$systemOnChip->toHTML());
		$str .= $this->fieldHTML('ram', $this->$ram->toHTML());
		$str .= $this->fieldHTML('flash', $this->$flash->toHTML());
		$str .= $this->fieldHTML('wirelessProtocols', $this->$wirelessProtocols->toHTML());
		$str .= $this->fieldHTML('ethernetPorts', $this->$ethernetPorts->toHTML());
		$str .= $this->fieldHTML('power', $this->$power->toHTML());
		$str .= $this->fieldHTML('fccid', $this->$fccid->toHTML());
		$str .= $this->fieldHTML('openwrtPage', $this->$openwrtPage->toHTML());
		$str .= $this->fieldHTML('developmentStatus', $this->$developmentStatus->toHTML());
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}
}
