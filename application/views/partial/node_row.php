<?
	$style="";

	if ($shade)
		$shade = 'shade';
		
?>
	<tr class="sowntablerow<?=$shade?>"<?= $style?>>
		<td><?= $node->id ?></td>
		<td><?= $node->deployments ?></td>
		<td><?= $node->type ?></td>
	</tr>
