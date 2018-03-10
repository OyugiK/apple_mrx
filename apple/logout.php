<?php
	session_start();
	  # logged in?
  if(isset($_SESSION['authData'])){
    $_SESSION['flash-message'] = array("type" => "notice", "msg" => "Welcome to One Infinite Loop");
    # now we must bring back the user to this page after they login successfully,
    # to do this, we store this location in the session.
    $_SESSION['crm_referrer'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
  }

	# start the session
	ob_start();
	error_reporting(E_ALL);
		# flash messages
	$flash = isset($_SESSION['flash-message']) ? $_SESSION['flash-message'] : null;
	# read any flash message before unset
	$_SESSION['flash-message'] = array("type" => "notice", "msg" => "You have logged out successfully");
	$_SESSION['crm_referrer'] = $_SERVER['REQUEST_URI'];
	header("Location: login.php");
	session_unset();
	session_destroy();
?>