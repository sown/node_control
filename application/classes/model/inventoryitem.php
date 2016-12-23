<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_InventoryItem
 *
 * @Table(name="inventory")
 * @Entity(repositoryClass="Model_Repository_InventoryItem")
 */
class Model_InventoryItem extends Model_Entity
{
	/**
         * @var string $uniqueIdentifier
         *
         * @Column(name="uid", type="string", length=255, nullable=true)
         */
        protected $uniqueIdentifier;
	
	/**
         * @var string $type
         *
         * @Column(name="type", type="string", length=255, nullable=true)
         */
        protected $type;

	/**
         * @var string $model
         *
         * @Column(name="model", type="string", length=255, nullable=true)
         */
        protected $model;

	/**
	 * @var datetime $writtenOffDate
	 *
	 * @Column(name="written_off", type="datetime", nullable=false)
	 */
	protected $writtenOffDate;

	/**
         * @var string $description
         *
         * @Column(name="hardware_desc", type="text", nullable=false)
         */
        protected $description;

	 /**
         * @var string $price
         *
         * @Column(name="price", type="string", length=24, nullable=true)
         */
        protected $price;

	/**
         * @var string $location
         *
         * @Column(name="location", type="string", length=255, nullable=false)
         */
        protected $location = "PENDING";
	
	/** 
         * @var blob $photo
         * 
         * @Column(name="photo", type="blob")
         */
        protected $photo;

	/**
         * @var string $wikiLink
         *
         * @Column(name="link_to_wiki", type="string", length=235, nullable=false)
         */   
        protected $wikiLink;

        /**
         * @var string $addedBy
         *
         * @Column(name="added_by", type="string", length=235, nullable=false)
         */
        protected $addedBy;

	/**
         * @var datetime $acquiredDate
         *
         * @Column(name="purchased_on", type="datetime", nullable=false)
         */
        protected $acquiredDate;

        /**
         * @var string $state
         *
         * @Column(name="state", type="string", length=255, nullable=true)
         */
        protected $state;

	/**
         * @var string $architecture
         *
         * @Column(name="architecture", type="string", length=255, nullable=true)
         */
        protected $architecture;

        /**
         * @OneToMany(targetEntity="Model_Note", mappedBy="inventoryItem", cascade={"persist", "remove"})
         */
        protected $notes;


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

	public static function build($uniqueIdentifier, $type, $model, $description, $location, $price, $wikiLink, $addedBy, $state, $architecture)
	{
		$inventoryItem = new Model_InventoryItem();
		$inventoryItem->uniqueIdentifier = $uniqueIdentifier;
		$inventoryItem->type = $type;
		$inventoryItem->model = $model;
		$inventoryItem->description = $description;
		$inventoryItem->location = $location;
		$inventoryItem->price = $price;
		$inventoryItem->wikiLink = $wikiLink;
		$inventoryItem->addedBy = $addedBy;
		$inventoryItem->state = $state;
		$inventoryItem->architecture = $architecture;
		$inventoryItem->writtenOffDate = new \DateTime(Kohana::$config->load('system.default.admin_system.latest_end_datetime'));
		$inventoryItem->acquiredDate = new \DateTime();
		return $inventoryItem;
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "InventoryItem: {$this->id}, uniqueIdentifier={$this->uniqueIdentifier}, type={$this->type}, model=$this->model, {$this->writtenOffDate->format('Y-m-d H:i:s')}, description={$this->description}, price={$this->price}, location={$this->location}, wikiLink={$this->wikiLink}, addedBy={$this->addedBy} acquiredDate={$this->acquiredDate->format('Y-m-d H:i:s')}, state={$this->state}, architecture={$this->architecture}";
		foreach($this->notes as $note)
                {
                        $str .= "<br/>";
                        $str .= "note={$note}";
                }
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='inventoryItem' id='inventoryItem_{$this->id}'>";
		$str .= "<table>";
                $str .= $this->fieldHTML('uniqueIdentifier', $this->uniqueIdentifier->toHTML());
		$str .= $this->fieldHTML('type', $this->type->toHTML());
		$str .= $this->fieldHTML('model', $this->model->toHTML());
		$str .= $this->fieldHTML('writtenOffDate', $this->writtenOffDate->format('Y-m-d H:i:s'));
		$str .= $this->fieldHTML('description', $this->description->toHTML());
		$str .= $this->fieldHTML('price', $this->price->toHTML());
		$str .= $this->fieldHTML('location', $this->location->toHTML());
		$str .= $this->fieldHTML('wikiLink', $this->wikiLink->toHTML());
		$str .= $this->fieldHTML('addedBy', $this->addedBy->toHTML());
		$str .= $this->fieldHTML('acquiredDate', $this->acquiredDate->format('Y-m-d H:i:s'));
		$str .= $this->fieldHTML('state', $this->state->toHTML());
		$str .= $this->fieldHTML('architecture', $this->architecture->toHTML());
		foreach($this->notes as $note)
                {
                        $str .= $this->fieldHTML('note', $note->toHTML());
                }
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}
}
