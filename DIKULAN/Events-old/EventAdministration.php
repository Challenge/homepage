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
<body style="margin: 50px; background-color: #FF0000;">
<center>
<p style="font-size: 24px;">Du har ikke adgang til denne side!</p>
<p style="font-size: 24px;">Du skal v&aelig;re logget ind og medlem af DIKULAN gruppen for at se denne side.</p>
</center>
</body>
</html>
';

	exit;
}
?>

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
<title>Event administration</title>

<link rel="stylesheet" type="text/css" href="StyleEvent.css" />
</head>
<body>
<div id="administration_event_page">

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
			
			echo '<a href="?action=edit&id=' . $id . '"><h3>Rediger: ' . $name . '</h3></a>', PHP_EOL;
			if(strlen($description) > 200) {
				echo '<p class="description">' . substr($description, 0, 197) . '...</p>', PHP_EOL;
			} else {
				echo '<p class="description">' . $description . '</p>', PHP_EOL;
			}
		}
		
		//No events in the database
		if(count($result) == 0) echo 'Der er for &oslash;jeblikket ikke nogen events.', PHP_EOL;
		
		echo '<br />', PHP_EOL;
		echo '<a href="?action=add"><p>Tilf&oslash;j en ny event</p></a>', PHP_EOL;
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
function editEvent($errors = false) {
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
	$name = utf8_decode($name);
	$description = utf8_decode($description);
	$name = convertStr($name);
	$description = convertStr($description);
	$warningtext = '<span class="warningtext">Dette felt er p&aring;kr&aelig;vet!</span>';
	
	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data">', PHP_EOL;
	
	echo '<label for="event_name">Navnet eventen skal have: </label>', PHP_EOL;
	echo '<input type="text" name="event_name" id="event_name" value="';
	if(isset($_POST['event_name'])) echo $_POST['event_name'];
	else echo $name;
	echo '" />', PHP_EOL;
	if($errors && empty($_POST['event_name'])) echo $warningtext, PHP_EOL;
	echo '<br />', PHP_EOL;
	
	echo '<label for="event_description">Beskrivelsen af eventen: </label><br />', PHP_EOL;
	echo '<textarea name="event_description" id="event_description" rows="15" cols="75">';
	if(isset($_POST['event_description'])) echo $_POST['event_description'];
	else echo $description;
	echo '</textarea>', PHP_EOL;
	if($errors && empty($_POST['event_description'])) echo $warningtext, PHP_EOL;
	echo '<br />', PHP_EOL;
	
	echo '<label for="event_email">Email som tilmeldingen skal sendes til: </label>', PHP_EOL;
	echo '<input type="text" name="event_email" id="event_email" value="';
	if(isset($_POST['event_email'])) echo $_POST['event_email'];
	else echo $email;
	echo '" />', PHP_EOL;
	if($errors && empty($_POST['event_email'])) echo $warningtext;
	echo '<br />', PHP_EOL;
	
	echo '<br />', PHP_EOL;
	echo '<input type="hidden" name="id" value="' . $_GET['id'] . '" />', PHP_EOL;
	echo '<input type="submit" name="event_delete" value="Slet denne event helt fra listen" />', PHP_EOL;
	echo '<br />', PHP_EOL;
	echo '<input type="submit" name="event_list" value="G&aring; tilbage til oversigten (alle &aelig;ndringer vil g&aring; tabt)" />', PHP_EOL;
	echo '<input type="submit" name="event_edit" value="Gem dine &aelig;ndring" />', PHP_EOL;
	echo '</form>', PHP_EOL;
}
function addEvent($errors = false) {
	$warningtext = '<span class="warningtext">Dette felt er p&aring;kr&aelig;vet!</span>';
	
	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data">', PHP_EOL;
	
	echo '<label for="event_name">Navnet eventen skal have: </label>', PHP_EOL;
	echo '<input type="text" name="event_name" id="event_name" value="';
	if(isset($_POST['event_name'])) echo $_POST['event_name'];
	echo '" />', PHP_EOL;
	if($errors && empty($_POST['event_name'])) echo $warningtext, PHP_EOL;
	echo '<br />', PHP_EOL;
	
	echo '<label for="event_description">Beskrivelsen af eventen: </label><br />', PHP_EOL;
	echo '<textarea name="event_description" id="event_description" rows="15" cols="75">';
	if(isset($_POST['event_description'])) echo $_POST['event_description'];
	echo '</textarea>', PHP_EOL;
	if($errors && empty($_POST['event_description'])) echo $warningtext, PHP_EOL;
	echo '<br />', PHP_EOL;
	
	echo '<label for="event_email">Email som tilmeldingen skal sendes til: </label>', PHP_EOL;
	echo '<input type="text" name="event_email" id="event_email" value="';
	if(isset($_POST['event_email'])) echo $_POST['event_email'];
	echo '" />', PHP_EOL;
	if($errors && empty($_POST['event_email'])) echo $warningtext;
	echo '<br />', PHP_EOL;
	
	echo '<br />', PHP_EOL;
	echo '<input type="submit" name="event_cancel" value="Annuller (alle &aelig;ndringer vil g&aring; tabt)" />', PHP_EOL;
	echo '<input type="submit" name="event_add" value="Tilf&oslash;j din event til listen" />', PHP_EOL;
	echo '</form>', PHP_EOL;
}
?>

