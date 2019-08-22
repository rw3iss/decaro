<?php dorm()->response->responsive_view('shared/header'); ?>

<link rel="stylesheet" href="/assets/css/login.css" media="screen">

  <div ng-app="decaro">
  <div id="logged_out" ng-controller="LoginController">
    <div class="page" id="logged-out">

      <img class="logo" src="/assets/img/decaro_logo_sm2.png"/>

      <h3>Please Login</h3>

      <div class="input-group">
        <span class="input-group-addon">@</span>
        <input type="text" ng-keyup="$event.keyCode == 13 && tryLogin()" class="form-control" id="login_username" placeholder="Username" ng-model="username">
      </div>
      <div class="input-group">
        <span class="input-group-addon">@</span>
        <input type="password" ng-keyup="$event.keyCode == 13 && tryLogin()" class="form-control" id="login_password" placeholder="Password" ng-model="password">
      </div>

      <button type="button" class="login-btn btn btn-primary" ng-click="login()">Login</button>

    </div>
  </div>
</div>

<?php dorm()->response->responsive_view('shared/footer'); ?>