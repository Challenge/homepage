<?php 
include getcwd() . '/../../..' . '/JoomlaExternalCommunicator.inc';
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

<?php 
include 'dbcon.php';
include 'tickets.php';

$maxSeats = 80;
	function makeDB(){
		global $dbcon,$maxSeats,$ticketArray;
		try {
			/* drops all the tables */
			executeQuery('DROP TABLE reservationstable', 'reservationstable dropped<br>');
			executeQuery('DROP TABLE tickettable', 'tickettable dropped<br>');
				
			/* creates the tables again */
			executeQuery(
			'Create TABLE tickettable(	
				ticket_id varchar(255) NOT NULL PRIMARY KEY
			)','tickettable created<br>');
			
			executeQuery(
			'Create TABLE reservationstable(
				seat_number int UNIQUE NOT NULL,			
				ticket_id varchar(255) UNIQUE NOT NULL,
				
				CONSTRAINT fk_ticket_id FOREIGN KEY (ticket_id) REFERENCES tickettable(ticket_id),
				CONSTRAINT seat_check CHECK (seatNumber < '.$maxSeats.' AND seatNumber > 0)
			)','reservationstable created<br>');
			

		foreach($ticketArray as $id) {
			executeQuery('INSERT INTO tickettable (ticket_id) VALUES ("'.$id.'")',
						 '['.$id.'] Has been added'.'<br/>');

		}

		
		} catch(PDOException $e){
			echo $e->getMessage();
		}
	}
		function executeQuery($query,$succesMsg){
		global $dbcon;
		$dbcon->exec($query);
		wasQuerySuccesful($succesMsg);
	}
	
	function printErrorArray($errorInfo){
		echo '#####################################<br />';	
		echo $errorInfo[0].'<br />';
		echo $errorInfo[1].'<br />';
		echo $errorInfo[2].'<br />';
		echo '#####################################<br />';	
	}
	
	
	function wasQuerySuccesful($succesMsg){
	global $dbcon;
	
		if($dbcon->errorCode() == 00000){
			echo $succesMsg;
		} else {
			$errorInfo = $dbcon->errorInfo();
			printErrorArray($errorInfo);
		}
		
		return $dbcon->errorCode();
	}

	
	makeDB();
	
?>