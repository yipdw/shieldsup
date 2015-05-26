<?php

session_start();

if(empty($_SESSION['username'])){
   // User is not logged in, redirect
   header('Location: twitter_update.php');
}

?>

<?php

	print_r($_SESSION)
?>

<h2>Hello <?=(!empty($_SESSION['username']) ? '@' . $_SESSION['username'] : 'Guest'); ?></h2>


<h4><?=$_SESSION['id']; ?></h4>
<h4><?=$_SESSION['username'] ?></h4>
<h4><?=$_SESSION['oauth_uid'] ?></h4>
<h4><?=$_SESSION['oauth_provider'] ?></h4>
<h4><?=$_SESSION['oauth_token'] ?></h4>
<h4><?=$_SESSION['oauth_secret'] ?></h4>
