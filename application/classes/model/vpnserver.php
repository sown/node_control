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
         * @var Model_CertificateSet
         *
         * @ManyToOne(targetEntity="Model_CertificateSet")
         * @JoinColumns({
         *   @JoinColumn(name="certificate_set_setid", referencedColumnName="setid")
         * })
         */
        protected $vpnCertificateSet;

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

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='vpnServer' id='vpnServer_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>VPN Server</th><td>{$this->id}</td></tr>";
		foreach(array('name', 'externalIPv4', 'internalIPv4', 'IPv4', 'externalIPv6', 'internalIPv6', 'IPv6') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= $this->fieldHTML('port', $this->portStart.' - '.$this->portEnd);
		foreach(array('certificate', 'vpnCertificateSet') as $field)
		{
			$str .= $this->fieldHTML($field, $this->$field->toHTML());
		}
		$str .= "</table>";
		$str .= "</div>";
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
		$blocks = IP_Network_Address::get_block_in_smallest($this->IPv4->excluding($this->getUsedAddrs('IPv4')), $cidr);
		return $blocks[0];
	}

	public function getFreeIPv6Addr($cidr)
	{
		$blocks = IP_Network_Address::get_block_in_smallest($this->IPv6->excluding($this->getUsedAddrs('IPv6')), $cidr);
		return $blocks[0];
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
	
	public static function getVpnServerNames()
	{
		$vpnServers = Doctrine::em()->getRepository('Model_VpnServer')->findAll();
		$vpnServerNames = array();
		foreach ($vpnServers as $vpnServer)
		{
        		$vpnServerNames[$vpnServer->name] = $vpnServer->name;
		}
		return $vpnServerNames;
	}

	public static function validPort($port, $vpnServerName)
        {
                if (!is_numeric($port) || $port < 1 || $port > 65535)
                {
                        return FALSE;
                }
                $vpnServer = Doctrine::em()->getRepository('Model_VpnServer')->findOneByName($vpnServerName);
                if ($port < $vpnServer->portStart || $port > $vpnServer->portEnd)
                {
                        return FALSE;
                }
                return TRUE;

        }

	public static function validIPSubnet($address, $cidr, $version = 4, $vpnServerName = 'sown-auth2.ecs.soton.ac.uk')
        {
		$vpnServer = Doctrine::em()->getRepository('Model_VpnServer')->findOneByName($vpnServerName);
		$IPAddrName = "IPv" . $version . "Addr";
		$IPCidrName = "IPv" . $version . "AddrCidr";
		return IP_Network_Address::factory($vpnServer->$IPAddrName, $vpnServer->$IPCidrName)->encloses_subnet(IP_Network_Address::factory($address, $cidr));	
	}
}
