<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * Model_NodeSetupRequest
 *
 * @Table(name="node_setup_requests")
 * @Entity
 */
class Model_NodeSetupRequest extends Model_Entity
{
	/**
         * @var string $nonce
         *
         * @Column(name="nonce", type="string", length=128, nullable=false)
         */
        protected $nonce;

	/**
         * @var string $mac
         *
         * @Column(name="mac", type="string", length=17, nullable=false)
         */
        protected $mac;

	/**
         * @var datetime $requestedDate
         *
         * @Column(name="requested_date", type="datetime", nullable=false)
         */
        protected $requestedDate;

	/**
         * @var string $status
         *
         * @Column(name="status", type="string", length=255, nullable=false)
         */
        protected $status;
	
 	/**
         * @var string $ipAddr
         *
         * @Column(name="ip_address", type="string", length=39, nullable=false)
         */
        protected $ipAddr;
	
	/**
	 * @var Model_User
	 *
	 * @ManyToOne(targetEntity="Model_User")
	 * @JoinColumns({
	 *   @JoinColumn(name="approved_by", referencedColumnName="id")
	 * })
	 */
	protected $approvedBy;

        /**
         * @var datetime $approvedDate
         *
         * @Column(name="approved_date", type="datetime", nullable=false)
         */
        protected $approvedDate;

	/**
         * @var datetime $expiryDate
         *
         * @Column(name="expiry_date", type="datetime", nullable=false)
         */
        protected $expiryDate;

	/**
         * @var datetime $password
         *
         * @Column(name="password", type="string", length=255, nullable=false)
         */
        protected $password;

	/**
         * @var Model_Node
         *
         * @ManyToOne(targetEntity="Model_Node")
         * @JoinColumns({
         *   @JoinColumn(name="node_id", referencedColumnName="id")
         * })
         */
        protected $node;

	public function __construct()
	{
		parent::__construct();
	}

	public function __get($name)
	{
		$this->logUse();
		if (property_exists($this, $name))
		{
			return $this->$name;
		}
		else
		{
			return parent::__get($name);
		}
	}
	
	public function __set($name, $value)
	{
		if (property_exists($this, $name))
		{
			$this->$name = $value;
		}
		else
		{
			parent::__set($name, $value);
		}
	}

	/**
	 * @PrePersist @PreUpdate
	 */
	public function validate()
	{
	}

	public function __toString()
	{
		$this->logUse();
		$password = $this->password;
		$password = ( empty($password) ? '[UNSET]' : '[SET]' );
		$str  = "NodeSetupRequest: {$this->id}, nonce={$this->nonce}, mac={$this->mac}, requestedDate={$this->requestedDate->format('Y-m-d H:i:s')}, ipAddr={$this->ipAddr}, status={$this->status}, approvedBy={$this->approvedBy->id}, approvedDate={$this->approvedDate->format('Y-m-d H:i:s')}, expiryDate={$this->expiryDate->format('Y-m-d H:i:s')}, node={$this->node->id}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
                $password = $this->password;
                $this->password = ( empty($password) ? '[UNSET]' : '[SET]' );
		$str  = "<div class='node' id='node_setup_request_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>NodeSetupRequest</th><td>{$this->id}</td></tr>";
		foreach(array('nonce', 'mac', 'ipAddr', 'status', 'password') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		foreach(array('requestedDate', 'approvedDate', 'expiryDate') as $field)
		{
			$date = $this->$field;
			if (!empty($date))
			{			
				$str .= $this->fieldHTML($field, $date->format('Y-m-d H:i:s'));
			}
			else
			{
				$str .= $this->fieldHTML($field, "");
			}
		}
		$approvedBy = $this->approvedBy;
		if (is_object($approvedBy))
                {
                        $str .= $this->fieldHTML('approvedBy', $approvedBy->toHTML());
                }
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

}
