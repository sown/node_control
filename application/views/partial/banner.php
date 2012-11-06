<table class="banner">
<tr>
<?php
foreach ($bannerItems as $name => $url)
{
	echo '<td class="bannerItem">'; 
	if ($name == $title)
	{
		echo "<big>";
	}
	echo "<a href=\"$url\">$name</a>";
	if ($name == $title)
        {
                echo "</big>";
        }
	echo "</td>\n";
}
?>
</tr>
</tbody>
</table>
