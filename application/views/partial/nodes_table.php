<?
	$columns = array(
		'id'          => 'ID',
		'boxNumber' => 'Name',
		'firmwareImage' => 'Firmware Image',
		'notes' => 'Notes',
		'view' => '',
		'edit' => '',
		'delete' => '',
	);
	
	if(isset($remove) && is_array($remove))
	{
		foreach ($remove as $item) {
			unset($columns[$item]);
		}
	}
	
	$shade = TRUE;
?>
<table class="sowntable">
	<thead>
		<tr class="tabletitle">
		<?php foreach ($columns as $key => $title): ?>
			<?php echo "<th id='$key'>$title</th>\n"; ?>
		<?php endforeach ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($nodes as $node): ?>
			<?php echo View::factory('partial/node_row')
				->bind('node', $node)
				->bind('columns', $columns)
				->set('shade', $shade = !$shade); ?>
		<?php endforeach ?>
	</tbody>
</table>
