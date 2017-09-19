<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
/**
 * Model_NodeCname
 *
 * @Table(name="node_cnames")
 * @Entity
 */
class Model_NodeCname extends Model_Entity
{

	/**
         * @var Model_Node
         *
         * @ManyToOne(targetEntity="Model_Node")
         * @JoinColumns({
         *   @JoinColumn(name="node_id", referencedColumnName="id")
         * })
         */
        protected $node;

	/**
         * @var string $cname
         *
         * @Column(name="cname", type="string", length=30, nullable=false)
         */
        protected $cname;

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

	public function __toString()
	{
		$this->logUse();
		$str  = "NodeCname: {$this->id}, node={$this->node->boxNumber} cname={$this->cname}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='node_cname' id='node_cname_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>NodeCname</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML($cname);
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($node, $cname)
        {
                $nc = new Model_NodeCname();
                $nc->node = $node;
                $nc->cname = $cname;
		$nc->save();
                return $nc;
        }


}
