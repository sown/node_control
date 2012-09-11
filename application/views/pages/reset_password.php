<?php
foreach($info as $class => $classinfo)
{
	foreach($classinfo as $i)
	{
		echo '<div class=\''.$class.'\'>'.$i.'</div>';
	}
}
if ($show_form)
{
?>
<form method="POST">
	<input name='reset_password_hash' type='hidden' />
	<table>
		<tr><th>Username:</th><td><?= $username ?></td></th>
		<tr><th>New Password:</th><td><input name='password1' type='password' /></td></tr>
		<tr><th>Confirm Password:</th><td><input name='password2' type='password' /></td></tr>
		<tr><th></th><td><input type='submit' name='submit' value='Reset Password' /></td></tr>
	</table>
</form>
<?php } ?>
