<?php
if (session_id() === '') {
	session_start();
}
if (!isset($_SESSION['uid'])) {
	$_SESSION['referer'] = $_SERVER['REQUEST_URI'];
	header('Location: '.'/login.php');
	exit;
}
?>
