<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
/**
 * Model_OtherHostCname
 *
 * @Table(name="other_host_cnames")
 * @Entity
 */
class Model_OtherHostCname extends Model_Entity
{

	/**
         * @var Model_OtherHost
         *
         * @ManyToOne(targetEntity="Model_OtherHost")
         * @JoinColumns({
         *   @JoinColumn(name="other_host_id", referencedColumnName="id")
         * })
         */
        protected $otherHost;

	/**
         * @var string $cname
         *
         * @Column(name="cname", type="string", length=255, nullable=true)
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
		$str  = "OtherHostCname: {$this->id}, cname={$this->cname}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='other_host_cname' id='other_host_cname_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>OtherHostCname</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('cname');
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($otherHost, $cname)
        {
                $ohc = new Model_OtherHostCname();
		$ohc->otherHost = $otherHost;
		$ohc->cname = $cname;
		$ohc->save();
                return $ohc;
        }
	public static function getList($ohcs)
	{
		$cnames = array();
		foreach ($ohcs as $ohc)
		{
			$cnames[] = $ohc->cname;
		}
		return implode(',',$cnames);
	}
}
