<?php
session_start();
unset ($_SESSION['username']);

if( isset($_SERVER['return']) ) {
	$return = $_SERVER['return'];
} else {
	$return = "/";
}
$logout_url = "/Shibboleth.sso/Logout?return=$return";

header("Location: $logout_url");
