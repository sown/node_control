<?php
if(!isset($user))
{
	$user = Auth::instance();
}
?>
<div id="sidebar">
  <div class="gadget">
    <div class="logout">
      <a title="Logout" href="/logout">Logout</a>
    </div>
    <div class="banner">Your details</div>
      <div class="content">
        You are logged in as:
        <br/>
        <div style='text-align:center'>
          <br/>
          <b><?= $user->get_user() ?></b>
          <br/>
        </div>
      </div>
    </div>
    <div class="gadget">
      <div class="banner">Main Menu</div>
      <div class="content">
<?php if($user->is('systemadmin')) { ?>
        <a href="/admin/test">Test</a><br/>
<?php } ?>
<?php if($user->is('deploymentadmin')) { ?>
        <a href="/admin/deployments/usage">Your Deployment(s) Usage</a><br/>
<?php } ?>
<?php if($user->is('systemadmin')) { ?>
        <a href="/admin/deployments/usage/all">All Deployments Usage</a><br/>
<?php } ?>
<?php if($user->is('local')) { ?>
        <a href="/admin/change_password">Change Password</a><br/>
<?php } ?>
      </div>
    </div>
  </div>
</div>
