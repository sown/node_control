<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_VpnServer
 *
 * @Table(name="vpn_servers")
 * @Entity
 */
class Model_VpnServer extends Model_Entity
{
	/**
	 * @var string $name
	 *
	 * @Column(name="name", type="string", length=255, nullable=true)
	 */
	protected $name;

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

	/**
	 * @var string $IPv4Network
	 *
	 * @Column(name="IPv4_network", type="ipv4networkaddress", nullable=true)
	 */
	protected $ipv4Network;

	/**
	 * @var string $IPv6Network
	 *
	 * @Column(name="ipv6_network", type="ipv6networkaddress", nullable=true)
	 */
	protected $IPv6Network;

	/**
	 * @var integer $portStart
	 *
	 * @Column(name="port_start", type="integer", nullable=true)
	 */
	protected $portStart;

	/**
	 * @var integer $portEnd
	 *
	 * @Column(name="port_end", type="integer", nullable=true)
	 */
	protected $portEnd;

}
