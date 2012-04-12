<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_Node
 *
 * @Table(name="nodes")
 * @Entity
 */
class Model_Node extends Model_Entity
{
	/**
	 * @var integer $boxNumber
	 *
	 * @Column(name="box_number", type="integer", nullable=true)
	 */
	private $boxNumber;

	/**
	 * @var text $firmwareImage
	 *
	 * @Column(name="firmware_image", type="text", nullable=false)
	 */
	private $firmwareImage;

	/**
	 * @var text $notes
	 *
	 * @Column(name="notes", type="text", nullable=true)
	 */
	private $notes;

	/**
	 * @var Model_Certificate
	 *
	 * @ManyToOne(targetEntity="Model_Certificate")
	 * @JoinColumns({
	 *   @JoinColumn(name="certificate_id", referencedColumnName="id")
	 * })
	 */
	private $certificate;

	/**
	 * @var Model_VpnEndpoint
	 *
	 * @ManyToOne(targetEntity="Model_VpnEndpoint")
	 * @JoinColumns({
	 *   @JoinColumn(name="vpn_endpoint_id", referencedColumnName="id")
	 * })
	 */
	private $vpnEndpoint;

	public function __get($name)
	{
		//SOWN::send_irc_message("requesting " . $name);
		switch($name)
		{
			case "FQDN":
				return $this->getFQDN();
			case "name":
			case "hostname":
				return $this->getHostname();
			case "updatePoint":
				return $this->getUpdatePoint();
			default:
				return parent::__get($name);
		}
	}
	
	public function __set($name, $value)
	{
		switch($name)
		{
			case "FQDN":
			case "name":
			case "hostname":
			case "updatePoint":
				parent::__throwReadOnlyException($name);
			default:
				parent::__set($name, $value);
		}
	}

	protected function getFQDN()
	{
		return $this->hostname.'.sown.org.uk.';
	}

	protected function getHostname()
	{
		return 'node'.$this->boxNumber;
	}

	protected function getUpdatePoint()
	{
		$now2 = time();
		$near = date("Y-m-d 04:00:00");
		$now = date("Y-m-d H:i:s",$now2);

		if ($near > $now) {
			$unix = date("U",$now2);
			$day = 60 * 60 * 24;
			$unix = $unix - $day;
			return date("Y-m-d 04:00:00",$unix);
		} else {
			return $near;
		}
	}

	/**
	 * @PrePersist @PreUpdate
	 */
	public function validate()
	{
	}


	public static function getByMac($mac)
	{
		require_once('../mysql-dbo.php');
		$q = "SELECT nodes.* FROM nodes JOIN physical_interfaces ON nodes.id = physical_interfaces.node_id WHERE physical_interfaces.mac_address = ?";
		$params = array($mac);
		$res = query($q, $params, 'Model_Node');
		print_r($res);
	}

	public static function getByCertificate($pubkey)
	{
	}
}
