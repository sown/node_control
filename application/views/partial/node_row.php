<?
	$style="";

	if ($shade)
		$shade = 'shade';
		
?>
        <tr class="sowntablerow<?=$shade?>"<?= $style?>>
	  <td><?= $node->id ?></td>
          <td><?= $node->boxNumber ?></td>
          <td><?= $node->firmwareImage ?></td>
          <td><?= $node->notes ?></td>
          <td class="icon"><a class="view" title="View" href="/admin/nodes/<?= $node->boxNumber ?>">&nbsp;</a></td>
          <td class="icon"><a class="edit" title="Edit" href="/admin/nodes/<?= $node->boxNumber ?>/edit">&nbsp;</a></td>
          <td class="icon"><a class="delete" title="Delete" href="/admin/nodes/<?= $node->boxNumber ?>/delete">&nbsp;</a></td>
        </tr>
