<?php
isset($_GET['width']) ? $width = $_GET['width'] : $width = 800;
isset($_GET['height']) ? $height = $_GET['height'] : $height = 600;
(isset($_GET['preload']) && strcmp($_GET['preload'], "false") == 0) ? $preload = false : $preload = true;
?>


<html>
<head>
<title>Projecter</title>
</head>
<body style="padding: 0px; margin: 0px;">

<script type="text/javascript">
var ajaxRequest;  // The variable that makes Ajax possible!
window.onload = init();

function init() {
	try{
		// Opera 8.0+, Firefox, Safari
		ajaxRequest = new XMLHttpRequest();
	} catch (e){
		// Internet Explorer Browsers
		try{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				// Something went wrong
				alert("Your browser broke!");
				return false;
			}
		}
	}
	
	changeImage();
}

<?php if($preload): ?>
function preloadImage(imagePath, target) {
	var img = new Image();
	img.onload = function() { target.src = ajaxRequest.responseText; }
	img.src = imagePath;
}
<?php endif; ?>

function changeImage() {
	var target = 0;
	target = document.getElementById("imageContainer");
	
	if (target) {
		ajaxRequest.open("GET", "ProjekterScript.php", false);
		ajaxRequest.send(null);
		
		if(target.src != ajaxRequest.responseText) {
			<?php if($preload): ?>
				preloadImage(ajaxRequest.responseText, target);
			<?php else: ?>
				target.src = ajaxRequest.responseText;
			<?php endif; ?>
		}

		setTimeout("changeImage()", 2000);
	}
	else {
		setTimeout("changeImage()", 500);
	}
}
</script>

<div id="content_image" style="padding: 0px; margin: 0px;">
<img src="" alt="" id="imageContainer" width="<?php echo $width; ?>" height="<?php echo $height; ?>" />
</div>

</body>
</html>


























