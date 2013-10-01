<?php
//Denne fil indeholder generelle værdier om hele bordreservationsdelen
//De vigtigtste værdier er samlet her så man hurtigt kan ændre dem hvis man finder det nødvendigt.

	/* Directories for different compontents of this system */
	$graphicDir = 'Graphics/';
	$dataDir = 'Data/';
	$phpDir = 'Php/';
	$jsDir = 'Javascript/';
	
	/* The size of each tile */
	$tileSize = 20;

	/* The Width and Height of the room, given in tiles */
	$roomWidth = 16;
	$roomHeight = 36;
	
	/* DO NOT CHANGE THIS */
	$seatCount = 1;
	
	/* The Width and Height of the room given in pixels */
	$realRoomHeight  = $tileSize * $roomHeight;
	$realRoomWidth = $tileSize * $roomWidth;
	
	/* Help text */
	$helpGuest = 'Billet ID er det id der står på din billet som har formen xxxx-xxxx-xxxx';

	/* Defualt text for generated textboxes */	
	$defaultTextboxTextTicket = 'Indtast billet ID';
	$defaultTextboxTextName = 'Indtast dit navn her';
	
	/* The theme chosen when no theme has ben specified
	 * Note that a theme consists of a css file located in "graphicsDir" and a php located in "dataDir"
	 */
	$defaultTheme = 'Zelda';	
		
	$room = $dataDir.$defaultTheme.".php";
	$roomStyle = $graphicDir.$defaultTheme."Style.css";
	
	/* When redirecting, submitting and changeing theme, you end up on this site */
	$root = 'index.php';
?>