<?
	$style="";

	if ($shade)
		$shade = 'shade';
		
?>
	<tr class="sowntablerow<?=$shade?>"<?= $style?>>
		<td><?= $node->id ?></td>
		<td><?= $node->boxNumber ?></td>
	</tr>
