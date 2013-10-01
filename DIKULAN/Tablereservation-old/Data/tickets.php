<?php
$inputPath = 'tickets.txt';
$input = file($inputPath,FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
$ticketArray = array();
$i = 0;

foreach ($input as $line_num => $line) {
	$ticketArray[$i++] = $line;
}


function printTickets(){
global $ticketArray;
	$i = 1;
	foreach($ticketArray as $ticket) {
		echo $i++." : ".$ticket.'</br>';
	}
}

?>