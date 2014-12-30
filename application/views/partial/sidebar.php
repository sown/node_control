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
        <a href="<?= Route::url('my_deployments') ?>">My Deployment(s)</a><br/>
        <a href="<?= Route::url('deployments_usage') ?>">My Deployment(s) Usage</a><br/>
<?php } ?>
<?php if($user->is('systemadmin')) { ?>
        <a href="<?= Route::url('deployments_usage_all') ?>">All Deployments Usage</a><br/>
<?php } ?>
        <a href="<?= Route::url('reserved_subnets') ?>">Reserved Subnets</a><br/>
      </div>
    </div>
     <div class="gadget">
      <div class="banner">Miscellaneous</div>
      <div class="content">
<?php if($user->is('systemadmin')) { ?>
        <a href="<?= Route::url('cron_jobs_enabled') ?>">Cron Jobs</a><br/>
<?php } ?>
<?php if($user->is('systemadmin')) { 
	$unresponded = sizeof(Model_Enquiry::getUnresponded());
	$enquiry_type_account = Doctrine::em()->getRepository('Model_EnquiryType')->find(3);
	$unresponded_accounts = sizeof(Model_Enquiry::getUnresponded(array('type' => $enquiry_type_account)));
?>	
        <a href="<?= Route::url('unresponded_enquiries') ?>">Enquiries (<?= $unresponded ?>)</a><br/>
	<a href="<?= Route::url('unresponded_type_enquiries', array('type' => 3)) ?>">Community Account Requests (<?= $unresponded_accounts ?>)</a><br/>
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
