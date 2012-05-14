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
	 * @var string $IPv4Network
	 *
	 * @Column(name="ipv4_network", type="ipv4networkaddress", nullable=true)
	 */
	protected $IPv4;

	/**
	 * @var string $IPv6Network
	 *
	 * @Column(name="ipv6_network", type="ipv6networkaddress", nullable=true)
	 */
	protected $IPv6;

	/**
	 * @var Model_VpnServer
	 *
	 * @ManyToOne(targetEntity="Model_VpnServer")
	 * @JoinColumns({
	 *   @JoinColumn(name="vpn_server_id", referencedColumnName="id")
	 * })
	 */
	protected $vpnServer;

	public function toString()
	{
		$str  = "VpnEndpoint: {$this->id}, port={$this->port}, protocol={$this->protocol}, IPv4={$this->IPv4}, IPv6={$this->IPv6}";
		$str .= "<br/>";
		$str .= "vpnServer={$this->vpnServer->toString()}";
		return $str;
	}
}
