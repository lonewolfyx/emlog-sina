<?php

session_start();
include_once( 'config.php' );
include_once( 'oauth.class.php' );
$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
$code_url = $o->getAuthorizeURL( WB_CALLBACK_URL );
header("Location: $code_url");
?>
