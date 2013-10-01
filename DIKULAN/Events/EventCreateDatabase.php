<?php 
include getcwd() . '/../..' . '/JoomlaExternalCommunicator.inc';
$JoomlaScript = new JoomlaScript();
$userID = $JoomlaScript->joomlaGetUserID();
$userGroups = $JoomlaScript->joomlaGetUserGroups($userID, 1);

if(!(in_array(8, $userGroups) || in_array(14, $userGroups))){
echo '
<html>
<head>
<title>Adgang n&aelig;gtet</title>
</head>
<body style="margin: 50px; background-color: #FF0000; text-align: center;">
<p style="font-size: 24px;">Du har ikke adgang til denne side!</p>
<p style="font-size: 24px;">Du skal v&aelig;re logget ind og medlem af DIKULAN gruppen for at se denne side.</p>
</body>
</html>
';

	exit;
}
?>

<html>
<head>
<title>Event database creation</title>

<link rel="stylesheet" type="text/css" href="StyleEvent.css" />
</head>
<body style="text-align: center;">

<?php
try {
	include getcwd() . '/../..' . '/DIKULAN/dikulan_connection.inc';
	$dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
	echo "Could not establish connection to the database.";
	echo "This is unfortunately a fatal error that cannot be ignored and further execution has been halted." . "<br />";
	echo "Please contact an administrator immediately";
	echo "If you don't know any administrators, please visit the contact page." . "<br />";
	echo "<br />" . "Please give the administrator the following information:" . "<br />";
	echo $e;
	die();
}
?>


<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL); /* DEBUGGING */

if(isset($_GET['reset']) && strcasecmp($_GET['reset'], "true") == 0) {
	try {
		$sql = "DROP TABLE IF EXISTS dikulan_event_administration";
		$dbcon->exec($sql);
		
		echo '<p class="huge">Event administration database deleted!</p>';
		
		$sql = "DROP TABLE IF EXISTS dikulan_event_registration";
		$dbcon->exec($sql);
		
		echo '<p class="huge">Event registration database deleted!</p>';
	}
	catch (PDOException $e) {
		echo "Could not establish connection to the database.";
		echo "This is unfortunately a fatal error that cannot be ignored and further execution has been halted." . "<br />";
		echo "Please contact an administrator immediately";
		echo "If you don't know any administrators, please visit the contact page." . "<br />";
		echo "<br />" . "Please give the administrator the following information:" . "<br />";
		echo $e;
		die();
	}
}

try {
	//dikulan_event_administration: id, name, description, email
	$sql = "CREATE TABLE IF NOT EXISTS dikulan_event_administration(id INT NOT NULL AUTO_INCREMENT, name VARCHAR(255) NOT NULL, description TEXT, email VARCHAR(255) NOT NULL, priority SMALLINT UNSIGNED NOT NULL, PRIMARY KEY(id))";
	$dbcon->exec($sql);
	
	echo '<p class="huge">Event administration database created!</p>';
	
	//dikulan_event_registration: id, person_name, table_number, person_email, gamertag, additional_message
	$sql = "CREATE TABLE IF NOT EXISTS dikulan_event_registration(id INT NOT NULL, person_name VARCHAR(255) NOT NULL, table_number SMALLINT NOT NULL, person_email VARCHAR(255) NOT NULL, gamertag VARCHAR(255), additional_message TEXT, UNIQUE(id, table_number), FOREIGN KEY (id) REFERENCES dikulan_event_administration(id) ON DELETE CASCADE ON UPDATE CASCADE)";
	$dbcon->exec($sql);
	
	echo '<p class="huge">Event registration database created!</p>';
}
catch (PDOException $e) {
	echo "Could not establish connection to the database.";
	echo "This is unfortunately a fatal error that cannot be ignored and further execution has been halted." . "<br />";
	echo "Please contact an administrator immediately";
	echo "If you don't know any administrators, please visit the contact page." . "<br />";
	echo "<br />" . "Please give the administrator the following information:" . "<br />";
	echo $e;
	die();
}


?>


</body>
</html>
































