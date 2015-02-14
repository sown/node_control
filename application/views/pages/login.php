<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Login | SOWN Admin System</title>
  <?= HTML::style('media/css/sown.css', array("media" => "all")) ?> 
  <?= HTML::style('media/css/screen.css', array("media" => "screen")) ?> 
  <?= HTML::style('media/css/handheld.css', array("media" => "handheld")) ?> 
  <?= HTML::style('media/css/login.css', array("media" => "all")) ?> 
  <link<?= HTML::attributes(array("rel" => "icon", "href" => "media/images/favicon.ico", "type" => "image/vnd.microsoft.icon")) ?> />
  <link<?= HTML::attributes(array("rel" => "shortcut icon", "href" => "media/images/favicon.ico", "type" => "image/vnd.microsoft.icon")) ?> />
</head>
<body>
   <div id="top_menu">
      <div class="menucontainer">
         <div>
	    <a href="/"><img class="logo" src="/media/images/sown_adminsys.png" alt="SOWN Admin System logo"/></a>
	 </div>
      </div>
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
	<!-- Accepted Users List -->
	<!--
	<div id='valid_institutions'>
		<h4>Currently accepted users:</h4>
	
		<dl class="list">
			<dt>University of Southampton <span class='no_bold'>(@soton.ac.uk)</span></dt>
				<dd>&raquo; This uses your <strong>iSolutions</strong> username &amp; password.</dd>
			<dt><abbr title="Electronics and Computer Science">ECS</abbr> <span class='no_bold'>(@ecs.soton.ac.uk)</span></dt>
				<dd>&raquo; This uses your ECS username and password.</dd>
			<dt><abbr title="Southampton Open Wireless Network">SOWN</abbr> <span class='no_bold'>(@sown.org.uk)</span></dt>
				<dd>&raquo; If you have forgotten your password <a href="forgot_password">click here</a>.</dd>
		</dl>
	</div>
	<div id='contact_sown'>
		<h4>Contact SOWN</h4>
		<p>Please contact SOWN if you...</p>
		<dl class="list">
			<dt><a href="http://www.sown.org.uk/contact/faultreport">are experiencing a problem</a></dt>
				<dd>&raquo; for problems logging in and accessing websites.</dd>
			<dt>wish to <a href="http://www.sown.org.uk/contact/accountrequest">request a Community Account</a></dt>
				<dd>&raquo; for new users.</dd>
			<dt>have <a href="http://www.sown.org.uk/contact/?no_links">any other enquiries</a></dt>
				<dd>&raquo; for any questions or proposals.</dd>
		</dl>
	</div>
	-->
      </div>
    </div>
  </div>

  <div class="hr"><hr/></div>
  <div id="footer">&copy; SOWN 2007-<?= date('Y') ?></div>
</body>

</html>
