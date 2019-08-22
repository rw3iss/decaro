<?php dorm()->response->responsive_view('shared/header'); ?>

  <div id="logged_out">
  <div class="page" id="logged-out">

    <img class="logo" src="/img/decaro_logo_sm2.png"/>

    <h3>Please Login</h3>

    <div class="input-group">
      <span class="input-group-addon">@</span>
      <input type="text" class="form-control" id="login_username" placeholder="Username">
    </div>
    <div class="input-group">
      <span class="input-group-addon">@</span>
      <input type="password" class="form-control" id="login_password" placeholder="Password">
    </div>

    <button type="button" class="login-btn btn btn-primary">Login</button>

  </div>
</div>

<?php dorm()->response->responsive_view('shared/header'); ?>