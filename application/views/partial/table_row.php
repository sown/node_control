<?
	$style="";

	if ($shade)
		$shade = 'shade';
		
?>
        <tr class="sowntablerow<?=$shade?>"<?= $style?>>
<?php
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
	else
	{
		if (gettype($row->$f) == "object" && get_class($row->$f) == "DateTime")
		{
			$row->$f = $row->$f->format('Y-m-d H:i:s');
		}
		echo "          <td>" . $row->$f . "</td>\n";
	}
}
?>
        </tr>
