<?php

/**
 * Model_NetworkAdapter
 *
 * @Table(name="network_adapters")
 * @Entity
 */
class Model_NetworkAdapter extends Model_Entity
{
	/**
	 * @var text $mac
	 *
	 * @Column(name="mac", type="text", nullable=false)
	 */
	private $mac;

	/**
	 * @var integer $wirelessChannel
	 *
	 * @Column(name="wireless channel", type="integer", nullable=false)
	 */
	private $wirelessChannel;

	/**
	 * @var text $type
	 *
	 * @Column(name="type", type="text", nullable=false)
	 */
	private $type;

	/**
	 * @var Model_Node
	 *
	 * @ManyToOne(targetEntity="Model_Node")
	 * @JoinColumns({
	 *   @JoinColumn(name="node_id", referencedColumnName="id")
	 * })
	 */
	private $node;

}
