<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_SwitchVlanPort
 *
 * @Table(name="switch_vlan_ports")
 * @Entity
 */
class Model_SwitchVlanPort extends Model_Entity
{
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
         * @var Model_SwitchVlan
         *
         * @ManyToOne(targetEntity="Model_SwitchVlan")
         * @JoinColumns({
         *   @JoinColumn(name="switch_vlan_id", referencedColumnName="id")
         * })
         */
        protected $switchVlan;

	/**
         * @var Model_SwitchPort
         *
         * @ManyToOne(targetEntity="Model_SwitchPort")
         * @JoinColumns({
         *   @JoinColumn(name="switch_port_id", referencedColumnName="id")
         * })
         */
        protected $switchPort;

        /**
         * @var boolean $tagged
         * 
         * @Column(name="tagged", type="boolean", nullable=false)
         */
	protected $tagged;

	public function __toString()
	{
		$this->logUse();
		$str  = "SwitchVlan: {$this->id}, switch={$this->switch->id} switchVlan={$this->switchVlan->vlanNumber}, switchPort={$this->switchPort->portNumber}, tagged={$this->tagged}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='switch' id='switch_vlan_port{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>SwitchVlanPort</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML($this->switchPort->portNumber);
		$str .= $this->fieldHTML($this->tagged);
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($switchVlan, $switchPort, $tagged)
	{
		$obj = new Model_SwitchVlanPort();
		$obj->switch = $switchVlan->switch;
		$obj->switchVlan = $switchVlan;
		$obj->switchPort = $switchPort;
		$obj->tagged = $tagged;
		$obj->save();
		return $obj;
	}
	
	public static function getValuesForForm($switch, $action = '')
	{
                $switchVlanPorts = $switch->switchVlanPorts;
		$vlan_port_fields = array('id', 'switchVlan', 'switchPort', 'tagged');
                $switch_vlan_ports = array();
                $switch_vlan_port_ids = array();
		$formValues = array();
		$vp = 0;
                // Fixes bug where duplicate ports appear when a new port is added.
                foreach($switchVlanPorts as $svp => $switchVlanPort)
                {
                        if (!in_array($switchVlanPort->id, $switch_vlan_port_ids))
                        {
                                $switch_vlan_ports[] = $switchVlanPort;
                                $switch_vlan_port_ids[] = $switchVlanPort->id;
                        }
                }
                foreach ($switch_vlan_ports as $vp => $switchVlanPort)
                {
                	$formValues[$vp]['id'] = $switchVlanPort->id;
			if ($action == 'view')
			{
				$formValues[$vp]['switchVlan'] = $switchVlanPort->switchVlan->vlanNumber;
                                $formValues[$vp]['switchPort'] = $switchVlanPort->switchPort->portNumber;
			}
			else
			{
                        	$formValues[$vp]['switchVlan'] = $switchVlanPort->switchVlan->id;
                       	 	$formValues[$vp]['switchPort'] = $switchVlanPort->switchPort->id;
			}
                        $formValues[$vp]['tagged'] = $switchVlanPort->tagged;
                }
		if ($action == 'edit')
		{
			foreach ($vlan_port_fields as $vpf)
                        {
                                $formValues[$vp+1][$vpf] = '';
                        }
		}
		elseif ($action == 'view')
		{
			foreach ($formValues as $svp => $svpdata)
                        {
                                $formValues[$svp]['tagged'] = ($svpdata['tagged'] ? 'Yes' : 'No');
                        }
		}
		return $formValues;
	}

	public static function getFormTemplate($switch)
	{
		$switchPort_options = array();
		$switchVlan_options = array();
		if (isset($switch) && $switch->id > 0)
		{
			$switchPort_options = $switch->getSwitchPortOptions();
                	$switchVlan_options = $switch->getSwitchVlanOptions();
		}
		return array(
                        'width' => '100%',
                        'clear' => 'both',
                        'title' => 'Switch VLAN Ports',
                        'type' => 'table',
                        'fields' => array(
                                'id' => array('type' => 'hidden'),
                                'switchVlan' => array('title' => 'Switch VLAN', 'type' => 'select', 'options' => $switchVlan_options),
                                'switchPort' => array('title' => 'Switch Port', 'type' => 'select', 'options' => $switchPort_options),
				'tagged' => array('title' => 'Tagged', 'type' => 'checkbox'),
                        ),
                );
	}	

	public static function updateAll($formValues, $switch)
	{
		foreach ($formValues as $vp => $vlanPortValues)
                {
                        if (empty($vlanPortValues['switchVlan']) || empty($vlanPortValues['switchPort']))
                        {
                                if (!empty($vlanPortValues['id']))
                                {
                                        $vlanPort = Doctrine::em()->getRepository('Model_SwitchVlanPort')->find($vlanPortValues['id']);
                                        $vlanPort->delete();
                                }
                        }
                        else
                        {
                                $vlanPortValues['tagged'] = FormUtils::getCheckboxValue($vlanPortValues, 'tagged');
                                $switchVlan = Doctrine::em()->getRepository('Model_SwitchVlan')->find($vlanPortValues['switchVlan']);
                                $switchPort = Doctrine::em()->getRepository('Model_SwitchPort')->find($vlanPortValues['switchPort']);
                                if (empty($vlanPortValues['id'])) {
                                        $switch->switchVlanPorts->add(Model_SwitchVlanPort::build(
                                                $switchVlan,
                                                $switchPort,
                                                $vlanPortValues['tagged']
                                        ));
                                }
                                else
				{
                                        $switchVlanPort = Doctrine::em()->getRepository('Model_SwitchVlanPort')->find($vlanPortValues['id']);
                                        $switchVlanPort->switchVlan = $switchVlan;
                                        $switchVlanPort->switchPort = $switchPort;
                                        $switchVlanPort->tagged = $vlanPortValues['tagged'];
                                        $switchVlanPort->save();
                                }
                        }
                }
	}

}

