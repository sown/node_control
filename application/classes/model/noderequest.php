<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_NodeRequest
 *
 * @Table(name="node_requests")
 * @Entity
 */
class Model_NodeRequest extends Model_Entity
{

	/**
         * @var string $name
         *
         * @Column(name="name", type="string", nullable=false)
         */
        protected $name;

	/**
         * @var string $email
         *
         * @Column(name="email", type="string", nullable=false)
         */
        protected $email;

	/**
         * @var string $contactNumber
         *
         * @Column(name="contact_no", type="string", nullable=false)
         */
        protected $contactNumber;

	/**
         * @var string $course
         *
         * @Column(name="course", type="string", nullable=false)
         */
        protected $course;

	/**
         * @var string $year
         *
         * @Column(name="year", type="string", nullable=false)
         */
        protected $year;

 	/**
         * @var string $houseNumber
         *
         * @Column(name="houseno", type="string", nullable=false)
         */
        protected $houseNumber;

	/**
         * @var string $street
         *
         * @Column(name="street", type="string", nullable=false)
         */
        protected $street;

	/**
         * @var string $postcode
         *
         * @Column(name="postcode", type="string", nullable=false)
         */
        protected $postcode;

	/**
         * @var string $facilities
         *
         * @Column(name="facilities", type="text", nullable=false)
         */
        protected $facilities;

	/**
         * @var datetime $requestedDate
         *
         * @Column(name="timestamp", type="datetime", nullable=false)
         */
        protected $requestedDate;

	/**
         * @var string $latitude
         *
         * @Column(name="lat", type="string", nullable=false)
         */
        protected $latitude;

	/**
         * @var string $longitude
         *
         * @Column(name="longitude", type="string", nullable=false)
         */
        protected $longitude;

	/**
         * @var integer $approved
         *
         * @Column(name="approved", type="integer", nullable=false)
         */
        protected $approved;

	/**
         * @var string $notes
         *
         * @Column(name="notes", type="text", nullable=false)
         */
        protected $notes;

	/**
         * @var integer $deploymentId
         *
         * @Column(name="deployment_id", type="integer", nullable=false)
         */
        protected $deploymentId;
	
	/**
         * @var Model_Deployment
         *
         * @OneToOne(targetEntity="Model_Deployment")
         * @JoinColumns({
         *   @JoinColumn(name="deployment_id", referencedColumnName="id")
         * })
         */
        protected $deployment;

	public function __get($name)
	{
		$this->logUse();
		switch($name)
		{
			case "requester":
                                return $this->getRequester();
                        case "address":
                                return $this->getAddress();
                        case "location":
                                return $this->getLocation();
                        case "status":
                                return $this->getStatus();
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

	public static function build()
	{
		$nodeRequest = new Model_NodeRequest();
		// Setting values to be added
		return $nodeRequest;
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "NodeRequest: {$this->id}, name={$this->name}, email={$this->email}, contactNumber={$this->contactNumber}, course={$this->course}, year={$this->year}, houseNumber={$this->houseNumber}, street={$this->street}, postcode={$this->postcode}, facilities={$this->facilities}, requestedDate={$this->requestedDate->format('Y-m-d H:i:s')}, latitude={$this->latitude}, longitude={$this->longitude}, approved={$this->approved}, notes={$this->notes}, deploymentId={$this->deploymentId}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='nodeRequest' id='nodeRequest_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Node Request</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('date', $this->requestedDate->format('Y-m-d H:i:s'));
		foreach(array('name', 'email', 'contactNumber', 'course', 'year', 'houseNumber', 'street', 'postcode', 'facilities', 'latitude', 'longitude', 'approved', 'notes') as $field)
                {
                        $str .= $this->fieldHTML($field);
                }
                if($this->deployment != null)
                {
                       	$str .= $this->fieldHTML('deployment', $this->deployment->toHTML());
		}
		elseif ($this->deploymentId == -1) {
			$this->fieldHTML('deployment', 'OLD NODE DEPLOYMENT');
		}			
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public function getRequester()
        {
		return $this->name . " &lt;" . $this->email . "&gt;, (" . $this->year . " " . $this->course . ")";
	}

	public function getAddress()
	{
		return $this->houseNumber." " . $this->street . ", " . $this->postcode;
	}

	public function getLocation()
	{
		if (!empty($this->latitude)) 
		{
			return SOWN::formatted_decimal_to_minute_second_degrees($this->latitude, 'latitude', TRUE) . ", " . SOWN::formatted_decimal_to_minute_second_degrees($this->longitude, 'longitude', TRUE);
		}
		return "UNKNOWN";
	}
	
	public function getStatus()
	{
		if ($this->approved === NULL) 
		{
			return "Undecided";;
		}
		switch($this->approved) {
			
			case '0':
                                return "Rejected";
			case '1':
				return "Approved";
			case '2':
			case '-1':
				$state = "Deployed";
				if ($this->approved == -1) 
				{
					$state = "Returned";
				}
				if (empty($this->deploymentId)) 
				{
					return "$state (Unknown Deployment)";
				}
				if ($this->deploymentId == -1)
				{
					return "$state (Old Deployment)";
				}
				return "$state (".$this->deployment->name." #".$this->deployment->boxNumber.")";
			default:
				return "UNKNOWN (".$this->approved.")";
		}
	}
}
