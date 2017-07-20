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
 * Model_ServerInterfaceCname
 *
 * @Table(name="server_interface_cnames")
 * @Entity
 */
class Model_ServerInterfaceCname extends Model_Entity
{

	/**
         * @var Model_ServerInterface
         *
         * @ManyToOne(targetEntity="Model_ServerInterface")
         * @JoinColumns({
         *   @JoinColumn(name="server_interface_id", referencedColumnName="id")
         * })
         */
        protected $serverInterface;

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
		$str  = "ServerInterfaceCname: {$this->id}, cname={$this->cname}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='server_interface_cname' id='server_interface_cname_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>ServerInterfaceCname</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('cname');
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($serverInterface, $cname)
        {
                $sic = new Model_ServerInterfaceCname();
		$sic->serverInterface = $serverInterface;
		$sic->cname = $cname;
		$sic->save();
                return $sic;
        }
	public static function getList($sics)
	{
		$cnames = array();
		foreach ($sics as $sic)
		{
			$cnames[] = $sic->cname;
		}
		return implode(',',$cnames);
	}
}
