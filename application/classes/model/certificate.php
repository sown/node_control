<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;

/**
 * Model_Certificate
 *
 * @Table(name="certificates")
 * @Entity
 */
class Model_Certificate extends Model_Entity
{
	/**
	 * @var blob $publicKey
	 *
	 * @Column(name="public_key", type="text", nullable=true)
	 */
	protected $publicKey;

	/**
	 * @var blob $privateKey
	 *
	 * @Column(name="private_key", type="text", nullable=true)
	 */
	protected $privateKey;

	/**
	 * @var boolean $current
	 *
	 * @Column(name="current", type="boolean", nullable=false)
	 */
	protected $current;

	public function __get($name)
	{
		$this->logUse();
		switch($name)
		{
			case "publicKeyFingerprint":
				return static::getFingerprint($this->publicKey);
			case "privateKeyFingerprint":
				return static::getFingerprint($this->privateKey);
			case "cn":
				return $this->getCN();
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
			case "publicKeyFingerprint":
			case "privateKeyFingerprint":
			case "cn":
				parent::__throwReadOnlyException($name);
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

	public static function getFingerprint($cert)
	{
		$digest_algos = openssl_get_md_methods();
		if(!in_array("sha1", openssl_get_md_methods()))
		{
			# going to fail
			return openssl_digest($cert, "sha1");
		}
		return openssl_digest($cert, "sha1");
	}

	public function getCN()
	{
		$data = openssl_x509_parse($this->publicKey);
		return $data['subject']['CN'];
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "Certificate: {$this->id}, cn={$this->cn}, publicKeyFingerprint={$this->publicKeyFingerprint}, privateKeyFingerprint={$this->privateKeyFingerprint}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='certificate' id='certificate_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Certificate</th><td>{$this->id}</td></tr>";
		foreach(array('cn', 'publicKeyFingerprint', 'privateKeyFingerprint') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build()
	{
		$obj = new Model_Certificate();
		$obj->current = 1;
		return $obj;
	}
}
