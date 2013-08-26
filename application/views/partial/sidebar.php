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
        <a href="<?= Route::url('test') ?>">Test</a><br/>
<?php } ?>
<?php if($user->is('systemadmin')) { ?>
        <a href="<?= Route::url('users') ?>">Users</a><br/>
<?php } ?>
<?php if($user->is('local')) { ?>
        <a href="<?= Route::url('change_password') ?>">Change Password</a><br/>
<?php } ?>
      </div>
    </div>
    <div class="gadget">
      <div class="banner">Nodes/Deployments</div>
      <div class="content">
<?php if($user->is('systemadmin')) { ?>
        <a href="<?= Route::url('nodes') ?>">Nodes</a><br/>
<?php } ?>
<?php if($user->is('systemadmin')) { ?>
        <a href="<?= Route::url('deployments') ?>">Deployments</a><br/>
<?php } ?>
<?php if($user->is('deploymentadmin')) { ?>
        <a href="<?= Route::url('deployments_usage') ?>">Your Deployment(s) Usage</a><br/>
<?php } ?>
<?php if($user->is('systemadmin')) { ?>
        <a href="<?= Route::url('deployments_usage_all') ?>">All Deployments Usage</a><br/>
<?php } ?>
      </div>
    </div>
     <div class="gadget">
      <div class="banner">Miscellaneous</div>
      <div class="content">
<?php if($user->is('systemadmin')) { ?>
        <a href="<?= Route::url('cron_jobs_enabled') ?>">Cron Jobs</a><br/>
<?php } ?>
<?php if($user->is('systemadmin')) { ?>
        <a href="<?= Route::url('enquiry_types') ?>">Enquiry Types</a><br/>
<?php } ?>
<?php if($user->is('systemadmin')) { ?>
        <a href="<?= Route::url('inventory') ?>">Inventory</a><br/>
<?php } ?>
<?php if($user->is('systemadmin')) { ?>
        <a href="<?= Route::url('radaccts') ?>">Radius Accounting</a><br/>
<?php } ?>

      </div>
  </div>
</div>
