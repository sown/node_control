<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * Model_Enquiry
 *
 * @Table(name="enquiries")
 * @Entity
 */
class Model_Enquiry extends Model_Entity
{
	/**
         * @var Model_EnquiryType
         *
         * @ManyToOne(targetEntity="Model_EnquiryType")
         * @JoinColumns({
         *   @JoinColumn(name="type_id", referencedColumnName="id")
         * })
         */
        protected $type;

	/**
         * @var datetime $dateSent
         *
         * @Column(name="date_sent", type="datetime", nullable=false)
         */
	protected $dateSent;

	/**
	 * @var string $fromName
	 *
	 * @Column(name="from_name", type="string", length=255, nullable=false)
	 */
	protected $fromName;

	/**
         * @var string $fromEmail
         *
         * @Column(name="from_email", type="string", length=255, nullable=false)
         */
        protected $fromEmail;

	/**
         * @var string $subject
         *
         * @Column(name="subject", type="string", length=255, nullable=false)
         */
        protected $subject;

        /**
         * @var text $message
         *
         * @Column(name="message", type="text", nullable=false)
         */
        protected $message;


	/**
         * @var string $ipAddress
         *
         * @Column(name="ip_address", type="string", length=255, nullable=false)
         */
        protected $ipAddress;

	/**
         * @var string $responseSummary
         *
         * @Column(name="response_summary", type="string", length=255, nullable=true)
         */
        protected $responseSummary;

	/**
         * @var text $response
         *
         * @Column(name="response", type="text", nullable=true)
         */
        protected $response;

	/**
         * @var datetime $acknowledgedUntil
         *
         * @Column(name="acknowledged_until", type="datetime", nullable=true)
         */
        protected $acknowledgedUntil;

	public function __get($name)
	{
		$this->logUse();
		switch($name)
		{
			case "from":
				return $this->fromName . "<" . $this->fromEmail . ">";
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

	public static function getUnresponded($type = NULL)
	{
		if (empty($type))
		{ 
			return Doctrine::em()->getRepository('Model_Enquiry')->findBy(array('responseSummary' => NULL), array('id' => 'DESC'));
		}
		return Doctrine::em()->getRepository('Model_Enquiry')->findBy(array('responseSummary' => NULL, 'type' => $type), array('id' => 'DESC'));
		
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "Enquiry: {$this->id}, dateSent={$this->dateSent->format('Y-m-d H:i:s')}, fromName={$this->fromName}, fromEmail={$this->fromEmail}, subject={$this->subject}, message={$this->message}, ipAddress={$this->ipAddress}, responseSummary={$this->responseSummary}, response={$this->response}, dateSent={$this->dateSent->format('Y-m-d H:i:s')}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='enquiry' id='enquiry_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Enquiry</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML($this->dateSent->format('Y-m-d H:i:s'));
		foreach(array('fromName', 'fromEmail', 'subject', 'message', 'ipAddress', 'responseSummary', 'response') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= $this->fieldHTML($this->acknowledgedUntil->format('Y-m-d H:i:s'));
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($type, $fromName, $fromEmail, $subject, $message, $ipAddress)
	{
		$obj = new Model_Enquiry();
		$obj->type = $type;
		$obj->dateSent = new \DateTime();
		$obj->fromName = $fromName;
		$obj->fromEmail = $fromEmail;
		$obj->subject = $subject;
		$obj->message = $message;
		$obj->ipAddress = $ipAddress;
		return $obj;
	}
}
