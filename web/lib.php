<?php

function login_to_backend($sock, $app_key, $app_secret, $oauth_token, $oauth_token_secret) {
	fwrite($sock, "$app_key\n");
	fwrite($sock, "$app_secret\n");
	fwrite($sock, $_SESSION['oauth_token']."\n");
	fwrite($sock, $_SESSION['oauth_token_secret']."\n");

	$ret = chop(fgets($sock));

	return $ret;
}

?>
