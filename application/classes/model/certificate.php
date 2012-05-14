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
	 * @Column(name="public_key", type="text", nullable=false)
	 */
	protected $publicKey;

	/**
	 * @var blob $privateKey
	 *
	 * @Column(name="private_key", type="text", nullable=false)
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
		switch($name)
		{
			case "publicKeyFingerprint":
				return static::getFingerprint($this->publicKey);
			case "privateKeyFingerprint":
				return static::getFingerprint($this->privateKey);
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
		return openssl_digest($cert, "sha1");
	}
}
