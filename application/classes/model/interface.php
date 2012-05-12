<?php

/**
 * Model_Interface
 *
 * @Table(name="interfaces")
 * @Entity
 */
class Model_Interface extends Model_Entity
{
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
	 * @var text $name
	 *
	 * @Column(name="name", type="text", nullable=false)
	 */
	protected $name;

	/**
	 * @var text $ssid
	 *
	 * @Column(name="ssid", type="text", nullable=false)
	 */
	protected $ssid;

	/**
	 * @var string $type
	 *
	 * @Column(name="type", type="string", nullable=false)
	 */
	protected $type;

	/**
	 * @var boolean $offerDhcp
	 *
	 * @Column(name="offer_dhcp", type="boolean", nullable=false)
	 */
	protected $offerDhcp;

	/**
	 * @var boolean $is1x
	 *
	 * @Column(name="is_1x", type="boolean", nullable=false)
	 */
	protected $is1x;

	/**
	 * @var Model_NetworkAdapter
	 *
	 * @ManyToOne(targetEntity="Model_NetworkAdapter")
	 * @JoinColumns({
	 *   @JoinColumn(name="network_adapter_id", referencedColumnName="id")
	 * })
	 */
	protected $networkAdapter;

	/**
	 * @var Model_Node
	 *
	 * @ManyToOne(targetEntity="Model_Node")
	 * @JoinColumns({
	 *   @JoinColumn(name="node_id", referencedColumnName="id")
	 * })
	 */
	protected $node;

}
