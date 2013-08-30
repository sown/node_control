<?php
if (empty($limit)) 
	$percent = 0;
else 
	$percent = round($used/$limit*100);
if ($percent >= 100)
{
        $percent=100;
        $color="red";
}
elseif ($percent >= 80) 
	$color="orange";
elseif ($percent >= 60) 
	$color="yellow";
else $color="green";
?>
<table style="margin-left: auto; margin-right: auto;" width="400px" height="10px">
  <tr>
<?php
for ($i=1; $i <= 100; $i++){
        if ($i==1) $markers="border-left: 1px solid black;";
        else if ($i%20 == 0) $markers="border-right: 1px solid black;";
        else $markers="";
        if ($i<=$percent) 
		echo "    <td width=\"1%\" style=\"background-color: $color; border-top: 1px solid black; border-bottom: 1px solid black; $markers\"></td>\n";
        else 
		echo "    <td width=\"1%\" style=\"border-top: 1px solid black; border-bottom: 1px solid black; $markers\"></td>\n";
}
?>
  </tr>
</table>
