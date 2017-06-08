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
         * @var integer $IPv4GatewayAddr
         *
         * @Column(name="ipv4_gateway_addr", type="ipv4address", nullable=false)
         */
        protected $IPv4GatewayAddr;

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
         * @var integer $IPv6GatewayAddr
         *
         * @Column(name="ipv6_gateway_addr", type="ipv6address", nullable=false)
         */
        protected $IPv6GatewayAddr;

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
	 * @var boolean $offerDhcpV6
	 *
	 * @Column(name="offer_dhcpv6", type="boolean", nullable=false)
	 */
	protected $offerDhcpV6;

	/**
	 * @var boolean $is1x
	 *
	 * @Column(name="is_1x", type="boolean", nullable=false)
	 */
	protected $is1x;

        /**
         * @var Model_RadiusConfig
         *
         * @ManyToOne(targetEntity="Model_RadiusConfig")
         * @JoinColumns({
         *   @JoinColumn(name="radius_config_id", referencedColumnName="id")
         * })
         */
        protected $radiusConfig;

	/**
         * @var Model_NetworkAdapter
         *
         * @ManyToOne(targetEntity="Model_NetworkAdapter", cascade={"persist", "remove"})
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
		$this->logUse();
		switch($name)
		{
			case "mode":
				return $this->getMode();
			case "IPv4":
				if($this->IPv4Addr == "")
					return "";
				return IP_Network_Address::factory($this->IPv4Addr."/".$this->IPv4AddrCidr);
			case "IPv6":
				if($this->IPv6Addr == "")
					return "";
				return IP_Network_Address::factory($this->IPv6Addr."/".$this->IPv6AddrCidr);
			case "encryption":
				return $this->getEncryption();
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
			case "IPv4":
				if($value == '')
				{
					$this->IPv4Addr = '';
					$this->IPv4AddrCidr = '';
				}
				else
				{
					$this->IPv4Addr = $value->get_address();
					$this->IPv4AddrCidr = $value->get_cidr();
				}
				break;
			case "IPv6":
				if($value == '')
				{
					$this->IPv6Addr = '';
					$this->IPv6AddrCidr = '';
				}
				else
				{
					$this->IPv6Addr = $value->get_address();
					$this->IPv6AddrCidr = $value->get_cidr();
				}
				break;
			case "encryption":
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

	protected function getEncryption()
	{
		if($this->is1x)
		{
			return 'wpa2+aes';
		}
		else
		{
			return '';
		}
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "Interface: {$this->id}, IPv4={$this->IPv4}, IPv6={$this->IPv6}, name={$this->name}, ssid={$this->ssid}, type={$this->type}, offerDhcp={$this->offerDhcp}, is1x={$this->is1x}";
		$str .= "<br/>";
		$str .= "radiusConfig={$this->radiusConfig}";
		$str .= "networkAdapter={$this->networkAdapter}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='interface' id='interface_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Interface</th><td>{$this->id}</td></tr>";
		foreach(array('IPv4', 'IPv6', 'name', 'ssid', 'type', 'offerDhcp', 'is1x') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		foreach(array('networkAdapter') as $field)
		{
			$str .= $this->fieldHTML($field, $this->$field->toHTML());
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($ipv4, $ipv4GatewayAddr, $ipv6, $ipv6GatewayAddr, $name, $ssid, $type, $offerDhcp, $offerDhcpV6, $is1x, $radiusConfigId, $networkAdapter, $node)
	{
		$obj = new Model_Interface();
		$obj->IPv4 = $ipv4;
		$obj->IPv4GatewayAddr = $ipv4GatewayAddr;
		$obj->IPv6 = $ipv6;
		$obj->IPv6GatewayAddr = $ipv6GatewayAddr;
		$obj->name = $name;
		$obj->ssid = $ssid;
		$obj->type = $type;
		$obj->offerDhcp = $offerDhcp;
		$obj->offerDhcpV6 = $offerDhcpV6;
		$obj->is1x = $is1x;
		if (!empty($radiusConfig))
		{
			$radiusConfig = Doctrine::em()->getRepository('Model_RadiusConfig')->find($radiusConfigId);
			$obj->radiusConfig = $radiusConfig;
		}
		$obj->networkAdapter = $networkAdapter;
		$obj->node = $node;
		return $obj;
	}

	public static function freeIPSubnet($address, $cidr, $version = 4, $interfaceId = 0)
	{
		$interfaces = Doctrine::em()->getRepository('Model_Interface')->findAll();
                $IPSubnet = IP_Network_Address::factory($address, $cidr);
                $IPAddrName = "IPv" . $version . "Addr";
                $IPCidrName = "IPv" . $version . "AddrCidr";
                foreach ($interfaces as $i => $interface) {
			
			if (empty($interface->$IPAddrName) || $interface->id == $interfaceId)
                        {
                                continue;
                        }
                        if ($IPSubnet->shares_subnet_space(IP_Network_Address::factory($interface->$IPAddrName, $interface->$IPCidrName)))
                        {
                                return FALSE;
                        }
                }
		return Model_Subnet_Reserved::freeIPSubnet($address, $cidr, $version);
	}
}
