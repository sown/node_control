<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_SwitchVlan
 *
 * @Table(name="switch_vlans")
 * @Entity
 */
class Model_SwitchVlan extends Model_Entity
{
	/**
	 * @var integer $vlanNumber
	 *
	 * @column(name="vlan_number", type="integer", nullable=false)
	 */
	protected $vlanNumber;

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
         * @OneToMany(targetEntity="Model_SwitchVlanPort", mappedBy="switchVlan", cascade={"persist", "remove"})
         */	
	protected $switchVlanPorts;

	public function __toString()
	{
		$this->logUse();
		$str  = "SwitchVlan: {$this->id}, vlanNumber={$this->vlanNumber}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='switch_vlan' id='switch_vlan_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Switch Vlan</th><td>{$this->id}</td></tr>";
		foreach(array('vlanNumber') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		foreach($this->switchVlanPorts as $switchVlanPort)
                {
                        $str .= $this->fieldHTML('switchVlanPort', $switchVlanPort->toHTML());
                }
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($switch, $vlanNumber)
	{
		$obj = new Model_SwitchVlan();
		$obj->switch = $switch;
		$obj->vlanNumber = $vlanNumber;
		$obj->save();
		return $obj;
	}

	public static function getValuesForForm($switch, $action = 'view')
	{
		$switchVlans = array();
                if (is_array($switch->switchVlans))
                {
                        $switchVlans = $switch->switchVlans;
                }
		$vlan_fields = array('id', 'vlanNumber');
                $switch_vlans = array();
                $switch_vlan_ids = array();
		$formValues = array();
		$v = 0;
                // Fixes bug where duplicate ports appear when a new port is added.
                foreach($switchVlans as $sv => $switchVlan)
                {
                        if (!in_array($switchVlan->id, $switch_vlan_ids))
                        {
                                $switch_vlans[] = $switchVlan;
                                $switch_vlan_ids[] = $switchVlan->id;
                        }
                }
                foreach ($switch_vlans as $v => $switchVlan)
                {
                        foreach ($vlan_fields as $vf)
                        {
                        	$formValues[$v][$vf] = $switchVlan->$vf;
                        }
                }
		if ($action == 'edit')
		{
			foreach ($vlan_fields as $vf)
                        {
                                $formValues[$v+1][$vf] = '';
                        }
		}
		return $formValues;
	}

	public static function getFormTemplate()
	{
		return array(
                	'width' => '49%',
                        'float' => 'left',
                        'title' => 'Switch VLANs',
                        'type' => 'table',
                        'fields' => array(
                        	'id' => array('type' => 'hidden'),
                                'vlanNumber' => array('title' => 'VLAN Number', 'type' => 'input', 'size' => 5),
	                ),
		);
	}

	public static function updateAll($formValues, $switch)
	{
		foreach ($formValues as $v => $vlanValues)
                {
                        if (empty($vlanValues['vlanNumber']) && empty($vlanValues['primaryVlan']))
                        {
                                if (!empty($vlanValues['id']))
                                {
                                        $vlan = Doctrine::em()->getRepository('Model_SwitchVlan')->find($vlanValues['id']);
                                        $vlan->delete();
                                }
                        }
                        else
                        {
                                if (empty($vlanValues['id'])) {
                                        $switch->switchVlans->add(Model_SwitchVlan::build(
                                                $switch,
                                                $vlanValues['vlanNumber']
                                        ));
                                }
                                else
                                {
                                        $switchVlan = Doctrine::em()->getRepository('Model_SwitchVlan')->find($vlanValues['id']);
                                        $switchVlan->vlanNumber = $vlanValues['vlanNumber'];
                                        $switchVlan->save();
                                }
                        }
                }
        }

}

