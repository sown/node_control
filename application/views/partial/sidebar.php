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
    <div class="banner">My details</div>
      <div class="content">
        Logged in username:
        <br/>
        <div style='text-align:center'>
          <br/>
          <b><?= $user->get_user() ?></b>
          <br/>
        </div>
      </div>
    </div>
<?php if($user->is('systemadmin') || $user->is('local')) { ?>
    <div class="gadget">
      <div class="banner">Main Menu</div>
      <div class="content">
<?php if($user->is('systemadmin')) { ?>
        <a href="<?= Route::url('users') ?>">Users</a><br/>
<?php } ?>
<?php if($user->is('local')) { ?>
        <a href="<?= Route::url('change_password') ?>">Change Password</a><br/>
<?php } ?>
      </div>
    </div>
<?php } ?>
<?php if($user->is('systemadmin')) { ?>
    <div class="gadget">
      <div class="banner">Nodes/Deployments</div>
      <div class="content">
        <a href="<?= Route::url('nodes') ?>">Nodes</a><br/>
        <a href="<?= Route::url('node_hardwares') ?>">Node Hardwares</a><br/>
        <a href="<?= Route::url('deployments') ?>">Deployments</a><br/>
        <a href="<?= Route::url('deployments_usage_all') ?>">All Deployments Usage</a><br/>
	<a href="<?= Route::url('pending_node_requests') ?>">Pending Node Requests</a><br/>
	<a href="<?= Route::url('pending_node_setup_requests') ?>">Pending Node Setup Requests</a><br/>
	<a href="<?= Route::url('reserved_subnets') ?>">Reserved Subnets</a><br/>
     </div>
    </div>
<?php } ?>
<?php if($user->is('deploymentadmin')) { ?>
     <div class="gadget">
      <div class="banner">My Deployment(s)</div>
      <div class="content">
        <a href="<?= Route::url('my_deployments') ?>">Configuration</a><br/>
        <a href="<?= Route::url('deployments_usage') ?>">Usage</a><br/>
       </div>
    </div>
<?php } ?>
<?php if($user->is('systemadmin')) { ?>
    <div class="gadget">
      <div class="banner">Miscellaneous</div>
      <div class="content">
<?php 
	$unresponded = sizeof(Model_Enquiry::getUnresponded());
        $enquiry_type_account = Doctrine::em()->getRepository('Model_EnquiryType')->find(3);
        $unresponded_accounts = sizeof(Model_Enquiry::getUnresponded(array('type' => $enquiry_type_account)));
?>
	<a href="<?= Route::url('current_servers') ?>">Servers</a><br/>
	<a href="<?= Route::url('current_hosts') ?>">Other Hosts</a><br/>
        <a href="<?= Route::url('cron_jobs_enabled') ?>">Cron Jobs</a><br/>
        <a href="<?= Route::url('certificates') ?>">Certificates</a><br/>
        <a href="<?= Route::url('unresponded_enquiries') ?>">Enquiries (<?= $unresponded ?>)</a><br/>
	<a href="<?= Route::url('unresponded_type_enquiries', array('type' => 3)) ?>">Community Account Requests (<?= $unresponded_accounts ?>)</a><br/>
        <a href="<?= Route::url('inventory') ?>">Inventory</a><br/>
        <a href="<?= Route::url('radaccts') ?>">Radius Accounting</a><br/>
      </div>
  </div>
<?php } ?>

</div>
