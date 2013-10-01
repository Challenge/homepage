<?php /* INCLUDES. */
/*
 * Provides:
 * getRandomString($length);
 */
include getcwd() . '/../..' . '/DIKULAN/functions.inc';

/*
 * Provides:
 * resample($targetFile, $sourceFile, $newWidth, $newHeight);
 * resize($targetFile, $sourceFile, $newWidth, $newHeight);
 */
include getcwd() . '/../..' . '/DIKULAN/ImageLibrary.inc';

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
function convertStr($str){
	return str_replace(array('æ','ø','å'), array('&aelig;','&oslash;','&aring;'), $str);
}

function listEvents() {
	global $dbcon;
	
	try {
		$sql = "SELECT id, name, description FROM dikulan_event";
		$stmt = $dbcon->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll();

		foreach ($result as $row) {
			$id = $row[0]; $name = $row[1]; $description = $row[2];
			$name = convertStr($name);
			$description = convertStr($description);
			$description = preg_replace('/<br( |\/| \/)\/>/', '', $description);
			
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
	$eventName = convertStr($eventName);
	$eventDescription = convertStr($eventDescription);
	$realName = convertStr($realName);
	$gamertag = convertStr($$gamertag);
	$additionalMessage = convertStr($additionalMessage);
	
	$to = $eventEmail;
	$subject = 'Event tilmelding til ' . $eventName;
	
	$additionalMessageNew = "";
	if(!empty($additionalMessage)) {
		$additionalMessageNew .= '<p>' . trim(htmlspecialchars($realName)) . ' &oslash;nskede at give f&oslash;lgende ekstra besked:</p>';
		$additionalMessageNew .= '<p>' . trim(htmlspecialchars($additionalMessage)) . '</p>';
		unset($additionalMessage); // Should not be able to use the 'old' variable, as it is not html-safe.
		
		$additionalMessageNew = nl2br($additionalMessageNew);
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
		Ekstra meddelse: ' . $additionalMessageNew . '<br />
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
function viewEvent($error = false) {
	global $dbcon;
	
	try {
		$sql = "SELECT name, description, email FROM dikulan_event WHERE id = ?";
		$stmt = $dbcon->prepare($sql);
		$stmt->execute(array($_GET['id']));
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
	//$name = utf8_decode($name);
	//$description = utf8_decode($description);
                        $name = convertStr($name);
                        $description = convertStr($description);
	
	echo '<center><h1>Event: ' . $name . '</h1></center>', PHP_EOL;
	echo '<h5 style="margin-top: -15px;">' . $description . '</h5>', PHP_EOL;
	
	$warningtext = "";
	if($error) {
		$warningtext = '<span class="warningtext">Dette felt er p&aring;kr&aelig;vet!</span>';
	}
	
	echo '<br /><br /><form action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data">
	<label for="real_name">Dit rigtige navn: </label><br />
	<input type="text" name="real_name" id="real_name" value="';
	if(isset($_POST['real_name'])) echo $_POST['real_name'];
	echo '" />';
	if($error && empty($_POST['real_name'])) echo $warningtext;
	
	echo '<br /><br />
	<label for="table_number">Bordnummer (nummeret p&aring; bordet du sidder ved): </label><br />
	<input type="text" name="table_number" id="table_number" value="';
	if(isset($_POST['table_number'])) echo $_POST['table_number'];
	echo '" />';
	if($error && empty($_POST['table_number'])) echo $warningtext;
	if($error && !is_numeric($_POST['table_number'])) echo '<span class="warningtext">Dette felt m&aring; kun indeholde tal.</span>';
	
	echo '<br /><br />
	<label for="user_email">Din email: </label><br />
	<input type="text" name="user_email" id="user_email" value="';
	if(isset($_POST['user_email'])) echo $_POST['user_email'];
	echo '" />';
	if($error && empty($_POST['user_email'])) echo $warningtext;
	
	echo '<br /><br />
	<label for="gamertag">Dit gamertag (valgfri): </label><br />
	<input type="text" name="gamertag" id="gamertag" value="';
	if(isset($_POST['gamertag'])) echo $_POST['gamertag'];
	echo '" />';
	
	echo '<br /><br />
	<label for="additional_message">Ekstra infromation til eventholderen (valgfri): </label><br />
	<textarea name="additional_message" id="additional_message" rows="10" cols="70">';
	if(isset($_POST['additional_message'])) echo $_POST['additional_message'];
	echo '</textarea>';
	
	echo '<br /><br />
	<input type="hidden" name="id" value="' . $_GET['id'] . '" />
	<input type="submit" name="event_list" value="Annuller (alle &aelig;ndringer vil g&aring; tabt)" />
	<input type="submit" name="registration" value="Send tilmelding" />
	</form>
	', PHP_EOL;
}
?>

<?php
if(isset($_POST['registration'])) {
	if(empty($_POST['real_name']) || (empty($_POST['table_number']) && is_numeric($_POST['table_number'])) || empty($_POST['user_email'])) {
		// Required fields are empty, so give the user an warning about this.
		viewEvent(true);
	} else {
		try {
			$sql = "SELECT name, description, email FROM dikulan_event WHERE id = ?";
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
		$mailSent = sendmail($name, $description, $email, $_POST['real_name'], $_POST['table_number'], $_POST['user_email'], $_POST['gamertag'], $_POST['additional_message']);
		if(!$mailSent) {
			echo '<p>Der skete en fejl, s&aring; tilmeldingen blev ikke afsendt.<p>', PHP_EOL;
			echo '<p>Pr&oslash;v at g&aring; tilbage til oversigt og pr&oslash;v igen.<p>', PHP_EOL;
		}
		else {
			echo '<p>Din tilmelding til f&oslash;lgende event blev sendt: "' . $name . '"<p>', PHP_EOL;
		}

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























