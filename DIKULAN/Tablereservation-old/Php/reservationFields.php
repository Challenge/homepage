<?php
//Denne fil indeholder alle javascripts og events der bruges i forbindelse med
//oprettelsen af reservations boksene der popper op når man klikker på ledig plads.
?>

<script type="text/javascript"> 
/* default text for the textboxes in the Fields*/
var defaultTextboxText = '<?php echo $defaultTextboxText; ?>';

/* sets the value of the textbox for the given seat */
	function setTextBoxValue(seat,value){
		var temp = document.getElementById('textbox'+seat);
		temp.setAttribute('value',value);
	}

/* gets the value of the textbox for the given seat */
	function getTextBoxValue(seat){
		var val = document.getElementById("textbox"+seat).value;
		
		if(val == defaultTextboxText || val == '')
			return undefined;
		else		
			return val;
	}
	
/* Called when a textbox with the given id gains focus */
	function onFocusTextBox(id){
		var temp = document.getElementById(id);

		if(temp.value == defaultTextboxText){
			temp.setAttribute('value','');
		}
	}

/* Called when a textbox with the given id loses focus */	
	function onFocusLostTextBox(id){	
		var temp = document.getElementById(id);

		if(temp.value == '' || temp.value == undefined){
			temp.setAttribute('value',defaultTextboxText);
		}
	}
	
/* Adds a field for the given seat */
	function makeField(seat){		
		var masterForm = document.createElement('div');
		var fieldset = document.createElement('fieldset');
		var legend = document.createElement('legend');
		var img = document.createElement('img');
		var input = document.createElement('input');
		var dropdown = document.createElement('select');

		/* the title-label for the field */
		legend.innerHTML = 'Plads: '+seat;
		legend.setAttribute('class','formLegend');

		/* the Help-icon next to the title-label*/
		img.setAttribute('src','Graphics/global/questionMark.png');
		img.setAttribute('class','help');
		img.title = '<?php echo $helpGuest;?>'

		/* the textbox in the field*/
		var textBoxId = 'textbox'+seat
		input.setAttribute('id',textBoxId );
		input.setAttribute('class', 'textbox');		
		input.setAttribute('onfocus', 'onFocusTextBox("'+textBoxId+'")');
		input.setAttribute('onblur', 'onFocusLostTextBox("'+textBoxId +'")');
		input.setAttribute('value',defaultTextboxText);	
		input.setAttribute('type','text');
		
		/* the div containing all the elements*/
		masterForm.setAttribute('id','reservationform'+seat);
		masterForm.setAttribute('class','reservationForm');
		masterForm.appendChild(fieldset);	
	
		/* the box with the title-label as title*/
		fieldset.innerHTML += '<p class="textboxLabel">Billet ID: </p>';
		fieldset.setAttribute('id', 'field'+seat);
		fieldset.setAttribute('class', 'field');
		fieldset.appendChild(input);
	
		legend.appendChild(img);
		fieldset.appendChild(legend);
		
		document.getElementById('fieldDiv').appendChild(masterForm);
	
	}


	/* wrapper function for makeField 
	 This function is called whenever a user selects a seat
	 */
	function addReservationField(seat){
		makeField(seat);	
	}

	/* removes a reservationField with the given seat */
	function removeReservationField(seat){
		var temp = document.getElementById('reservationform'+(seat));
		temp.parentNode.removeChild(temp);
	}

</script>