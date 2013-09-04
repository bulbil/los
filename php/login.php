<?php
session_start();

include_once '../html/header.html';
include_once 'db.php';
include_once 'utilities.php'; 
login();

function login() {
  if (isset($_POST['username']) && isset($_POST['password']) && strlen($_POST['username']) > 0 && strlen($_POST['password']) > 0){

    $dbh = db_connect();

    print_r($_SESSION);
    print_r($_POST);
    echoLine('waht');


    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING, array(FILTER_FLAG_STRIP_LOW, FILTER_FLAG_STRIP_HIGH));
    $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING, array(FILTER_FLAG_STRIP_LOW, FILTER_FLAG_STRIP_HIGH));
      
      echoLine(ifExists($username, 'Reviewers', 'username', $dbh, $password, 'password'));
      if(ifExists($username, 'Reviewers', 'username', $dbh, $password, 'password')){
        
        $reviewer_id = returnID($username, 'reviewer_id', 'username', 'Reviewers', $dbh);

        $_SESSION['username'] = $username;
        $_SESSION['reviewer_id'] = $reviewer_id;

        header('location: home.php');

      } else echo "<div class='col-md-6 col-md-offset-3'>
                  <div class='alert alert-danger'>sorry <em>!</em> invalid username/password combo</div>
                  </div>";

  } else echo "<div class='col-md-6 col-md-offset-3'>
                  <div class='alert alert-warning'><em>please enter username and password to login ...</em></div>
                  </div>";  
}

echo <<<eod
<div style="text-align: center; margin-right:100px"><img src="../img/los_lion.png" /></div>

<div class="row">
<div class="col-md-3 col-md-offset-4">
  <form class="form-signin" action="login.php" method="post">
    <h2 class="form-signin-heading">please sign in</h2>
    <input type="text" name='username' class="form-control" placeholder="username" autofocus>
    <input type="password" name='password' class="form-control" placeholder="password">
    <button class="btn btn-large btn-primary" type="submit" style="width: 62%;">sign in</button>
  </form>
</div>
</div>
eod;

include '../html/footer.html';