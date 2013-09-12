<?php ob_start();
session_start();

include '../html/header.html';
include '../includes/db.php';
include '../includes/utilities.php'; 

if (isset($_POST['username']) && isset($_POST['password']) && strlen($_POST['username']) > 0 && strlen($_POST['password']) > 0){

  $dbh = db_connect();
  $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING, array(FILTER_FLAG_STRIP_LOW, FILTER_FLAG_STRIP_HIGH));
  $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING, array(FILTER_FLAG_STRIP_LOW, FILTER_FLAG_STRIP_HIGH));

  if(if_exists(array($username, $password), array('username', 'password'), 'Reviewers', $dbh)){
    
    $reviewer_id = return_id('reviewer_id', array($username), array('username'), 'Reviewers', $dbh);

    $_SESSION['username'] = $username;
    $_SESSION['reviewer_id'] = $reviewer_id;
    header('location: home.php');
    ob_end_flush();
    return;

  } else {echo "<div class='col-md-6 col-md-offset-3'>
                <div class='alert alert-danger'>sorry <em>!</em> invalid username/password combo</div>
                </div>";}
}
?>

<div class="row">
<div class="col-md-3 col-md-offset-4">
  <a href="home.php"><img src="../img/los_lion.png" alt="land of sunshine lion" /></a>
  <form class="form-signin" action="login.php" method="post">
    <h2 class="form-signin-heading">please sign in</h2>
    <input type="text" name='username' class="form-control" placeholder="username" autofocus>
    <input type="password" name='password' class="form-control" placeholder="password">
    <button class="btn btn-primary btn-block" type="submit">go <em>!</em></button>
  </form>
</div>
</div>

<?php include '../html/footer.html'; ?>