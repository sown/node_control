<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
/**
 * Model_Server
 *
 * @Table(name="servers")
 * @Entity
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"server" = "Model_Server", "vpn" = "Model_VpnServer"})
 */
class Model_Server extends Model_Entity
{
	/**
	 * @var string $name
	 *
	 * @Column(name="name", type="string", length=255, nullable=true)
	 */
	protected $name;

	/**
         * @var string $internalName
         *
         * @Column(name="internal_name", type="string", length=255, nullable=true)
         */
        protected $internalName;

	/**
         * @var string $internalCname
         *
         * @Column(name="internal_cname", type="string", length=255, nullable=true)
         */
        protected $internalCname;

	/**
         * @var string $icingaName
         *
         * @Column(name="icinga_name", type="string", length=255, nullable=true)
         */
        protected $icingaName;

	 /**
         * @var string $description
         *
         * @Column(name="description", type="text", nullable=true)
         */
        protected $description;

	/**
	 * @var Model_Certificate
	 *
	 * @ManyToOne(targetEntity="Model_Certificate", cascade={"persist", "remove"})
	 * @JoinColumns({
	 *   @JoinColumn(name="certificate_id", referencedColumnName="id")
	 * })
	 */
	protected $certificate;

	/**
         * @var string $externalInterface
         *
         * @Column(name="external_interface", type="string", length=20, nullable=true)
         */
        protected $externalInterface;

        /**
         * @var string $internalInterface
         *
         * @Column(name="internal_interface", type="string", length=20, nullable=true)
         */
        protected $internalInterface;

        /**
         * @var string $externalMac
         *
         * @Column(name="external_mac", type="string", length=17, nullable=true)
         */
        protected $externalMac;

        /**
         * @var string $internalMac
         *
         * @Column(name="internal_mac", type="string", length=17, nullable=true)
         */
        protected $internalMac;

	 /**
         * @var string $externalSwitchport
         *
         * @Column(name="external_switchport", type="string", length=255, nullable=true)
         */
        protected $externalSwitchport;

        /**
         * @var string $internalSwitchport
         *
         * @Column(name="internal_switchport", type="string", length=255, nullable=true)
         */
        protected $internalSwitchport;

	 /**
         * @var string $externalCable
         *
         * @Column(name="external_cable", type="string", length=50, nullable=true)
         */
        protected $externalCable;

        /**
         * @var string $internalCable
         *
         * @Column(name="internal_cable", type="string", length=50, nullable=true)
         */
        protected $internalCable;

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
         * @var datetime $acquiredDate
         *
         * @Column(name="acquired_date", type="datetime", nullable=false)
         */
        protected $acquiredDate;

        /**
         * @var integer $retired
         *
         * @Column(name="retired", type="integer", nullable=true)
         */
        protected $retired;

	/**
         * @var string $serverCase
         *
         * @Column(name="server_case", type="string", length=255, nullable=true)
         */
        protected $serverCase;

        /**
         * @var string $processor
         *
         * @Column(name="processor", type="string", length=255, nullable=true)
         */
        protected $processor;

	/**
         * @var string $memory
         *
         * @Column(name="memory", type="string", length=20, nullable=true)
         */
        protected $memory;

        /**
         * @var string $hardDrive
         *
         * @Column(name="hard_drive", type="string", length=255, nullable=true)
         */
        protected $hardDrive;

        /**
         * @var string $networkPorts
         *
         * @Column(name="network_ports", type="string", length=255, nullable=true)
         */
        protected $networkPorts;
	
	/**
         * @var string $wakeOnLan
         *
         * @Column(name="wake_on_lan", type="string", length=255, nullable=true)
         */
        protected $wakeOnLan;

	/**     
         * @var string $kernel
         *
         * @Column(name="kernel", type="string", length=255, nullable=true)
         */
        protected $kernel;

        /**     
         * @var string $os
         *
         * @Column(name="os", type="string", length=255, nullable=true)
         */
        protected $os;

	/**
         * @var Model_Location
         *
         * @ManyToOne(targetEntity="Model_Location")
         * @JoinColumns({
         *   @JoinColumn(name="location_id", referencedColumnName="id")
         * })
         */
        protected $location;	

       	/**
	* @ManyToMany(targetEntity="Model_CronJob")
	* @JoinTable(name="host_cron_jobs",
	*      joinColumns={@JoinColumn(name="server_id", referencedColumnName="id")},
	*      inverseJoinColumns={@JoinColumn(name="cron_job_id", referencedColumnName="id")}
	*      )
	*/ 
	protected $cronJobs;
	

