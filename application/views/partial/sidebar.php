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
          <b><?= $username ?></b>
          <br/>
        </div>
      </div>
    </div>
    <div class="gadget">
      <div class="banner">Main Menu</div>
      <div class="content">
        <a href="/admin/test">Test</a><br/>
        <a href="/admin/deployments/usage">Your Deployment(s) Usage</a><br/>
        <a href="/admin/deployments/usage/all">All Deployments Usage</a><br/>
        <a href="/admin/change_password">Change Password</a><br/>
      </div>
    </div>
  </div>
</div>
