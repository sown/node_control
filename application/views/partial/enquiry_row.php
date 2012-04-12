<?
	$enq = $enquiry;
	
	$subject = $enq->subject;
	if (strlen($subject)>27)
		$subject = substr($enq->subject, 0, 25) . "...";
	
	if (! $enq->is_handled())
		$style=' style="font-weight: bold;"';
	else
		$style="";
		
	$response_summary = $enq->response_summary;
	if ($response_summary == null)
		$response_summary = '';
	else if (strlen($response_summary) > 27)
		$response_summary = substr($response_summary, 0, 25)."...";
	
	if ($shade)
		$shade = 'shade';
		
?>
	<tr class="sowntablerow<?=$shade?>"<?= $style?>>
		<td><?= date('D j M Y', $enq->date_sent)?></td>
	<? if (isset($columns['type'])): ?>
		<td><a href="<?= URL::query(array('type' => $enq->type->id )) ?>"><?=$enq->type->title?></a></td>
	<? endif ?>
		<td><a href="mailto:<?=$enq->from_email?>"><?=$enq->from_name?><br /><small>(<?=$enq->from_email?>)</small></a></td>
		<td><a href="<?=URL::query(array('id' => $enq->id, 'type' => NULL, 'andresponded' => NULL ))?>"><?=$subject?></a></td>
	<? if(isset($columns['response'])): ?>
		<td><?=$response_summary?></td>
            <td><? if ($enq->acknowledged_until > 0 && $enq->acknowledged_until>time()) echo date('H:i:s\<\b\r\>D j M Y', $enq->acknowledged_until)?></td>
	<? endif ?>
	</tr>
