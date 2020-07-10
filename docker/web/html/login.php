<?php if (session_id() === '') { session_start(); } ?>
<?php require_once 'include/db.php'; ?>
<?php
function verify_certificate() {
	if (isset($_SERVER['SSL_CLIENT_V_END'])
	&& isset($_SERVER['SSL_CLIENT_VERIFY'])	&& $_SERVER['SSL_CLIENT_VERIFY'] === 'SUCCESS'
	&& isset($_SERVER['SSL_CLIENT_I_DN'])
	&& $_SERVER['SSL_CLIENT_V_REMAIN'] > 0
	&& isset($_SERVER['SSL_CLIENT_M_SERIAL'])) {
		// Check if certificate has been revoked
		$rev = db_exec('SELECT revoked FROM certificates WHERE serial = ?', 'i', [$_SERVER['SSL_CLIENT_M_SERIAL']])->fetch_object();
		if ($rev && $rev->revoked !== TRUE) {
			// Check if certificate was issued by User CA
			if (isset($_SERVER['SSL_CLIENT_I_DN_CN']) && $_SERVER['SSL_CLIENT_I_DN_CN'] === 'iMovies Client CA') {
				$cert = openssl_x509_parse($_SERVER['SSL_CLIENT_CERT']);
				$email = $cert['subject']['emailAddress'];	
				$user = db_exec('SELECT * FROM users WHERE email = ? ORDER BY lastname ASC, firstname ASC LIMIT 1', 's', [$email]);
				return $user->fetch_object()->uid;
			}
		}
		$_SESSION['error'] = 'Authentication using certificate failed';
		return NULL;
	}
 
	return NULL;
}
?>
<?php 
function verify_credentials($username, $password) {
	$config = json_decode(file_get_contents('../config/mysql.json'));
	$con = new mysqli(NULL, $config->db_user, $config->db_pass, 'imovies', NULL, $config->db_socket);
	if ($con->connect_errno) {
		echo 'Connection to database failed: '.$con->connect_errno.' '.$con->connect_error;
		return false;
	}
	$hash = sha1($password);
	$sql = "SELECT * FROM users WHERE uid = '$username' and pwd = '$hash' limit 0,1";

	if ($result=mysqli_query($con,$sql)) {
		$user=mysqli_fetch_object($result);
		$rowcount=mysqli_num_rows($result);
		if ($rowcount == 1) {
			return $user->uid;
		}
	} 
	return NULL;
}
?>
<?php
function login($uid) {
	$_SESSION['uid'] = $uid;
	unset($_SESSION['error']);
	if (array_key_exists('referer', $_SESSION) && $_SESSION['referer'] !== '/') {
		$redirect = $_SESSION['referer'];
		unset($_SESSION['referer']);
	} else {
		$redirect = '/dashboard.php';
	}
	header('Location: '.$redirect);
	exit;
}
?>
<?php
if (isset($_SESSION['uid'])) {
	login($_SESSION['uid']);
} elseif ($uid = verify_certificate()) {
	login($uid);
} else {
	if (!empty($_POST) && isset($_POST['username']) && isset($_POST['password'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		if ($uid = verify_credentials($username, $password)) {
			login($uid);
		} else {
			$_SESSION['error'] = 'Wrong username or password. Please try again. ';
		}
	}
}
?>
<!doctype html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Login Page</title>
</head>

<body>
<?php 
if (isset($_SESSION['error'])) { 
	echo '<span>'.$_SESSION['error'].'</span>'; 
	unset($_SESSION['error']);
}
?>
	<form action="" method="POST">
		<input type="text" name="username" placeholder="Enter username">
		<input type="password" name="password" placeholder="Enter password">
		<input type="submit" value="Log in">
	</form>

</body>
</html>
