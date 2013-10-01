<?php
$inputPath = 'Scripts/tickets.txt';
$input = file($inputPath,FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
$ticketArray = array();
$i = 0;

foreach ($input as $line_num => $line) {
	$info = explode('|',$line);

	$ticketArray[$info[0]] = $info;
	$ticketArray[$i] = $info;
	$i++;
}


function printTickets(){
global $ticketArray;
	$i = 1;
	foreach($ticketArray as $ticket) {
		echo $i++." : ".$ticket.'</br>';
	}
}

?>