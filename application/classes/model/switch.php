<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_Switch
 *
 * @Table(name="switches")
 * @Entity
 */
class Model_Switch extends Model_Entity
{
	/**
	 * @var text $name
	 *
	 * @Column(name="name", type="text")
	 */
	protected $name;

	/**
	 * @var integer $enable
	 *
	 * @column(name="enable", type="integer", nullable=false)
	 */
	protected $enable;

	/**
	 * @var integer $enableVlan
	 *
	 * @column(name="enable_vlan", type="integer", nullable=false)
	 */
	protected $enableVlan;

	/**
	 * @var integer $reset
	 *
	 * @column(name="reset", type="integer", nullable=false)
	 */
	protected $reset;

	 /**
         * @OneToMany(targetEntity="Model_SwitchPort", mappedBy="switch", cascade={"persist", "remove"})
         */
        protected $switchPorts;

	/**
         * @OneToMany(targetEntity="Model_SwitchVlan", mappedBy="switch", cascade={"persist", "remove"})
         */
        protected $switchVlans;

	/**
         * @OneToMany(targetEntity="Model_SwitchVlanPort", mappedBy="switch")
         */
        protected $switchVlanPorts;

	public function __toString()
	{
		$this->logUse();
		$str  = "Switch: {$this->id}, name={$this->name}, enable={$this->enable}, enableVlan={$this->enableVlan}, reset={$this->reset},";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='switch' id='switch_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Switch</th><td>{$this->id}</td></tr>";
		foreach(array('name', 'enable', 'enableVlan', 'reset') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		foreach($this->switchPorts as $switchPort)
                {
			$str .= $this->fieldHTML('switchPort', $switchPort->toHTML());
                }
		foreach($this->switchVlans as $switchVlan)
                {
			$str .= $this->fieldHTML('switchVlan', $switchVlan->toHTML());
                }
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public function getSwitchVlanOptions()
	{
		$switchVlan_options = array('0' => '');
		$switchVlans = $this->switchVlans;
		foreach ($switchVlans as $sv => $switchVlan)
		{
			$switchVlan_options[$switchVlan->id] = $switchVlan->vlanNumber;
		}
		return $switchVlan_options;
	}

	public function getSwitchPortOptions()
        {
                $switchPort_options = array('0' => '');
		$switchPorts = $this->switchPorts;	
		if (is_object($switchPorts))
		{
		        foreach ($switchPorts as $sp => $switchPort)
        		{
                		$switchPort_options[$switchPort->id] = $switchPort->portNumber;
			}
		}
                return $switchPort_options;
        }

	public static function build($name)
	{
		$obj = new Model_Switch();
		$obj->name = $name;
		$obj->enable = 1;
		$obj->enableVlan = 1;
		$obj->reset = 1;
		return $obj;
	}

	public function cloneSwitch()
	{
		$newSwitch = new Model_Switch();
		$newSwitch->name = $this->name;
		$newSwitch->enable = $this->enable;
		$newSwitch->enableVlan = $this->enableVlan;
		$newSwitch->reset = $this->reset;
		$newSwitch->save();
		$newSwitchPorts = array();
		foreach ($this->switchPorts as $switchPort)
		{
			$newSwitchPort = new Model_SwitchPort();
			$newSwitchPort->switch = $newSwitch;
			$newSwitchPort->portNumber = $switchPort->portNumber;
			$newSwitchPort->primaryVlan = $switchPort->primaryVlan;
			$newSwitchPort->save();
			$newSwitchPorts[] = $newSwitchPort;
		}
		$newSwitchVlans = array();
		foreach ($this->switchVlans as $switchVlan)
                {
                        $newSwitchVlan = new Model_SwitchVlan();
                        $newSwitchVlan->switch = $newSwitch;
                        $newSwitchVlan->vlanNumber = $switchVlan->vlanNumber;
                        $newSwitchVlan->save();
                        $newSwitchVlans[] = $newSwitchVlan;
                }
		foreach ($this->switchVlanPorts as $switchVlanPort)
		{
			$svpVlanNumber = $switchVlanPort->switchVlan->vlanNumber;
			$svpPortNumber = $switchVlanPort->switchPort->portNumber;
			$newSwitchVlanPort = new Model_SwitchVlanPort();
			$newSwitchVlanPort->switch = $newSwitch;
			$newSwitchVlanPort->tagged = $switchVlanPort->tagged;
			foreach ($newSwitchVlans as $newSwitchVlan)
			{
				if ($newSwitchVlan->vlanNumber == $svpVlanNumber)
				{
					$newSwitchVlanPort->switchVlan = $newSwitchVlan;
					break;
				}
			}
			foreach ($newSwitchPorts as $newSwitchPort)
                        {
                                if ($newSwitchPort->portNumber == $svpPortNumber)
                                {
                                        $newSwitchVlanPort->switchPort = $newSwitchPort;
                                        break;
                                }
                        }
			$newSwitchVlanPort->save();
		}
		return $newSwitch;
	}
		
	public static function getValuesForForm($switch, $action = 'view')
	{
		return array(
                	'id' => $switch->id,
                        'name' => $switch->name,
                        'enable' => $switch->enable,
                        'enableVlan' => $switch->enableVlan,
                        'reset' => $switch->reset,
                        'switchPorts' => Model_SwitchPort::getValuesForForm($switch, $action),
                        'switchVlans' => Model_SwitchVlan::getValuesForForm($switch, $action),
                        'switchVlanPorts' => Model_SwitchVlanPort::getValuesForForm($switch, $action),
                );
	}

	public static function getFormTemplate($switch)
	{
		return array(
                        'title' => 'Switch',
                        'type' => 'fieldset',
                        'fields' => array(
                                'id' => array('title' => 'ID', 'type' => 'hidden'),
                                'name' => array('title' => 'Name', 'type' => 'input', 'size' => 10),
                                'enable' => array('title' => 'Enable', 'type' => 'input', 'size' => 2),
                                'enableVlan' => array('title' => 'Enable VLAN', 'type' => 'input', 'size' => 2),
                                'reset' => array('title' => 'Reset', 'type' => 'input', 'size' => 2),
                                'switchPorts' => Model_SwitchPort::getFormTemplate(),
                                'switchVlans' => Model_SwitchVlan::getFormTemplate(),
                                'switchVlanPorts' => Model_SwitchVlanPort::getFormTemplate($switch),
                        ),
		);
	}

	public static function update($switchId, $formValues)
	{
		$switch = Doctrine::em()->getRepository('Model_Switch')->find($switchId);
		$switch->name = $formValues['name'];
                $switch->enable = $formValues['enable'];
                $switch->enableVlan = $formValues['enableVlan'];
                $switch->reset = $formValues['reset'];
		$switch->save();
		Model_SwitchPort::updateAll($formValues['switchPorts'], $switch);
		Model_SwitchVlan::updateAll($formValues['switchVlans'], $switch);
		Model_SwitchVlanPort::updateAll($formValues['switchVlanPorts'], $switch);	
	}
}
