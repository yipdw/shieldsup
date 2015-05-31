<?php

function login_to_backend($sock, $app_key, $app_secret, $oauth_token, $oauth_token_secret) {
	fwrite($sock, "$app_key\n");
	fwrite($sock, "$app_secret\n");
	fwrite($sock, $_SESSION['oauth_token']."\n");
	fwrite($sock, $_SESSION['oauth_token_secret']."\n");

	$ret = chop(fgets($sock));

	return $ret;
}

    /* Print HTML Header
     (None) -> None
     
     Print standard HTML header for page (title, style).
     */
    function print_html_header(){
        ?>
        <html>
        <head>
        <title>OAPI: Shields Up</title>

        <style>
            body {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 1.2em;
            }

            table {
            margin: 0px;
            border: 0px;
            padding: 10px;
            }
        </style>
        </head>

        <body>

        <?php
    } // print_html_header()


    /* Shields Up Start Session
     (None) -> None

     Start PHP session. Redirect to twitter_login.php if no oauth session.
     Open connection to backend via socket. Throw error if can't connect.
     Log in to backend. Throw error on auth issue.
     */
    function su_session_start() {
        session_start();
        
        // User is not logged in, redirect
        if(empty($_SESSION['oauth_uid'])){
            header('Location: twitter_login.php');
            exit;
        }
        
        $sock = stream_socket_client("unix://$socket_file", $errno, $errstr);
        
        // todo: make this error message prettier?
        
        if ($errno) {
            print("something is wrong: <strong>$errstr!</strong><br />\n");
            print("the backend server probably isn't running. kick it.\n");
            print("</body></html>");
            exit;
        }

        // create a connection to our backend server
        // serves no purpose beyond ensuring our auth works
        $ret = login_to_backend(	$sock,
                                $app_key,
                                $app_secret,
                                $_SESSION['oauth_token'],
                                $_SESSION['oauth_token_secret'] );

        // todo: put in a link to the webpage. we should add that to the conf file.
        if ( $ret == "AUTH_ERR" ) {
            print_html_header();
            print("There seems to be a problem logging you in to twitter. ");
            print("Try hitting back in your browser to restart the process.\n");
            print("</body></html>");
            exit;
        }

    } // su_session_start()

?>
