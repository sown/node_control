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
		$nodes = Doctrine::em()->createQuery("SELECT n.boxNumber FROM Model_NodeDeployment nd JOIN nd.node n WHERE nd.deployment = " . $row->id . " ORDER BY nd.startDate DESC")->getResult();
			
                echo "          <td>" . $nodes[0]['boxNumber'] . "</td>\n";
        }
	elseif ($f == "currentDeployment")
	{
		$deployments = Doctrine::em()->createQuery("SELECT d.name FROM Model_NodeDeployment nd JOIN nd.deployment d WHERE nd.node = " . $row->id . " AND nd.endDate = '$latest_end_datetime' ORDER BY nd.startDate DESC")->getResult();
		if (sizeof($deployments) > 0) 
		{
			echo "          <td>" . $deployments[0]['name'] . "</td>\n";
		}
		else 
		{
			echo "          <td></td>\n";
		}
	}
	elseif ($f == "latestNote")
	{
		$latest_note = $row->latest_note();
		echo "<td>";
		if (is_object($latest_note)) 
		{
			echo $latest_note->text_only();
		}
		echo "</td>\n";
	}
	elseif (preg_match("/octet/", $f)) 
	{
		$row->$f = round($row->$f/1024/1024, 3);
		echo "          <td>" . $row->$f . "</td>\n";
	}
	elseif ($f == "calledstationid")
	{
		$csbits=explode(':', $row->$f);
		$nodename = str_replace('-', ':', $csbits[0]);
		$query = Doctrine::em()->createQuery("SELECT n FROM Model_Node n JOIN n.interfaces i JOIN i.networkAdapter na WHERE na.mac like '{$nodename}'");
		$query->setMaxResults(1);
		$nodes = $query->getResult();
		if (!empty($nodes[0])) 
		{
			$nodename = "node" . $nodes[0]->boxNumber;
		}
		echo "          <td>{$nodename} ({$csbits[1]})</td>\n";

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
