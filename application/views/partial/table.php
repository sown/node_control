<?
	if(isset($remove) && is_array($remove))
	{
		foreach ($remove as $item) {
			unset($fields[$item]);
		}
	}
	
	$shade = TRUE;
?>
<table class="sowntable">
	<thead>
		<tr class="tabletitle">
		<?php foreach ($fields as $key => $title): ?>
			<?php echo "<th id='$key'>$title</th>\n"; ?>
		<?php endforeach ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($rows as $row): ?>
			<?php echo View::factory('partial/table_row')
				->bind('row', $row)
				->bind('fields', $fields)
				->bind('objectType', $objectType)
				->bind('idField', $idField)
				->set('shade', $shade = !$shade); ?>
		<?php endforeach ?>
	</tbody>
</table>
