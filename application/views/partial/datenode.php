<div align="center">
<?php
$nodes = Doctrine::em()->getRepository('Model_Node')->findAll();
$nodeoptions[] = "";
foreach ($nodes as $anode) 
{
	$nodeoptions[$anode->boxNumber] = $anode->boxNumber;
}
asort($nodeoptions);
$formTemplate = array(
	'date' => array('type' => 'date', 'title' => 'Date'),
	'node' => array('type' => 'select', 'title' => 'Node', 'options' => $nodeoptions),
);
$formValues = array(
	'date' => $date,
	'node' => $node,
);
echo FormUtils::drawForm('Date_Node', $formTemplate, $formValues, array('filter' => 'Filter'), array(), "", array('inline' => true));
?>
</div>
<br/>
