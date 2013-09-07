<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * Model_Note
 *
 * @Table(name="notes")
 * @Entity
 */
class Model_Note extends Model_Entity
{
	/**
	 * @var blob $noteText
	 *
	 * @Column(name="note_text", type="text", nullable=true)
	 */
	protected $noteText;

	 /**
         * @var Model_User $notetaker
         *
         * @ManyToOne(targetEntity="Model_User")
         * @JoinColumns({
         *   @JoinColumn(name="notetaker_id", referencedColumnName="id")
         * })
         */
        protected $notetaker;	

	/**
	 * @var datetime $createdAt
	 *
	 * @Column(name="created_at", type="datetime", nullable=true)
	 */
	protected $createdAt;

 	/**
         * @var Model_Deployment $deployment
         *
         * @ManyToOne(targetEntity="Model_Deployment")
         * @JoinColumns({
         *   @JoinColumn(name="deployment_id", referencedColumnName="id", nullable=true)
         * })
         */
        protected $deployment;

	/**
         * @var Model_InventoryItem $inventoryItem
         *
         * @ManyToOne(targetEntity="Model_InventoryItem")
         * @JoinColumns({
         *   @JoinColumn(name="inventory_id", referencedColumnName="id", nullable=true)
         * })
         */
        protected $inventoryItem;

	 /**
         * @var Model_Node $node
         *
         * @ManyToOne(targetEntity="Model_Node")
         * @JoinColumns({
         *   @JoinColumn(name="node_id", referencedColumnName="id", nullable=true)
         * })
         */
        protected $node;

	 /**
         * @var Model_User $user
         *
         * @ManyToOne(targetEntity="Model_User")
         * @JoinColumns({
         *   @JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
         * })
         */
        protected $user;


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
		$str  = "Note: {$this->id}, noteText={$this->noteText}, notetaker={$this->notetaker->username}, createdAt={$this->createdAt->format('Y-m-d H:i:s')}";
		return $str;
	}

	public function text_only() 
	{
	 	$this->logUse();
		return $this->noteText;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='note' id='note_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Note</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('note', $this->noteText);
		$str .= $this->fieldHTML('notetaker', $this->notetaker->username);
		$str .= $this->fieldHTML('createdAt', $this->createdAt->format('Y-m-d H:i:s'));
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($entityType, $entityId, $noteText, $notetakerId)
        {
                $obj = new Model_Note();
		
		switch ($entityType) 
		{
			case 'Deployment':
				$obj->deployment = Doctrine::em()->getRepository('Model_Deployment')->find($entityId);
				break;
			case 'InventoryItem':
                                $obj->inventoryItem = Doctrine::em()->getRepository('Model_InventoryItem')->find($entityId);
                                break;
			case 'Node':
				$obj->node = Doctrine::em()->getRepository('Model_Node')->find($entityId);
                                break;
			case 'User':
				$obj->user = Doctrine::em()->getRepository('Model_User')->find($entityId);
                                break;
			default:
		}
                $obj->noteText = $noteText;
		$obj->createdAt = new \DateTime();
                $obj->notetaker = Doctrine::em()->getRepository('Model_User')->find($notetakerId);
                return $obj;
        }
}
