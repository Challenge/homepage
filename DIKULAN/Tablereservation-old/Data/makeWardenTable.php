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

$inputPath = 'Wardens.txt';
$input = file($inputPath,FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
$wardenArray = array();
$i = 0;

foreach ($input as $line_num => $line) {
	$info = explode('|',$line);
	$q = 3;
	$date = '2012-04-20';
	
	if($info[0] == 'lørdag')
		$date = '2012-04-21';
	else if($info[0] == 'søndag')
		$date = '2012-04-22';
	
	$start = $info[1];
	$end = $info[2];

	
	while(isset($info[$q])){
	
		if($info[$q] == '')
			break;
		$finalStartDate = $date." ".$start;
		$finalEndDate = $date." ".$end;
		$wardenArray[$i] = array("name" => $info[$q], "start" => $finalStartDate, "end" => $finalEndDate); 

		echo $wardenArray[$i]['name'].' added with start: '.$wardenArray[$i]['start'].' and end: '.$wardenArray[$i]['end'].'</br>';
		$i++;
		$q++;
	}

}

function makeTable(){
		global $dbcon,$wardenArray;
		try {
			/* drops all the tables */
			executeQuery('DROP TABLE WARDENSTABLE', 'Wardenstable dropped<br>');
			executeQuery('DROP TABLE WARDENTIMETABLE', 'Wardentimetable dropped<br>');

			
			/* creates the tables again */
			executeQuery(
			'Create TABLE WARDENSTABLE(	
				warden varchar(255) NOT NULL PRIMARY KEY
			)','Wardenstable created<br>');
			
			executeQuery(
			'Create TABLE WARDENTIMETABLE(	
				Id int NOT NULL AUTO_INCREMENT,
				warden varchar(255) UNIQUE NOT NULL,
				start_time DATETIME ,	
				end_time DATETIME ,
				PRIMARY KEY (id),
				CONSTRAINT fk_warden FOREIGN KEY (warden) REFERENCES WARDENSTABLE(warden)
				
			)','Wardentimetable created<br>');
 
		foreach($wardenArray as $info) {
			$warden = $info['name'];
			$start = $info['start'];
			$end = $info['end'];
			
			echo $warden."</br>";
			
			executeQuery('INSERT INTO WARDENSTABLE (warden) VALUES ("'.$warden.'")',
						 '['.$warden.'] Has been added'.'<br/>');
						 
			executeQuery(
			"INSERT INTO WARDENTIMETABLE (warden, start_time, end_time)
			VALUES ('$warden','$start','$end')",
						 '['.$warden.'] Has been added'.'<br/>');
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

	makeTable();
	
?>