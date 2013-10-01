<?php 
include getcwd() . '/../..' . '/JoomlaExternalCommunicator.inc';
$JoomlaScript = new JoomlaScript();
$userID = $JoomlaScript->joomlaGetUserID();
$userGroups = $JoomlaScript->joomlaGetUserGroups($userID, 1);

if (!(in_array(8, $userGroups) || in_array(14, $userGroups))) {
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

<?php /* INCLUDES. */
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

<script type="text/javascript">
function toggleAll(source) {
	checkboxes = document.getElementsByName('delete_registration[]');
	
	for each(var checkbox in checkboxes) {
		checkbox.checked = source.checked;
	}
}
</script>

<div id="administration_event_page">

<?php
function listRegistrations($id) {
	global $dbcon;
	$id = htmlentities($id);
	
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
	
	echo '<form name="registrations" action="' . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . '" method="post" enctype="multipart/form-data">', PHP_EOL;
	
	echo '
	<table border="1" style="tr:hover { background: #FCF; }">
	<tr>
	<th><input type="checkbox" id="toggle_registrations" name="toggle_registrations" onClick="toggleAll(this)"></th>
	<th>Name</th>
	<th>Table</th>
	<th>Email</th>
	<th>Gamertag</th>
	<th>Message</th>
	</tr>
	', PHP_EOL;
	
	if(count($result) == 0) echo '<tr><td colspan="6">Der er p&aring; nuv&aelig;rende tidspunkt ingen tilmeldinger.</td></tr>', PHP_EOL;
	else {
		echo '<input type="hidden" name="event_id" value="' . $id . '" />', PHP_EOL;
		
		foreach ($result as $row) {
			$person_name = $row[0]; $table_number = $row[1]; $person_email = $row[2]; $gamertag = $row[3]; $additional_message = $row[4];

			echo '<tr>', PHP_EOL;
			
			echo '<td>' . '<input type="checkbox" name="delete_registration[]" value="' . $table_number . '">' . '</td>';
			
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
	}
	
	echo '</table>', PHP_EOL;
	
	echo '<br />';
	echo '<input type="submit" name="registration_delete" value="Slet tilmeldinger"/>', PHP_EOL;
	
	echo '</form>', PHP_EOL;
}
function listEvents() {
	global $dbcon;
	
	try {
		$sql = "SELECT id, name, description FROM dikulan_event_administration ORDER BY priority";
		$stmt = $dbcon->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll();

		foreach ($result as $row) {
			$id = $row[0]; $name = $row[1]; $description = $row[2];
			
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
		echo '<a href="' . $_SERVER['PHP_SELF'] . '?action=add"><p>Tilf&oslash;j en ny event</p></a>', PHP_EOL;
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
		$sql = "SELECT name, description, email, priority FROM dikulan_event_administration WHERE id = ?";
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

	$name = $result[0][0]; $description = $result[0][1]; $email = $result[0][2]; $priority = $result[0][3];
	$warningtext = '<span class="warningtext">Dette felt er p&aring;kr&aelig;vet!</span>';
	
	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data">', PHP_EOL;
	
	echo '<label for="event_name">Navnet eventen skal have: </label>', PHP_EOL;
	echo '<input type="text" name="event_name" id="event_name" value="';
	if(isset($_POST['event_name'])) echo htmlentities($_POST['event_name']);
	else echo $name;
	echo '" />', PHP_EOL;
	if($errors && empty($_POST['event_name'])) echo $warningtext, PHP_EOL;
	echo '<br />', PHP_EOL;
	
	echo '<label for="event_priority">Prioriteten eventen skal have (lavere tal er h&oslash;jere prioritet): </label>', PHP_EOL;
	echo '<input type="text" name="event_priority" id="event_priority" value="';
	if(isset($_POST['event_priority'])) echo htmlentities($_POST['event_priority']);
	else echo $priority;
	echo '" />', PHP_EOL;
	if($errors && empty($_POST['event_priority'])) echo $warningtext, PHP_EOL;
	elseif($errors && (!is_numeric($_POST['event_priority']) || $_POST['event_priority'] < 1 || $_POST['event_priority'] > 65536)) echo "Tallet skal v&aelig;re et positiv tal mellem 1 og 65536", PHP_EOL;
	echo '<br />', PHP_EOL;
	
	echo '<br />', PHP_EOL;
	echo '<label for="event_description">Beskrivelsen af eventen: </label><br />', PHP_EOL;
	echo '<textarea name="event_description" id="event_description" rows="15" cols="75">';
	if(isset($_POST['event_description'])) echo htmlentities($_POST['event_description']);
	else echo $description;
	echo '</textarea>', PHP_EOL;
	if($errors && empty($_POST['event_description'])) echo $warningtext, PHP_EOL;
	echo '<br />', PHP_EOL;
	
	echo '<label for="event_email">Email som tilmeldingen skal sendes til (optional): </label>', PHP_EOL;
	echo '<input type="text" name="event_email" id="event_email" value="';
	if(isset($_POST['event_email'])) echo htmlentities($_POST['event_email']);
	else echo $email;
	echo '" />', PHP_EOL;
	echo '<br />', PHP_EOL;
	
	echo '<br />', PHP_EOL;
	echo '<input type="hidden" name="id" value="' . htmlentities($_GET['id']) . '" />', PHP_EOL;
	echo '<input type="submit" name="event_delete" value="Slet denne event helt fra listen" />', PHP_EOL;
	echo '<br />', PHP_EOL;
	echo '<input type="submit" name="event_list" value="G&aring; tilbage til oversigten (alle &aelig;ndringer vil g&aring; tabt)" />', PHP_EOL;
	echo '<input type="submit" name="event_edit" value="Gem dine &aelig;ndring" />', PHP_EOL;
	echo '</form>', PHP_EOL;
	
	echo '<br />'; echo '<br />'; echo 'Aktive tilmeldinger:';
	listRegistrations(htmlentities($_GET['id']));
}
function addEvent($errors = false) {
	$warningtext = '<span class="warningtext">Dette felt er p&aring;kr&aelig;vet!</span>';
	
	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data">', PHP_EOL;
	
	echo '<label for="event_name">Navnet eventen skal have: </label>', PHP_EOL;
	echo '<input type="text" name="event_name" id="event_name" value="';
	if(isset($_POST['event_name'])) echo htmlentities($_POST['event_name']);
	echo '" />', PHP_EOL;
	if($errors && empty($_POST['event_name'])) echo $warningtext, PHP_EOL;
	echo '<br />', PHP_EOL;
	
	echo '<label for="event_priority">Prioriteten eventen skal have (lavere tal er h&oslash;jere prioritet): </label>', PHP_EOL;
	echo '<input type="text" name="event_priority" id="event_priority" value="';
	if(isset($_POST['event_priority'])) echo htmlentities($_POST['event_priority']);
	else echo $priority;
	echo '" />', PHP_EOL;
	if($errors && empty($_POST['event_priority'])) echo $warningtext, PHP_EOL;
	elseif($errors && (!is_numeric($_POST['event_priority']) || $_POST['event_priority'] < 1 || $_POST['event_priority'] > 65536)) echo "Tallet skal v&aelig;re et positiv tal mellem 1 og 65536", PHP_EOL;
	echo '<br />', PHP_EOL;
	
	echo '<br />', PHP_EOL;
	echo '<label for="event_description">Beskrivelsen af eventen: </label><br />', PHP_EOL;
	echo '<textarea name="event_description" id="event_description" rows="15" cols="75">';
	if(isset($_POST['event_description'])) echo htmlentities($_POST['event_description']);
	echo '</textarea>', PHP_EOL;
	if($errors && empty($_POST['event_description'])) echo $warningtext, PHP_EOL;
	echo '<br />', PHP_EOL;
	
	echo '<label for="event_email">Email som tilmeldingen skal sendes til (optional): </label>', PHP_EOL;
	echo '<input type="text" name="event_email" id="event_email" value="';
	if(isset($_POST['event_email'])) echo htmlentities($_POST['event_email']);
	echo '" />', PHP_EOL;
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
	echo '<input type="hidden" name="id" value="' . htmlentities($_POST['id']) . '" />', PHP_EOL;
	echo '<input type="submit" name="event_cancel" value="Annuller (eventen vil ikke blive slettet)" />', PHP_EOL;
	echo '<input type="submit" name="event_delete_confirmed" value="Accepter (event vil blive slette, dette kan ikke fortrydes)" />', PHP_EOL;
	echo '</form>', PHP_EOL;
}
else if(isset($_POST['event_delete_confirmed'])) {
	$name = "";
	
	try {
		$sql = "SELECT name FROM dikulan_event_administration WHERE id = ?";
		$stmt = $dbcon->prepare($sql);
		$stmt->execute(array($_POST['id']));
		$result = $stmt->fetchAll();
		$name = $result[0][0];
		
		$sql = "DELETE FROM dikulan_event_registration WHERE id = ?";
		$stmt = $dbcon->prepare($sql);
		$stmt->execute(array($_POST['id']));
		
		$sql = "DELETE FROM dikulan_event_administration WHERE id = ?";
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
	if(empty($_POST['event_name']) || empty($_POST['event_description'])) addEvent(true);
	elseif(empty($_POST['event_priority']) || !is_numeric($_POST['event_priority']) || $_POST['event_priority'] < 1 || $_POST['event_priority'] > 65536) addEvent(true);
	else {
		try {
			$sql = "INSERT INTO dikulan_event_administration(name, description, email, priority) VALUES(?, ?, ?, ?)";
			$stmt = $dbcon->prepare($sql);
			$stmt->execute(array($_POST['event_name'], $_POST['event_description'], $_POST['event_email'], $_POST['event_priority']));
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
		
		echo '<p>F&oslash;lgende event blev tilf&oslash;jet: ' . htmlentities($_POST['event_name']) . '</p>', PHP_EOL;
		echo '<a href="' . $_SERVER['PHP_SELF'] . '"><p>G&aring; tilbage til oversigten<p></a>', PHP_EOL;
	}
}
else if(isset($_POST['event_edit'])) {
	if(empty($_POST['event_name']) || empty($_POST['event_description'])) editEvent(true);
	else {
		try {
			$sql = "UPDATE dikulan_event_administration SET name = ?, description = ?, email = ?, priority = ? WHERE id = ?";
			$stmt = $dbcon->prepare($sql);
			$stmt->execute(array($_POST['event_name'], $_POST['event_description'], $_POST['event_email'], $_POST['event_priority'], $_POST['id']));
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
		
		echo '<p>F&oslash;lgende event blev opdateret: ' . htmlentities($_POST['event_name']) . '</p>', PHP_EOL;
		echo '<a href="?action=edit&id=' . htmlentities($_POST['id']) . '"><p>Se din &aelig;ndring<p></a>', PHP_EOL;
		echo '<a href="' . $_SERVER['PHP_SELF'] . '"><p>G&aring; tilbage til oversigten<p></a>', PHP_EOL;
	}
}
else if(isset($_POST['registration_delete'])) {
	try {
		$sql = "DELETE FROM dikulan_event_registration WHERE id = ? AND table_number = ?";
		$stmt = $dbcon->prepare($sql);
		
		//Run through all the checked values and delete those entries from the database.
		foreach($_POST['delete_registration'] as $value) {
			$stmt->execute(array($_POST['event_id'], $value));
		}
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

	if(empty($_POST['delete_registration'])) {
		echo '<p class="large">Du valgte ikke nogen tilmeldinger!<br />S&aring; der blev ikke slettet noget fra databasen.</p>';
	}
	else {
		echo '<p class="large">De valgte tilmeldinger blev slette.</p>';
	}
	
	echo '<a href="' . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . '"><p>G&aring; tilbage til den event du redigerede<p></a>', PHP_EOL;
	echo '<br >';
	echo '<a href="' . $_SERVER['PHP_SELF'] . '"><p>G&aring; tilbage til hoved oversigten<p></a>', PHP_EOL;
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















