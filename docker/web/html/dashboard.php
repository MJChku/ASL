<?php include 'include/authenticate.php'; ?>
<?php require_once 'include/db.php'; ?>
<?php
// fetch user information from db
$user = get_user($_SESSION['uid']);
$fnameErr = $lnameErr = $emailErr = "";

if (isset($_POST['continue'])) {
	header('Location: '.'./revoke.php');
	exit;
} elseif (isset($_POST['reset'])) {
	header('Location: '.'./dashboard.php');
	exit;
} elseif (isset($_POST['submit'])) { 
	if ($user->firstname != $_POST['fname']) {
		if (empty($_POST["fname"])) {
			$fnameErr = "First name is required";
		} else {
			db_exec('UPDATE users SET firstname = ? WHERE uid = ?', 'ss', [$_POST['fname'], $_SESSION['uid']]);
		}
	}
	if ($user->lastname != $_POST['lname']) {
		if (empty($_POST["lname"])) {
			$lnameErr = "Last name is required";
		} else {
			db_exec('UPDATE users SET lastname = ? WHERE uid = ?', 'ss', [$_POST['lname'], $_SESSION['uid']]);
		}
	}
	if ($user->email != $_POST['email']) {
		if (empty($_POST["email"])) {
			$emailErr = "Email is required";
		} else {
			if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
  				$emailErr = "Invalid email format";
			} else {
				db_exec('UPDATE users SET email = ? WHERE uid = ?', 'ss', [$_POST['email'], $_SESSION['uid']]);
			}
		}
	}
	if ($fnameErr === "" && $lnameErr === "" && $emailErr === "") {
		header('Location: '.'./dashboard.php');
		exit;
	}
}
?>
<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <style> .error {color: #FF0000;} </style>

  <title>Dashboard</title>
</head>

<body>
	<form action="" method="post">
		<br/>
			First Name: <input type="text" value="<?php echo htmlspecialchars($user->firstname);?>" name="fname"/><span class="error"> <?php echo htmlspecialchars($fnameErr);?></span>
		<br/>
		 
		<br/>
			Last Name: <input type="text" value="<?php echo htmlspecialchars($user->lastname);?>" name="lname"/><span class="error"> <?php echo htmlspecialchars($lnameErr);?></span>
		<br/>
		 
		<br/>
			E-Mail: <input type="text" value="<?php echo htmlspecialchars($user->email);?>" name="email"/><span class="error"> <?php echo htmlspecialchars($emailErr);?></span>
		</br>
		 
		<br/>
			<button type='submit' name='submit'>Save Changes</button>
			<button type='submit' name='reset'>Discard Changes</button>
			<button type='submit' name='continue'>Continue</button>
		</br>
		
	</form>
</body>
</html>
