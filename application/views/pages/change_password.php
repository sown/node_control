<?php
foreach($info as $class => $classinfo)
{
	foreach($classinfo as $i)
	{
		echo '<div class=\''.$class.'\'>'.$i.'</div>';
	}
}
?>
<form method="POST">
	<table>
		<tr><th>Username:</th><td><?= $username ?></td></th>
		<tr><th>Current Password:</th><td><input name='oldpassword' type='password' /></td></tr>
		<tr><th>New Password:</th><td><input name='password1' type='password' /></td></tr>
		<tr><th>Confirm Password:</th><td><input name='password2' type='password' /></td></tr>
		<tr><th></th><td><input type='submit' name='submit' value='Change Password' /></td></tr>
	</table>
</form>
