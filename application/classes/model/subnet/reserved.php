<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
/**
 * Model_Subnet_Reserved
 *
 * @Table(name="reserved_subnets")
 * @Entity
 */
class Model_Subnet_Reserved extends Model_Entity
{
	/**
         * @var text $name
         *
         * @Column(name="name", type="text", nullable=false)
         */
        protected $name;

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

	public function __get($name)
	{
		$this->logUse();
		switch($name)
		{
			case "IPv4":
				if($this->IPv4Addr == "")
					return "";
				return IP_Network_Address::factory($this->IPv4Addr."/".$this->IPv4AddrCidr);
			case "IPv6":
				if($this->IPv6Addr == "")
					return "";
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
		$str  = "Subnet_Reserved: {$this->id}, name={$this->name}, IPv4={$this->IPv4}, IPv6={$this->IPv6}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='subnet_reserved' id='subnet_reserved_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Reserved Subnet</th><td>{$this->id}</td></tr>";
		foreach(array('name', 'IPv4', 'IPv6') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($name, $ipv4, $ipv6)
	{
		$reservedSubnet = new Model_Subnet_Reserved();
		$reservedSubnet->name = $name;
		$reservedSubnet->IPv4 = $ipv4;
		$reservedSubnet->IPv6 = $ipv6;
		return $reservedSubnet;
	}
	
	public static function freeIPSubnet($address, $cidr, $version = 4)
        {
		$IPSubnet = IP_Network_Address::factory($address, $cidr);
                $IPAddrName = "IPv" . $version . "Addr";
                $IPCidrName = "IPv" . $version . "AddrCidr";
		$reservedSubnets = Doctrine::em()->getRepository('Model_Subnet_Reserved')->findAll();
                foreach ($reservedSubnets as $rs => $reservedSubnet) {
                        if (empty($reservedSubnet->$IPAddrName))
                        {
                                continue;
                        }
                        if ($IPSubnet->shares_subnet_space(IP_Network_Address::factory($reservedSubnet->$IPAddrName, $reservedSubnet->$IPCidrName)))
                        {
                                return FALSE;
                        }
                }
		return TRUE;
	}
}
