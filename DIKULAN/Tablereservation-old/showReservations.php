<?php
include "Data/settings.php";
include "Php/databaseInteraction.php";
include 'Scripts/tickets.php';

/* bubblesort because im fucking lazy*/
function doSort($array){
	for($i = 0; $i < sizeof($array); $i++){
		for($j = 0; $j < sizeof($array); $j++){
		
			if($array[$i][0] < $array[$j][0]){
				$temp = $array[$i];
				$array[$i] = $array[$j];
				$array[$j] = $temp;
			}
		}
	}
	
	return $array;
}

$reservations = getReservations()->fetchAll();
$reservations = doSort($reservations);



?>
<table border ="1">
<tr> 
<td> <b> Plads nummer </b> </td>
<td> <b> Billet ID </b> </td>
<td> <b> S&aelig;lger </b> </td>
<td> <b> Navn </b> </td>
<td> <b> Email </b> </td>
</tr>
<?php

foreach($reservations as $row){
?>
<tr> 
<td> <?php echo $row[0];?> </td>
<td> <?php echo $row[1];?> </td>
<td> <?php echo str_replace(array('æ','ø','å'), array('&aelig;','&oslash;','&aring;'), $ticketArray[strtoupper($row[1])][2]);?> </td>
<td> <?php echo str_replace(array('æ','ø','å'), array('&aelig;','&oslash;','&aring;'), $ticketArray[strtoupper($row[1])][3]);?> </td>
<td> <?php echo str_replace(array('æ','ø','å'), array('&aelig;','&oslash;','&aring;'), $ticketArray[strtoupper($row[1])][4]);?> </td>
</tr>
<?php	
}
echo "</p>";

?>
</table>
