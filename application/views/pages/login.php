<?php
$isDevSite = Kohana::$config->load('system.default.admin_system.development');
if (!empty($isDevSite))
{
        $favicon = "/media/images/favicon-development.ico";
        $logo = "/media/images/sown_adminsys-development.png";
}
else
{
        $favicon = "/media/images/favicon-default.ico";
        $logo = "/media/images/sown_adminsys-default.png";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Login | SOWN Admin System</title>
  <?= HTML::style('media/css/sown.css', array("media" => "all")) ?> 
  <?= HTML::style('media/css/screen.css', array("media" => "screen")) ?> 
  <?= HTML::style('media/css/handheld.css', array("media" => "handheld")) ?> 
  <?= HTML::style('media/css/login.css', array("media" => "all")) ?> 
  <link<?= HTML::attributes(array("rel" => "icon", "href" => $favicon, "type" => "image/vnd.microsoft.icon")) ?> />
  <link<?= HTML::attributes(array("rel" => "shortcut icon", "href" => $favicon, "type" => "image/vnd.microsoft.icon")) ?> />
</head>
<body>
   <div id="top_menu">
      <div class="menucontainer">
         <div>
	    <a href="/"><img<?= HTML::attributes(array("class" => "logo", "src" => $logo, "alt" => "Admin system logo")) ?>></a>
	 </div>
      </div>
      <div class="menucontainer"><div class="item"><a href="http://www.sown.org.uk/wiki/">Wiki</a></div></div>
      <div class="menucontainer"><div class="item"><a href="https://www.suws.org.uk/wp/">SUWS</a></div></div>
      <div class="menucontainer"><div class="item"><a href="http://www.sown.org.uk/connect/">Connect</a></div></div>
      <div class="menucontainer"><div class="item"><a href="http://www.sown.org.uk/contact/">Contact&nbsp;Us</a></div></div>
   </div>
<?= View::factory('partial/devsplash') ?>   
   <div class="break" />
   <div>

      <div id="main_body">
         <div class="content">
            <div class="hr">
	       <hr/>
	    </div>
	<div id='login'>
		<h3>Welcome to the <abbr title="Southampton Open Wireless Network">SOWN</abbr> Admin System</h3>
		<!-- Process node messages -->
		<?php if (isset($message)) echo '<div class="message">' . $message . '</div>'; ?>
		<p>Please enter your login and password below</p>
		<br />
		<form method="post" action="" id='login_form'>
			<input type="hidden" name="uri" value="/admin/"/>
			<table>
				<!-- 'Login' row -->
				<tr>
					<td class='legend'>
						<label for='username'>Login:</label>
					</td><td class='fields'>
						<input type="text" name="username" size="32" id="username" />
					</td>
				</tr>
				<!-- 'Password' row -->
				<tr>
					<td class='legend'>
						<label for='password'>Password:</label>
					</td><td class='fields'>
						<input type="password" name="password" size="32" id="password" />
					</td>
				</tr>
			</table>
			<p>
				<input type="submit" value="Login" name="submit" id='submit'/>
			</p>
		</form>
	</div>
	<div id='valid_institutions'>
		<h4>Users from the following domains can login to managed their accounts or a node that they host:</h4>
	
		<dl class="list">
                        <dt><abbr title="Southampton Open Wireless Network">SOWN</abbr> <span class='no_bold'>(e.g. username@sown.org.uk)</span></dt>
                                <dd>&raquo; If you have forgotten your password <a href="forgot_password">click here</a>.</dd>
			<dt>University of Southampton <span class='no_bold'>(e.g. username@soton.ac.uk)</span></dt>
				<dd>&raquo; This uses your <strong>iSolutions</strong> username &amp; password.</dd>
		</dl>
	</div>
      </div>
    </div>
  </div>

  <div class="hr"><hr/></div>
  <div id="footer">&copy; SOWN 2007-<?= date('Y') ?></div>
</body>

</html>
