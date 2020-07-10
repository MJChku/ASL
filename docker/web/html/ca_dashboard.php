<?php require_once 'include/db.php'; ?>
<?php
$num_valid = db_exec('SELECT COUNT(*) AS count FROM certificates WHERE revoked IS FALSE')->fetch_object()->count;
$num_revoked = db_exec('SELECT COUNT(*) AS count FROM certificates WHERE revoked IS TRUE')->fetch_object()->count;
$max_serial = db_exec('SELECT COALESCE(MAX(serial), 0) as max_serial FROM certificates')->fetch_object()->max_serial;
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>CA Admin Dashboard</title>
</head>

<body>
	<h2>Dashboard for CA Admin</h2>
	<table>
		<tr><td>Valid Certificates: </td><td><?php echo htmlspecialchars($num_valid); ?></td></tr>
		<tr><td>Revoked Certificates: </td><td><?php echo htmlspecialchars($num_revoked); ?></td></tr>
		<tr><td>Current Serial Number: </td><td><?php echo htmlspecialchars($max_serial); ?></td></tr>
	</table>
</body>
</html>
