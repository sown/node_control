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
class Model_VpnServer extends Model_Server
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

	public function __toString()
	{
		$this->logUse();
		$str  = "VpnServer: {$this->id}, name={$this->name}, externalIPv4={$this->externalIPv4}, internalIPv4={$this->internalIPv4}, IPv4={$this->IPv4}, externalIPv6={$this->externalIPv6}, internalIPv6={$this->internalIPv6}, IPv6={$this->IPv6}, portStart={$this->portStart}, portEnd={$this->portEnd}";
		$str .= "<br/>";
		$str .= "certificate={$this->certificate}";
		return $str;
	}

	public function getFreePort()
	{
		$repository = Doctrine::em()->getRepository('Model_VpnEndpoint');
		foreach($repository->findAll() as $entity)
		{
			if($entity->port != "")
			{
				$usedports[] = $entity->port;
			}
		}
		for($i = $this->portStart; $i <= $this->portEnd; $i++)
		{
			if(!in_array($i, $usedports))
			{
				return $i;
			}
		}
		return null;
	}

	public function getFreeIPv4Addr($cidr)
	{
		return IP_Network_Address::get_smallest_free_block_for($this->IPv4->get_free_network_addresses($this->getUsedAddrs('IPv4')), $cidr);
	}

	public function getFreeIPv6Addr($cidr)
	{
		return IP_Network_Address::get_smallest_free_block_for($this->IPv6->get_free_network_addresses($this->getUsedAddrs('IPv6')), $cidr);
	}

	private function getUsedAddrs($type)
	{
		foreach(array('Model_Interface', 'Model_VpnEndpoint') as $class)
		{
			$repository = Doctrine::em()->getRepository($class);
			foreach($repository->findAll() as $entity)
			{
				if($entity->$type != "")
				{
					$usedspace[] = $entity->$type;
				}
			}
		}
		return $usedspace;
	}
}
