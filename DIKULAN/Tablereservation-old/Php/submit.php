<?php
/*
	Dependencies
		- *dataDir*\settings.php
		- *phpDir*\reservationFields.php
		- *phpDir*\schema.php
		- *jsDir*\selected.js
		- *jsDir*\submit.js
*/

/* Should the errors be shown?
   note: if submit is set in the query, errors are shown regardless
*/
$showErrors = 0;

/* The destionation for a successful submition*/
$succesDest = $root;

/* All the error messages to be shown */
$selectedErrorText1 = "Plads ";
$selectedErrorText2 = " er desv&aelig;rre allerede blevet reserveret";
$noSelectionErrorText = "Der er ikke blevet valgt nogle pladser. \\n Du kan v&aelig;lge pladser ved at trykke p&aring; et eller flere borde nedenfor. ";
$undefinedTicketErrorText = "Indtast venligst billet ID";
$invalidTicketIdErrorText = "Det indtastede billet ID er ugyldigt";
$eid = 'errorView';		
$alreadyHasReservationErrorText = "Der er allerede blevet reserveret en plads til billet id: ";
$dublicateTicketErrorText = "Gentaget billet ID";
$cancelReservationText1 = 
"Kilk her for at annullere reservationen p&aring; plads: ";
$cancelReservationText2 = ". Til billet id: ";

/* A count that tells the amount of errors*/
$hasError = 0;


/* Adds an message to a tag with the given id.*/
function addFieldError($id, $error){
	echo 'addError('.$id.','.$error.');';
}

/* Checks for Ticket errors ie. invalid tickets. 
   Adds an error message for each violation found.
*/
function makeTicketErrors($seat, $ticket){
	global $undefinedTicketErrorText, $invalidTicketIdErrorText;
	
	$id = 'field'.$seat;
	if(isSeatTaken($seat) != 1){
		if($ticket == 'undefined'){	
				addError($id,$undefinedTicketErrorText);
		} else if(isValidTicket($ticket) != 1){
				addError($id,$invalidTicketIdErrorText);
		}
	}
	
}

/* returns true if the given seat is taken */
function isSeatTaken($seat){
	global $takenArray;
	return isset($takenArray[$seat]);
}

/* returns the seat of the given ticketID, returns false the ticket has not been used*/
function isTicketUsed($ticketId){
	global $takenArray;
	
	foreach($takenArray as $seat => $ticket)
		if($ticket == $ticketId)
			return $seat;		
	
			return false;
}

/* Checks all the textfields for dublicate ticket id's*/
function makeDublicateErrors($seat,$ticket){
	global $takenArray,$eid,$dublicateTicketErrorText,$defaultTextBoxText;
	
	for($i = 0; isset($_REQUEST['selected'.$i]);$i++){	
		$id = explode('_',$_REQUEST['selected'.$i]);
	
		if($ticket == $id[1] && $ticket != 'undefined' && $seat != $id[0]){
			addError('field'.$seat,$dublicateTicketErrorText);	
		}
		
	}
}

/* Check to see if all the selected seats are valid. 
 * e.g not taken
*/
function makeSelectedErrors($seat, $ticket){
			global $selectedErrorText1, $selectedErrorText2,$eid;
			
			if(isSeatTaken($seat) != 0){
				addError($eid,$selectedErrorText1.$seat.$selectedErrorText2);	
			}	
}

/* initializes everything except the schema itself (see schema.php) */
function init(){
	global $eid, $showErrors, $selectedErrorText1, $selectedErrorText2,$noSelectionErrorText, $hasError;		
	$i = 0;	
	
	/* is "showErrors" defined? if yes use that value*/
	if(isset($_REQUEST['showErrors']))
		$showErrors = $_REQUEST['showErrors'];
	
	if($showErrors == 1 || isset($_REQUEST['submit'])){
		/* Only show errors if the user has submitted atleast once */
		$showErrors = 1;
		
		for($i = 0; isset($_REQUEST['selected'.$i]);$i++){	
			$id = explode('_',$_REQUEST['selected'.$i]);
			makeSelectedErrors($id[0],$id[1]);
			makeTicketErrors($id[0],$id[1]);
			makeDublicateErrors($id[0],$id[1]);
		}
	
	if($i == 0)
		addError($eid,$noSelectionErrorText);
	} else {
		/* Don't show errors on theme change if the user has not selected atleast one seat*/
		$showErrors = 0;
	}
		
	/* if there was no errors found and submit is defined in the query string */
	if($hasError == 0 && isset($_REQUEST['submit'])){
		/* array containing information on successful or changed reservations */
		$succesArray = array();
		$changeArray = array();

		startTransaction();
		
		/* itterate on all the selections*/
		for($i = 0; isset($_REQUEST['selected'.$i]) ;$i++){
			$selection = explode('_',$_REQUEST['selected'.$i]);
			/* Can the reservation be made? */
			if(makeReservation($selection[0],$selection[1]) != '00000'){
				/* if not, can the reservation be changed? */
				if(changeReservation($selection[0],$selection[1]) != '00000'){
					addError($eid,$selectedErrorText1.$selection[0].$selectedErrorText2);
				} else {
					/* if the reservation was changed add it to the changeArray */
					$changeArray[$i] = $_REQUEST['selected'.$i];
				}
			}else{
					/* if the reservation was successful add it to the succesArray */
					$succesArray[$i] = $_REQUEST['selected'.$i];
			}
		
		}
		/* If theres still no errors, commit the changes and call doSuccess*/
		if($hasError == 0){
			commit();
			doSuccesMessage($succesArray,$changeArray);

		} else {
			rollbackTransaction();	
		}
	}

}


/* add an error to the tag with the given id*/
function addError($id,$error){
global $hasError;
	$hasError++;
	echo 'addError("'.$id.'","'.$error.'");';
}

/* Show a popup box contain information about the reservation,
	when the popup is closed, redirect to $succesDest
	IF POSSIBLE CHANGE THIS TO SOMETHING BETTER!!!!
	*/
function doSuccesMessage($succesArray, $changeArray){
global $succesDest;
 $str = '';
 
 if(sizeof($succesArray) > 0){
 $str = "Følgende pladser er blevet reserveret:\\n"; 
  foreach($succesArray as $index => $select){
   $t = explode('_',$select);
   $str.= "Plads: $t[0] med billet id : $t[1]\\n"; 
  }
 }
 
 if(sizeof($changeArray) > 0){
  $str .= "\\nFølgende reservation er blevet ændrt :\\n";
  
  foreach($changeArray as $index => $change){
   $t = explode('_',$change);
   $str.= "Billet id : $t[1] plads er blevet ændret til : $t[0]\\n"; 
  }
 }
 
 if(sizeof($changeArray) == 0 && sizeof($succesArray) == 0)
  return;
 
 
 $str = str_replace(array('æ','ø','å'), array('\u00E6','\u00F8','\u00E5'), $str);
 echo 'alert("'.$str.'");';
 echo "window.location.href = '$succesDest';";
}


?>
