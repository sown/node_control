<table class="banner">
<tr>
<?php
if (!isset($subtitle))
{
	$subtitle = "";
}
foreach ($bannerItems as $name => $url)
{
	echo '<td class="bannerItem">'; 
	if ($name == $subtitle)
	{
		echo "<big>";
	}
	echo "<a href=\"$url\">$name</a>";
	if ($name == $subtitle)
        {
                echo "</big>";
        }
	echo "</td>\n";
}
?>
</tr>
</tbody>
</table>
