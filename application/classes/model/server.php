<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
/**
 * Model_Server
 *
 * @Table(name="servers")
 * @Entity
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"server" = "Model_Server", "vpn" = "Model_VpnServer"})
 */
class Model_Server extends Model_Entity
{
	/**
	 * @var string $name
	 *
	 * @Column(name="name", type="string", length=255, nullable=true)
	 */
	protected $name;

	/**
	 * @var Model_Certificate
	 *
	 * @ManyToOne(targetEntity="Model_Certificate")
	 * @JoinColumns({
	 *   @JoinColumn(name="certificate_id", referencedColumnName="id")
	 * })
	 */
	protected $certificate;

	/**
	 * @var string $externalIPv4
	 *
	 * @Column(name="external_ipv4", type="ipv4address", nullable=true)
	 */
	protected $externalIPv4;

	/**
	 * @var string $internalIPv4
	 *
	 * @Column(name="internal_ipv4", type="ipv4address", nullable=true)
	 */
	protected $internalIPv4;

	/**
	 * @var string $externalIPv6
	 *
	 * @Column(name="external_ipv6", type="ipv6address", nullable=true)
	 */
	protected $externalIPv6;

	/**
	 * @var string $internalIPv6
	 *
	 * @Column(name="internal_ipv6", type="ipv6address", nullable=true)
	 */
	protected $internalIPv6;

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

	public static function getByName($name)
	{
		return Doctrine::em()->getRepository('Model_Server')->findOneByName($name);
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "Server: {$this->id}, name={$this->name}, externalIPv4={$this->externalIPv4}, internalIPv4={$this->internalIPv4}, externalIPv6={$this->externalIPv6}, internalIPv6={$this->internalIPv6}";
		$str .= "<br/>";
		$str .= "certificate={$this->certificate}";
		return $str;
	}
}
