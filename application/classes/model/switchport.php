<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_SwitchPort
 *
 * @Table(name="switch_ports")
 * @Entity
 */
class Model_SwitchPort extends Model_Entity
{
	/**
	 * @var integer $portNumber
	 *
	 * @column(name="port_number", type="integer", nullable=false)
	 */
	protected $portNumber;

	/**
	 * @var integer $primaryVlan
	 *
	 * @column(name="primary_vlan", type="integer", nullable=false)
	 */
	protected $primaryVlan;

        /**
         * @var Model_Switch
         *
         * @ManyToOne(targetEntity="Model_Switch")
         * @JoinColumns({
         *   @JoinColumn(name="switch_id", referencedColumnName="id")
         * })
         */
        protected $switch;

 	/**
         * @OneToMany(targetEntity="Model_SwitchVlanPort", mappedBy="switchPort", cascade={"persist", "remove"})
         */
        protected $switchVlanPorts;

	public function __toString()
	{
		$this->logUse();
		$str  = "SwitchPort: {$this->id}, portNumber={$this->portNumber}, primaryVlan={$this->primaryVlan}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='switch_port' id='switch_port_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Switch Port</th><td>{$this->id}</td></tr>";
		foreach(array('portNumber', 'primaryVlan') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($switch, $portNumber, $primaryVlan)
	{
		$obj = new Model_SwitchPort();
		$obj->switch = $switch;
		$obj->portNumber = $portNumber;
		$obj->primaryVlan = $primaryVlan;
		$obj->save();
		return $obj;
	}

	public static function getValuesForForm($switch, $action = 'view')
        {
		$switchPorts = array();
		if (is_array($switch->switchPorts))
		{
			$switchPorts = $switch->switchPorts;
		}
                $port_fields = array('id', 'portNumber', 'primaryVlan');
                $switch_ports = array();
                $switch_port_ids = array();
                $formValues = array();
		$p = 0;
                // Fixes bug where duplicate ports appear when a new port is added.
                foreach($switchPorts as $sp => $switchPort)
                {
                        if (!in_array($switchPort->id, $switch_port_ids))
                        {
                                $switch_ports[] = $switchPort;
                                $switch_port_ids[] = $switchPort->id;
                        }
                }
                foreach ($switch_ports as $p => $switchPort)
                {
                        foreach ($port_fields as $pf)
                        {
                                $formValues[$p][$pf] = $switchPort->$pf;
                        }
                }
		if ($action == 'edit')
		{
			foreach ($port_fields as $pf)
        	        {
                		$formValues[$p+1][$pf] = '';
                	}
		}
		return $formValues;
        }

	public static function getFormTemplate()
	{
		return array(
                 	'width' => '49%',
                        'float' => 'right',
                        'title' => 'Switch Ports',
                        'type' => 'table',
                        'fields' => array(
                        	'id' => array('type' => 'hidden'),
                                'portNumber' => array('title' => 'Port Number', 'type' => 'input', 'size' => 5),
                                'primaryVlan' => array('title' => 'Primary VLAN', 'type' => 'input', 'size' => 5),
                        ),
		);		
	}

	public static function updateAll($formValues, $switch)
	{
		foreach ($formValues as $p => $portValues)
                {
                        if (empty($portValues['portNumber']) && empty($portValues['primaryVlan']))
                        {
                                if (!empty($portValues['id']))
                                {
                                        $port = Doctrine::em()->getRepository('Model_SwitchPort')->find($portValues['id']);
                                        $port->delete();
                                }
                        }
                        else
                        {
                                if (empty($portValues['id'])) {
                                        $switch->switchPorts->add(Model_SwitchPort::build(
                                                $switch,
                                                $portValues['portNumber'],
                                                $portValues['primaryVlan']
                                        ));
                                }
                                else
                                {
                                        $switchPort = Doctrine::em()->getRepository('Model_SwitchPort')->find($portValues['id']);
                                        $switchPort->portNumber = $portValues['portNumber'];
                                        $switchPort->primaryVlan = $portValues['primaryVlan'];
                                        $switchPort->save();
                                }
                        }
                }
	}
}

