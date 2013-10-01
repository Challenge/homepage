<?php /* INCLUDES. */
/*
 * Provides:
 * Communication with joomla, to view usergroups, etc.
 */
include getcwd() . '/../..' . '/JoomlaExternalCommunicator.inc';
$JoomlaScript = new JoomlaScript();

include getcwd() . '/../..' . '/DIKULAN/functions.inc';

/*
 * Provides:
 * $dbcon Connection to the MySQL database.
 */
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
<?php /* GLOBAL VARIABLES */

?>

<html>
<head>
<title>Event</title>

<link rel="stylesheet" type="text/css" href="StyleEvent.css" />
</head>
<body>
<div id="event_page">

<?php
function listRegistrations($id) {
	global $dbcon;
	
	try {
		$sql = "SELECT person_name, table_number, person_email, gamertag, additional_message FROM dikulan_event_registration WHERE id = ?";
		$stmt = $dbcon->prepare($sql);
		$stmt->execute(array($id));
		$result = $stmt->fetchAll();
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
	
	echo '
	<table border="1" style="tr:hover { background: #FCF; }">
	<tr>
	<th>Name</th>
	<th>Table</th>
	<th>Email</th>
	<th>Gamertag</th>
	<th>Message</th>
	</tr>
	', PHP_EOL;

	foreach ($result as $row) {
		$person_name = $row[0]; $table_number = $row[1]; $person_email = $row[2]; $gamertag = $row[3]; $additional_message = $row[4];

		echo '<tr>', PHP_EOL;
		echo '<td onClick="alert(\'' . lineBreaks(trim($person_name), 80) . '\')">' . stringLengthSplit($person_name, 30) . '</td>', PHP_EOL;
		echo '<td onClick="alert(\'' . lineBreaks(trim($table_number), 80) . '\')">' . stringLengthSplit($table_number, 5) . '</td>', PHP_EOL;
		echo '<td onClick="alert(\'' . lineBreaks(trim($person_email), 80) . '\')">' . stringLengthSplit($person_email, 20) . '</td>', PHP_EOL;
		echo '<td onClick="alert(\'' . lineBreaks(trim($gamertag), 80) . '\')">' . stringLengthSplit($gamertag, 10) . '</td>', PHP_EOL;
		if(empty($additional_message)) {
			echo '<td></td>', PHP_EOL;
		} else {
			echo '<td onClick="alert(\'' . lineBreaks(trim($additional_message), 80) . '\')">' . stringLengthSplit($additional_message, 50) . '</td>', PHP_EOL;
		}
		echo '</tr>', PHP_EOL;
	}
	
	if(count($result) == 0) echo '<tr><td colspan="5">Der er p&aring; nuv&aelig;rende tidspunkt ingen tilmeldinger.</td></tr>', PHP_EOL;
	echo '</table>';
}
function listEvents() {
	global $dbcon;
	
	try {
		$sql = "SELECT id, name, description FROM dikulan_event_administration ORDER BY priority ASC";
		$stmt = $dbcon->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll();

		foreach ($result as $row) {
			$id = $row[0]; $name = $row[1]; $description = $row[2];
			
			echo '<a href="?action=view&id=' . $id . '"><h3>Event: ' . $name . '</h3></a>', PHP_EOL;
			if(strlen($description) > 200) {
				echo '<p class="description">' . substr($description, 0, 197) . '...</p>', PHP_EOL;
			} else {
				echo '<p class="description">' . $description . '</p>', PHP_EOL;
			}
		}
		
		//No events in the database
		if(count($result) == 0)	echo 'Der er for &oslash;jeblikket ikke nogen events.', PHP_EOL;
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
function sendmail($eventName, $eventDescription, $eventEmail, $realName, $tableNumber, $userEmail, $gamertag, $additionalMessage = "") {
	$to = $eventEmail;
	$subject = 'Event tilmelding til ' . $eventName;
	
	$additionalMessageNew = "";
	if(!empty($additionalMessage)) {
		$additionalMessageNew .= '<p>' . trim(htmlspecialchars($realName)) . ' &oslash;nskede at give f&oslash;lgende ekstra besked:</p>';
		$additionalMessageNew .= '<p>' . trim(htmlspecialchars($additionalMessage)) . '</p>';
		unset($additionalMessage); // Should not be able to use the 'old' variable, as it is not html-safe.
	}
	
	if(empty($gamertag)) {
		$gamertag = 'Ikke opgivet';
	}

	// Format the message as HTML
	$message = '
	<html>
	<head>
		<title>' . $subject . '</title>
	</head>
	<body>
		<p>F&oslash;lgende bruger &oslash;nsker at tilmelde sig til "' . $eventName . '": ' . trim(htmlspecialchars($realName)) . ' (Gamertag: "' . trim(htmlspecialchars($gamertag)) . '")</p>
		<p>Personen sidder ved bordnummer ' . trim(htmlspecialchars($tableNumber)) . ' og har f&oslash;lgende email-adresse: ' . trim(htmlspecialchars($userEmail)) . '</p>
		<br />' . $additionalMessageNew . '<br />
		<br />
		<br />
		<p>Hurtig oversigt</p>
		Event navn: ' . $eventName . '<br />
		Rigtige navn: ' . trim(htmlspecialchars($realName)) . '<br />
		Gamertag: ' . trim(htmlspecialchars($gamertag)) . '<br />
		Bordnummer: ' . trim(htmlspecialchars($tableNumber)) . '<br />
		Email-addresse: ' . trim(htmlspecialchars($userEmail)) . '<br />
	</body>
	</html>
	';

	// To send HTML mail, the Content-type header must be set
	$headers = '';
	$headers .= 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	// Additional headers
	$headers .= 'From: ' . $userEmail . "\r\n";

	// Mail it
	return mail($to, $subject, $message, $headers);
}
function viewEvent($error = false, $table_taken = false) {
	global $dbcon;
	
	try {
		$sql = "SELECT name, description, email FROM dikulan_event_administration WHERE id = ?";
		$stmt = $dbcon->prepare($sql);
		$stmt->execute(array($_REQUEST['id']));
		$result = $stmt->fetchAll();
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

	$name = $result[0][0]; $description = $result[0][1]; $email = $result[0][2];
	echo '<center><h1>Event: ' . $name . '</h1></center>', PHP_EOL;
	echo '<h5 style="margin-top: -15px;">' . $description . '</h5>', PHP_EOL;
	
	$warningtext = "";
	if($error) {
		$warningtext = '<span class="warningtext">Dette felt er p&aring;kr&aelig;vet!</span>';
	}
	
	echo '<br /><br /><form action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data">
	<label for="real_name">Dit rigtige navn: </label><br />
	<input type="text" name="real_name" id="real_name" value="';
	if(isset($_POST['real_name'])) echo htmlentities($_POST['real_name']);
	echo '" />';
	if($error && empty($_POST['real_name'])) echo $warningtext;
	
	echo '<br /><br />
	<label for="table_number">Bordnummer (nummeret p&aring; bordet du sidder ved): </label><br />
	<input type="text" name="table_number" id="table_number" value="';
	if(isset($_POST['table_number'])) echo htmlentities($_POST['table_number']);
	echo '" />';
	if($error && empty($_POST['table_number'])) echo $warningtext;
	if($error && !is_numeric($_POST['table_number'])) echo '<span class="warningtext">Dette felt m&aring; kun indeholde tal.</span>';
	if($error && $table_taken) echo '<span class="warningtext">Det valgte bord er allerede blevet taget, hvis du tror det er en fejl bedes du kontakte DIKULAN gruppen.</span>';
	
	echo '<br /><br />
	<label for="user_email">Din email: </label><br />
	<input type="text" name="user_email" id="user_email" value="';
	if(isset($_POST['user_email'])) echo htmlentities($_POST['user_email']);
	echo '" />';
	if($error && empty($_POST['user_email'])) echo $warningtext;
	
	echo '<br /><br />
	<label for="gamertag">Dit gamertag (valgfri): </label><br />
	<input type="text" name="gamertag" id="gamertag" value="';
	if(isset($_POST['gamertag'])) echo htmlentities($_POST['gamertag']);
	echo '" />';
	
	echo '<br /><br />
	<label for="additional_message">Ekstra infromation til eventholderen (valgfri): </label><br />
	<textarea name="additional_message" id="additional_message" rows="10" cols="70">';
	if(isset($_POST['additional_message'])) echo htmlentities($_POST['additional_message']);
	echo '</textarea>';
	
	echo '<br /><br />
	<input type="hidden" name="id" value="' . htmlentities($_REQUEST['id']) . '" />
	<input type="submit" name="event_list" value="Annuller (alle &aelig;ndringer vil g&aring; tabt)" />
	<input type="submit" name="registration" value="Send tilmelding" />
	</form>
	', PHP_EOL;
	
	global $JoomlaScript;
	$userID = $JoomlaScript->joomlaGetUserID();
	$userGroups = $JoomlaScript->joomlaGetUserGroups($userID, 1);
	if((in_array(8, $userGroups) || in_array(14, $userGroups))) {
		echo '<br />'; echo '<br />'; echo 'Aktive tilmeldinger:';
		listRegistrations(htmlentities($_REQUEST['id']));
	}
}
?>

<?php
if(isset($_POST['registration'])) {
	if(empty($_POST['real_name']) || (empty($_POST['table_number']) || !is_numeric($_POST['table_number'])) || empty($_POST['user_email'])) {
		// Required fields are empty, so give the user an warning about this.
		viewEvent(true);
	} else {
		try {
			$sql = "SELECT name, description, email FROM dikulan_event_administration WHERE id = ?";
			$stmt = $dbcon->prepare($sql);
			$stmt->execute(array($_POST['id']));
			$result = $stmt->fetchAll();
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
		
		$name = $result[0][0]; $description = $result[0][1]; $email = $result[0][2];
		if(!empty($email)) {
			$mailSent = sendmail($name, $description, $email, $_POST['real_name'], $_POST['table_number'], $_POST['user_email'], $_POST['gamertag'], $_POST['additional_message']);
			if(!$mailSent) {
				echo '<p>Der skete en fejl, s&aring; tilmeldingen blev ikke afsendt.<p>', PHP_EOL;
				echo '<p>Pr&oslash;v at g&aring; tilbage til oversigt og pr&oslash;v igen.<p>', PHP_EOL;
				echo '<p>Hvis der ikke kommer ydeligere fejlmeddelser er din registering stadig blevet godkendt, men du skal dog stadig sikre dig at dikulan gruppen stadig f�r dette at vide.<p>', PHP_EOL;
			}
		}
		
		try {
			$sql = "INSERT INTO dikulan_event_registration(id, person_name, table_number, person_email, gamertag, additional_message) VALUES(?, ?, ?, ?, ?, ?)";
			$stmt = $dbcon->prepare($sql);
			$stmt->execute(array($_POST['id'], $_POST['real_name'], $_POST['table_number'], $_POST['user_email'], $_POST['gamertag'], $_POST['additional_message']));
		}
		catch (PDOException $e) {
			if($e->getCode() == 23000) {
				viewEvent(true, true);
			}
			else {
				echo "Could not establish connection to the database.";
				echo "This is unfortunately a fatal error that cannot be ignored and further execution has been halted." . "<br />";
				echo "Please contact an administrator immediately";
				echo "If you don't know any administrators, please visit the contact page." . "<br />";
				echo "<br />" . "Please give the administrator the following information:" . "<br />";
				echo $e;
			}
			
			die();
		}
		
		echo '<p>Din tilmelding til f&oslash;lgende event blev afsendt: "' . $name . '"<p>', PHP_EOL;
		echo '<a href="' . $_SERVER['PHP_SELF'] . '"><p>G&aring; tilbage til oversigten<p></a>', PHP_EOL;
	}
}
else if(isset($_GET['action']) && strcasecmp($_GET['action'], "view") == 0 && isset($_GET['id'])) {
	viewEvent(false);
}
else {
	unset($_POST);
	listEvents();
}


?>

</div>
</body>
</html>























