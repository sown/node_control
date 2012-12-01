<?
	$style="";

	if ($shade)
		$shade = 'shade';
		
?>
        <tr class="sowntablerow<?=$shade?>"<?= $style?>>
<?php
$latest_end_datetime = Kohana::$config->load('system.default.admin_system.latest_end_datetime');
foreach ($fields as $f => $field) 
{
	if (in_array($f, array("configure", "delete", "edit", "usage", "view")))
	{
		$url = Route::url($f . "_" . $objectType, array($idField => $row->$idField));
		echo "          <td class=\"icon\"><a class=\"$f\" title=\"" . ucfirst($f) . "\" href=\"$url\">&nbsp;</a></td>\n";
	}
	// Need to figure out how to do this generically
	elseif ($f == "certificateWritten")
	{
		echo "          <td>" . ( (strlen($row->certificate->privateKey) > 0) ? 'Yes' : 'No')  . "</td>\n";
	}
	elseif ($f == "deploymentBoxNumber")
        {
		$nodes = Doctrine::em()->createQuery("SELECT n.boxNumber FROM Model_NodeDeployment nd JOIN nd.node n WHERE nd.endDate = '$latest_end_datetime' AND nd.deployment = " . $row->id)->getResult();
			
                echo "          <td>" . $nodes[0]['boxNumber'] . "</td>\n";
        }
	else
	{
		if (gettype($row->$f) == "object" && get_class($row->$f) == "DateTime")
		{
			$row->$f = $row->$f->format('Y-m-d H:i:s');
			if ($row->$f == $latest_end_datetime)
			{
				$row->$f = "";
			}
		}
		echo "          <td>" . $row->$f . "</td>\n";
	}
}
?>
        </tr>
