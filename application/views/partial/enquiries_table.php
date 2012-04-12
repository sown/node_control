<?
	$columns = array(
		'date_sent' => 'Date Sent',
		'type'      => 'Type',
		'from'      => 'From',
		'subject'   => 'Subject',
		'response'  => 'Response',
		'acknowledged_until' => 'Acknowledged Until?'
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
		<tr>
		<?php foreach ($columns as $key => $title): ?>
			<?php echo "<th class='$key'>$title</th>\n"; ?>
		<?php endforeach ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($enquiries as $enq): ?>
			<?php echo View::factory('partial/enquiry_row')
				->bind('enquiry', $enq)
				->bind('columns', $columns)
				->set('shade', $shade = !$shade); ?>
		<?php endforeach ?>
	</tbody>
</table>