	public function __get($name)
	{
		$this->logUse();
		switch($name)
		{
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

	public function getAllCronJobs()
        {
                $allCronJobs = $this->cronJobs;

                return $allCronJobs;
        }

	public function getEnabledCronJobs()
	{
		$enabledCronJobs = array();
		$allCronJobs = $this->getAllCronJobs();
		foreach ($allCronJobs as $c => $cronJob) 
		{
			if ($cronJob->disabled == 0)
			{
				$enabledCronJobs[] = $cronJob;
			}
		}
		return $enabledCronJobs;
	}

	public static function getByName($name)
	{
		return Doctrine::em()->getRepository('Model_Server')->findOneByName($name);
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "Server: {$this->id}, name={$this->name}, internalName={$this->internalName}, icingaName={$this->icingaName}, externalMac={$this->externalMac}, internalMac={$this->internalMac}, externalInterface={$this->externalInterface}, internalInterface={$this->internalInterface}, externalSwitchport={$this->externalSwitchport}, internalSwitcport={$this->internalSwitchport}, externalCable={$this->externalCable}, internalCable={$this->internalCable}, externalIPv4={$this->externalIPv4}, internalIPv4={$this->internalIPv4}, externalIPv6={$this->externalIPv6}, internalIPv6={$this->internalIPv6}, acquiredDate={$this->acquiredDate->format('Y-m-d H:i:s')}, retired={$this->retired}, serverCase={$this->serverCase}, processor={$this->processor}, memory={$this->memory}, hardDrive={$this->hardDrive}, networkPorts={$this->networkPorts}, wakeOnLan={$this->wakeOnLan},` kernel={$this->kernel}, os={$this->os}";
		$str .= "<br/>";
		$str .= "certificate={$this->certificate}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='server' id='server_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Server</th><td>{$this->id}</td></tr>";
		foreach(array('name', 'internalName', 'icingaName', 'externalMac', 'internalMac', 'externalInterface', 'internalInterface', 'externalSwitchport', 'internalSwitchport', 'externalCable', 'internalCable', 'externalIPv4', 'internalIPv4', 'externalIPv6', 'internalIPv6') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		foreach(array('certificate') as $field)
		{
			if($this->$field)
			{
				$str .= $this->fieldHTML($field, $this->$field->toHTML());
			}
		}
		$str .= $this->fieldHTML('acquiredDate')->format('Y-m-d H:i:s');
		foreach(array('retired', 'serverCase', 'processor', 'memory', 'hardDrive', 'networkPorts', 'wakeOnLan', 'kernel', 'os') as $field)
                {
                        $str .= $this->fieldHTML($field);
                }
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public function toWikiMarkup()
	{
		$wm = "";
		if (!empty($this->internalName)) $wm .= "[[sowndns::{$this->internalName}.sown.org.uk]]";
		elseif (!empty($this->name)) 
		{
			$wm .= "[[ecsdns::{$this->name}]]";
			$ecsdns = 1;	
		}
		else $wm .= $this->icingaName;
		$wm .= (empty($this->description) ? ' is a SOWN server' : " is ".$this->description);
		$wm .= ".\n\n";
		$hwphrases = array();
		if (!empty($this->processor)) $hwphrases[] = "a [[cpu::{$this->processor}]] processor";
		if (!empty($this->memory)) $hwphrases[] = "[[memory::{$this->memory}]] of memory";
		if (!empty($this->hardDrive)) $hwphrases[] = "[[harddrive::{$this->hardDrive}]] of memory";
		if (!empty($this->networkPorts)) $hwphrases[] = "[[networkinterfaces::{$this->networkPorts}]] network intefaces";
		$wm .= (sizeof($hwphrases) > 0 ? "It has {$hwphrases[0]}" : '');
		for ($h = 1; $h < sizeof($hwphrases); $h++) 
		{
			$wm .= ($h == sizeof($hwphrases)-1 ? ' and ' : ', ');
			$wm .= $hwphrases[$h];
		}	
		$wm .= (sizeof($hwphrases) > 0 ? '. ' : '');
		if (!empty($this->serverCase) || !empty($this->location)) {
			$wm .= "It is ";
			$wm .= (!empty($this->serverCase) ? "housed within a [[case::{$this->serverCase}]] case " : '');
			$wm .= (!empty($this->location) ? "located in [[location::{$this->location->longName} ({$this->location->name})]] ([[coordinates::{$this->location->latitude}, {$this->location->longitude}]])" : '');
			$wm .= ".";
		}
		$wm .= (sizeof($hwphrases) > 0 ? "\n\n" : "\n");
		$wm .= (!empty($this->acquiredDate) ? '' : "It was accquired on [[acquired_date::".$this->acquiredDate->format('jS F Y')."]].\n\n");
		$wm .= "= Network =\n\n";
		if (!empty($this->internalName))
		{
			if (!empty($this->internalIPv4) || !empty($this->internalIPv6))
			{
				$wm .= "This server is connected to the SOWN VLAN with the IP addresses:\n";
				$wm .= (empty($this->internalIPv4) ? '' : "* [[sownipv4::{$this->internalIPv4}]]\n");
				$wm .= (empty($this->internalIPv6) ? '' : "* [[sownipv6::".str_replace("::", ":&#58;", $this->internalIPv6)."]]\n");
			}
			$wm .= "It has the DNS names:\n* {$this->internalName}.sown.org.uk\n";
			$wm .= (empty($this->internalCname) ? '' : "[[has_cname::{$this->internalCname}.sown.org.uk]]\n");
			if (!empty($this->internalMac) || !empty($this->internalInterface))# || !empty($this->internalSwitchPort) || !empty($this->internalCable)) 
			{
				$wm .= "Its ";
				$wm .= (empty($this->internalMac) ? '' : "MAC address is [[sownmac::{$this->internalMac}]]");
				$wm .= (!empty($this->internalMac) && !empty($this->internalInterface) ? ' on its ' : ' ');
				$wm .= (empty($this->internalInterface) ? '' : "[[sowninterface::{$this->internalInterface}]] interface");
				$wm .= ((!empty($this->internalMac) || !empty($this->internalInterface)) && ((!empty($this->internalSwitchport) || !empty($this->internalCable))) ? ' and' : '');
				$wm .= (!empty($this->internalSwitchport) || !empty($this->internalCable) ? ' connected' : '');
				$wm .= (empty($this->internalSwitchport) ? '' : " to port [[sownport::{$this->internalSwitchport}]]");
				$wm .= (empty($this->internalCable) ? '' : " with a {$this->internalCable} network cable");
				$wm .= ".\n";
			}
			$wm .= "\n";
		}
		if (!empty($this->name))
		{
                        if (!empty($this->externalIPv4) || empty($this->externalIPv6))
                        {
                                $wm .= "This server is connected to the ECS VLAN with the IP addresses:\n";
                                $wm .= (empty($this->externalIPv4) ? '' : "* [[ecsipv4::{$this->externalIPv4}]]\n");
                                $wm .= (empty($this->externalIPv6) ? '' : "* [[ecsipv6::".str_replace("::", ":&#58;", $this->externalIPv6)."]]\n");
                        }
                        $wm .= "It has the DNS names:\n* ";
			$wm .= (empty($ecsdns) ? "[[ecsdns::{$this->name}]]" : $this->name);
			$wm .= "\n";
			if (!empty($this->externalMac) || !empty($this->externalInterface)) #|| !empty($this->externalSwitchport) || !empty($this->externalCable))
                        {
                                $wm .= "Its ";
                                $wm .= (empty($this->externalMac) ? '' : "MAC address is [[ecsmac::{$this->externalMac}]]");
                                $wm .= (!empty($this->externalMac) && !empty($this->externalInterface) ? ' on its ' : ' ');
                                $wm .= (empty($this->externalInterface) ? '' : "[[ecsinterface::{$this->externalInterface}]] interface");
                                $wm .= ((!empty($this->externalMac) || !empty($this->externalInterface)) && ((!empty($this->externalSwitchport) || !empty($this->externalCable))) ? ' and' : '');
                                $wm .= (!empty($this->externalSwitchport) || !empty($this->externalCable) ? ' connected' : '');
                                $wm .= (empty($this->externalSwitchport) ? '' : " to port [[ecsport::{$this->externalSwitchport}]]");
                                $wm .= (empty($this->externalCable) ? '' : " with a {$this->externalCable} network cable");
                                $wm .= ".\n";
                        }
			$wm .= "\n";
                }
		if (!empty($this->wakeOnLan))
		{
			$wm .= "The WakeOnLAN capability of this server is [[WakeOnLAN::{$this->wakeOnLan}]].\n\n";
		}
		$wm .= "[[Category:Server]]";	
		return $wm;
	}

	public static function uniqueIcingaName($icingaName, $id = 0)
        {
		if (empty($icingaName))
		{
			return FALSE;
		}
                $result = Doctrine::em()->getRepository('Model_Server')->findOneByIcingaName($icingaName);
                if (!empty($result->id) && $result->id == $id)
                {
                        return TRUE;
                }
                return empty($result->id);
        }

	public static function build($icingaName)
        {
                $obj = new Model_Server();
                $obj->icingaName = $icingaName;
		$obj->save();
                return $obj;
        }

}
