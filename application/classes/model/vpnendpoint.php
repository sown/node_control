<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * Model_VpnEndpoint
 *
 * @Table(name="vpn_endpoints")
 * @Entity
 */
class Model_VpnEndpoint extends Model_Entity
{
	/**
	 * @var integer $port
	 *
	 * @Column(name="port", type="integer", nullable=true)
	 */
	protected $port;

	/**
	 * @var string $protocol
	 *
	 * @Column(name="protocol", type="string", nullable=true)
	 */
	protected $protocol;

	/**
	 * @var string $IPv4Addr
	 *
	 * @Column(name="ipv4_addr", type="ipv4address", nullable=false)
	 */
	protected $IPv4Addr;

	/**
	 * @var integer $IPv4AddrCidr
	 *
	 * @Column(name="ipv4_addr_cidr", type="integer", nullable=false)
	 */
	protected $IPv4AddrCidr;

	/**
	 * @var string $IPv6Addr
	 *
	 * @Column(name="ipv6_addr", type="ipv6address", nullable=false)
	 */
	protected $IPv6Addr;

	/**
	 * @var integer $IPv6AddrCidr
	 *
	 * @Column(name="ipv6_addr_cidr", type="integer", nullable=false)
	 */
	protected $IPv6AddrCidr;

	/**
	 * @var Model_VpnServer
	 *
	 * @ManyToOne(targetEntity="Model_VpnServer")
	 * @JoinColumns({
	 *   @JoinColumn(name="vpn_server_id", referencedColumnName="id")
	 * })
	 */
	protected $vpnServer;

	public function __get($name)
	{
		switch($name)
		{
			case "IPv4":
				return $this->IPv4Addr."/".$this->IPv4AddrCidr;
			case "IPv6":
				return $this->IPv6Addr."/".$this->IPv6AddrCidr;
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

	public function toString()
	{
		$str  = "VpnEndpoint: {$this->id}, port={$this->port}, protocol={$this->protocol}, IPv4={$this->IPv4}, IPv6={$this->IPv6}";
		$str .= "<br/>";
		$str .= "vpnServer={$this->vpnServer->toString()}";
		return $str;
	}
}
