<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * Model_CertificateSet
 *
 * @Table(name="certificate_sets")
 * @Entity
 */
class Model_CertificateSet extends Model_Entity
{
	/**
	 * @var integer $setid
	 *
	 * @Column(name="setid", type="integer", nullable=true)
	 */
	protected $setid;

	 /**
         * @var Model_Certificate
         *
         * @ManyToOne(targetEntity="Model_Certificate")
         * @JoinColumns({
         *   @JoinColumn(name="certificate_id", referencedColumnName="id")
         * })
         */
	protected $certificate;

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
		$str  = "CertificateSet: {$this->id}, setid={$this->setid}, certificate={$this->certificate->id}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='certificate_set' id='certificate_set_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>CertificateSet</th><td>{$this->setid}</td></tr>";
		$setid = 1;
		$certificates = SOWN::get_certificates_for_set($setid);
		foreach($certificates as $certificate)
		{
			$str .= $this->fieldHTML("certificate", $certificate->toHTML());
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build()
	{
		$obj = new Model_CertificateSet();
		return $obj;
	}

}
