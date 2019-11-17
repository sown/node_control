<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * Model_NodeClientListMac
 *
 * @Table(name="node_client_list_macs")
 * @Entity
 */
class Model_NodeClientListMac extends Model_Entity
{

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
         * @var string $mac
         *
         * @Column(name="mac", type="string", length=17, nullable=false)
         */
        protected $mac;

	/**
	 * @var text $description
	 *
	 * @Column(name="description", type="text", nullable=false)
	 */
	protected $description;

	 /**
         * @var Model_User $lister
         *
         * @ManyToOne(targetEntity="Model_User")
         * @JoinColumns({
         *   @JoinColumn(name="lister_id", referencedColumnName="id")
         * })
         */
        protected $lister;	

	/**
	 * @var datetime $createdAt
	 *
	 * @Column(name="created_at", type="datetime", nullable=true)
	 */
	protected $createdAt;

	/**
         * @var maclisting $type
         *
         * @Column(name="type", type="maclisting", nullable=false)
         */
        protected $type;
	
	/**
         * @var boolean $disabled
         *
         * @Column(name="disabled", type="boolean", nullable=false)
         */
        protected $disabled;

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
		$str  = "NodeClientListMac: {$this->id}, node={$this->node->boxNumber}, mac={$this->mac}, description={$this->description}, lister={$this->lister->username}, createdAt={$this->createdAt->format('Y-m-d H:i:s')}, type={$this->type}, disabled={$this->disabled}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='nodeclientlistmac' id='nodeclientlistmac_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Node Client List Mac</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('node', $this->node->boxNumber);
		$str .= $this->fieldHTML('mac', $this->mac);
		$str .= $this->fieldHTML('description', $this->description);
		$str .= $this->fieldHTML('lister', $this->lister->username);
		$str .= $this->fieldHTML('createdAt', $this->createdAt->format('Y-m-d H:i:s'));
		$str .= $this->fieldHTML('type', $this->type);
		$str .= $this->fieldHTML('disabled', $this->disabled);	
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($node, $mac, $description, $type, $disabled=false)
        {
                $obj = new Model_NodeClientListMac();
		
		$obj->node = $node;
                $obj->mac = $mac;
		$obj->description = $description;
		$obj->createdAt = new \DateTime();
                $obj->lister = Doctrine::em()->getRepository('Model_User')->findOneByUsername(Auth::instance()->get_user());
		$obj->type = $type;
		$obj->disabled = $disabled;
	
                return $obj;
        }
}
