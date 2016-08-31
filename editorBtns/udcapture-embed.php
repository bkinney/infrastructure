<?php 
// Load up the Basic LTI Support code

include "/home/bkinney/includes/lti_db.php";
include "/www/canvas/apptracker.php";
// this is a very simple app that uses the rich content editor button extension to pop a ud capture video into an iframe in the Canvas wiki. for more information see https://canvas.instructure.com/doc/api/file.editor_button_tools.html
// Initialize blti using Dr. Chuck's library pretty much as is - for this use, none of the changes I made in his code are relevant

/*	
	if($_POST['selection_directive']=="embed_content"){
		$qstring = "?url=replaceme&embed_type=iframe&height=480&width=640";
	}else{
		
	}*/
	

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<title>Anonymous UDCapture Embed</title>
<style>
input.short{
	width:5em;
}
</style>
<script>
function writeframe(movie){
	//we seem to have some redundancy here, but the point is to be sure we are linking securely, lest the browser fail to load iframe content
	var orig = "<?php echo $qstring ?>";
	var redirect = orig.replace("replaceme",movie).replace("http:","https:");
	document.location = "<?php echo $_POST['launch_presentation_return_url'] ?>" + redirect;
}
function setDimensions(){
	
	var wide = $("#shape:checked").length==1 ? true : false;
	var size = $('input[name="vidsize"]:checked').val();
	var w=320;
	var h=240;
	if(wide){
		switch(size){
			case 's':
				w=320;
				h=180;
				break;
			case 'm':
				w=640;
				h=360;
				break;
			case 'l':
				w=720;
				h= 405;
				break;
		}
	}else{
		switch(size){
			case 's':
				w=320;
				h=240;
				break;
			case 'm':
				w=640;
				h=480;
				break;
			case 'l':
				w=768;
				h= 576;
				break;
		}
	}
	//we seem to have some redundancy here, but the point is to be sure we are linking securely, lest the browser fail to load iframe content
	$("#width").val(w);
	$("#height").val(h);
}
function secureurl(input){
	return input.value.replace("http:","https:");
}
</script>
</head>

<body>
<p></p>
<p>Enter the full url of the udcapture movie you wish to embed. </p>
<form onSubmit="this.url.value += '&embed=1'" action="<?php echo $_REQUEST['launch_presentation_return_url']; ?>">
  <p>
    <input name="url" type="text" id="url" size="50" onChange="this.value=secureurl(this)"/>
    <input type="hidden" value="iframe" name="embed_type">
  </p>
  <p>Widescreen:
    <label>
      <input type="checkbox" name="shape" id="shape" onChange="setDimensions()">
    </label>
  </p>
  <p>Size:
    <label>
      <input name="vidsize" type="radio" id="vidsize_0" onChange="setDimensions()" value="s" checked="CHECKED">
      small</label>
  
    <label>
      <input type="radio" name="vidsize" value="m" id="vidsize_1" onChange="setDimensions()">
      medium</label>
    
    <label>
      <input type="radio" name="vidsize" value="l" id="vidsize_2" onChange="setDimensions()">
      large<br>
    </label>
    <br>
    <input class="short" name="width" type="text" id="width" value="320" >
x    
<input class="short" name="height" type="text" id="height" value="240" >
  pixels
  <p><input type="submit" value="enter"></p>
</form>
</body>
</html>