<?php
foreach($info as $class => $classinfo)
{
        foreach($classinfo as $i)
        {
                echo '<div class=\''.$class.'\'>'.$i.'</div>';
        }
}
?>
<p>Enter your username or email address and a password reset URL will be sent to you.</p>
<form method="POST">
        <table>
                <tr><th>Username / Email Address:&nbsp;</th><td><input name='username_email' type='text' size='50' /></td></tr>
                <tr><td colspan='2' style='text-align: center; padding-top: 10px;'><input type='submit' name='submit' value='Request Password Reset' /></td></tr>
        </table>
</form>

