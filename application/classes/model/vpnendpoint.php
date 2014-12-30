<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
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
	 * @var tunnelprotocol $protocol
	 *
	 * @Column(name="protocol", type="tunnelprotocol", nullable=true)
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

	/**
	 * @OneToMany(targetEntity="Model_Node", mappedBy="vpnEndpoint")
	 */
	protected $nodes;

	public function __get($name)
	{
		$this->logUse();
		switch($name)
		{
			case "IPv4":
				return IP_Network_Address::factory($this->IPv4Addr."/".$this->IPv4AddrCidr);
			case "IPv6":
				return IP_Network_Address::factory($this->IPv6Addr."/".$this->IPv6AddrCidr);
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

	public function __toString()
	{
		$this->logUse();
		$str  = "VpnEndpoint: {$this->id}, port={$this->port}, protocol={$this->protocol}, IPv4={$this->IPv4}, IPv6={$this->IPv6}";
		$str .= "<br/>";
		$str .= "vpnServer={$this->vpnServer}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='vpnEndpoint' id='vpnEndpoint_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>VPN Endpoint</th><td>{$this->id}</td></tr>";
		foreach(array('port', 'protocol', 'IPv4', 'IPv6') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		foreach(array('vpnServer') as $field)
		{
			$str .= $this->fieldHTML($field, $this->$field->toHTML());
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($port, $protocol, $ipv4, $ipv6, $vpnServer)
	{
		$obj = new Model_VpnEndpoint();
		$obj->port = $port;
		$obj->protocol = $protocol;
		$obj->IPv4 = $ipv4;
		$obj->IPv6 = $ipv6;
		$obj->vpnServer = $vpnServer;
		return $obj;
	}
	
	public static function freePort($port, $vpnEndpointId = 0)
	{
		$vpnEndpoint = Doctrine::em()->getRepository('Model_VpnEndpoint')->findOneByPort($port);
                if (!empty($vpnEndpoint->id) && !empty($vpnEndpointId) && $vpnEndpointId != $vpnEndpoint->id)
                {
                        return FALSE;
                }
                return TRUE;
	}

	public static function freeIPSubnet($address, $cidr, $version = 4, $vpnEndpointId = 0)
	{
		$vpnEndpoints = Doctrine::em()->getRepository('Model_VpnEndpoint')->findAll();
		$IPSubnet = IP_Network_Address::factory($address, $cidr);
		$IPAddrName = "IPv" . $version . "Addr";
                $IPCidrName = "IPv" . $version . "AddrCidr";
		foreach ($vpnEndpoints as $v => $vpnEndpoint) {
			if ($vpnEndpoint->id == $vpnEndpointId)
			{
				continue;
			}
			if ($IPSubnet->shares_subnet_space(IP_Network_Address::factory($vpnEndpoint->$IPAddrName, $vpnEndpoint->$IPCidrName))) 
			{
				return FALSE;
			}
		}
		return Model_Subnet_Reserved::freeIPSubnet($address, $cidr, $version);
	}
}
