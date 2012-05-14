<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
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

	public function __get($name)
	{
		switch($name)
		{
			case "mode":
				return $this->getMode();
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

	public function __set($name, $value)
	{
		switch($name)
		{
			case "mode":
				$this->setMode($value);
				break;
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

	protected function getMode()
	{
		if($this->offerDhcp)
		{
			return "dhcp";
		}
		else
		{
			return "static";
		}
	}

	protected function setMode($value)
	{
		$this->offerDhcp = ($value == 'dhcp');
	}

	public function toString()
	{
		$str  = "Interface: {$this->id}, IPv4={$this->IPv4}, IPv6={$this->IPv6}, name={$this->name}, ssid={$this->ssid}, type={$this->type}, offerDhcp={$this->offerDhcp}, is1x={$this->is1x}";
		$str .= "<br/>";
		$str .= "networkAdapter={$this->networkAdapter->toString()}";
		return $str;
	}
}