<?php
if(isset($_POST['event_cancel'])) {
	echo '<p>Dine &aelig;ndringer blev annulleret.<p>', PHP_EOL;
	echo '<a href="' . $_SERVER['PHP_SELF'] . '"><p>G&aring; tilbage til oversigten<p></a>', PHP_EOL;
}
else if(isset($_POST['event_delete'])) {
	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data">', PHP_EOL;
	echo '<input type="hidden" name="id" value="' . $_POST['id'] . '" />', PHP_EOL;
	echo '<input type="submit" name="event_cancel" value="Annuller (eventen vil ikke blive slettet)" />', PHP_EOL;
	echo '<input type="submit" name="event_delete_confirmed" value="Accepter (event vil blive slette, dette kan ikke fortrydes)" />', PHP_EOL;
	echo '</form>', PHP_EOL;
}
else if(isset($_POST['event_delete_confirmed'])) {
	$name = "";
	
	try {
		$sql = "SELECT name FROM dikulan_event WHERE id = ?";
		$stmt = $dbcon->prepare($sql);
		$stmt->execute(array($_POST['id']));
		$result = $stmt->fetchAll();
		$name = $result[0][0];
		
		$sql = "DELETE FROM dikulan_event WHERE id = ?";
		$stmt = $dbcon->prepare($sql);
		$stmt->execute(array($_POST['id']));
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

	echo '<p>F&oslash;lgende event blev slette fra listen: ' . $name . '</p>', PHP_EOL;
	echo '<a href="' . $_SERVER['PHP_SELF'] . '"><p>G&aring; tilbage til oversigten<p></a>', PHP_EOL;
}
else if(isset($_POST['event_add'])) {
	if(empty($_POST['event_name']) || empty($_POST['event_description']) || empty($_POST['event_email'])) addEvent(true);
	else {
		try {
			$sql = "INSERT INTO dikulan_event(name, description, email) VALUES(?, ?, ?)";
			$stmt = $dbcon->prepare($sql);
			$stmt->execute(array(utf8_encode($_POST['event_name']), utf8_encode($_POST['event_description']), utf8_encode($_POST['event_email'])));
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
		
		echo '<p>F&oslash;lgende event blev tilf&oslash;jet: ' . $_POST['event_name'] . '</p>', PHP_EOL;
		echo '<a href="' . $_SERVER['PHP_SELF'] . '"><p>G&aring; tilbage til oversigten<p></a>', PHP_EOL;
	}
}
else if(isset($_POST['event_edit'])) {
	if(empty($_POST['event_name']) || empty($_POST['event_description']) || empty($_POST['event_email'])) editEvent(true);
	else {
		try {
			$sql = "UPDATE dikulan_event SET name = ?, description = ?, email = ? WHERE id = ?";
			$stmt = $dbcon->prepare($sql);
			$stmt->execute(array(utf8_encode($_POST['event_name']), utf8_encode($_POST['event_description']), utf8_encode($_POST['event_email']), $_POST['id']));
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
		
		echo '<p>F&oslash;lgende event blev opdateret: ' . $_POST['event_name'] . '</p>', PHP_EOL;
		echo '<a href="?action=edit&id=' . $_POST['id'] . '"><p>Se din &aelig;ndring<p></a>', PHP_EOL;
		echo '<a href="' . $_SERVER['PHP_SELF'] . '"><p>G&aring; tilbage til oversigten<p></a>', PHP_EOL;
	}
}
else if(isset($_GET['action']) && strcasecmp($_GET['action'], "add") == 0) {
	addEvent(false);
}
else if(isset($_GET['action']) && strcasecmp($_GET['action'], "edit") == 0 && isset($_GET['id'])) {
	editEvent(false);
}
else {
	unset($_POST);
	listEvents();
}

?>

</div>
</body>
</html>















