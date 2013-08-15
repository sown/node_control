<div align="center" style="display: inline;">
<?php
$formTemplate = array(
	'page' => array('type' => 'input', 'title' => 'Page', 'size' => strlen($maxpages), 'hint' => "<big><b>/ $maxpages</b></big>"),
);
$formValues = array(
	'page' => $page,
);
if (!empty($hiddenfields))
{
	foreach ($hiddenfields as $name => $value) 
	{
		$formTemplate[$name] = array('type' => 'hidden');
		$formValues[$name] = $value;
	}
}
echo FormUtils::drawForm('Pages', $formTemplate, $formValues, array('go' => 'Go'), array(), "", array('inline' => true));
?>
</div>
<br/>
