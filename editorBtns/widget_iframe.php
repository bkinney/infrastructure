<?php 
// Load up the Basic LTI Support code
//require_once '../ims-blti/blti.php';

// Initialize, all secrets are 'secret', do not set session, and do not redirect
//$context = new BLTI("none", false, false);
//new plan, this is an anonymous tool, so not even invoking lti
//editor buttons are strange beasts. basically, we return content in a get string appended onto the launch_presentation_return_url. Then Canvas sends it back, and we have to provide json.
if(array_key_exists('launch_presentation_return_url',$_REQUEST)){
	
$returnurl = $_REQUEST['launch_presentation_return_url'];
//print_r($_REQUEST);	
}else{//this is just for testing. I don't think it kicks in during actual use.
	$returnurl="../oembed/index.php";
	
session_start();

$old_sessionid = session_id();

session_regenerate_id();

$new_sessionid = session_id();



}
session_write_close();
?>
<html><head>

<title>Content Editable</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>


<script>

<!-- start oEmbed script -->
function wrapAs(){
	
	var type="iframe";
	//doesn't matter what is in front of the html= here. Not surewhy I even have anything there. The url is GETed to my oEmbed page (the endpoint)
	var url = "https://apps.ats.udel.edu/html="
	
	switch(type){
    	case "iframe"://this is actually the only case now
		var iframe= $("#frame");
		iframe.attr("width",iframe.width());
		iframe.attr("height",iframe.height());
		iframe.removeAttr("style");
		if(iframe.width()>760)$("#editor").prepend('<div class="showMenu">&nbsp;</div>')
        	url += encodeURI($("#editor").html());
			
            $("#url").val(url);
			break;
		case "accordion":
		
			$( "#acc div.udats-accordion" ).children().remove();
			
			$("#editor > ul li").each(function(index, element) {
		
			var tabTitle = $(this).text();
			$("#acc div.udats-accordion").append('<h3>'+tabTitle+'</h3>' )
			.append('<div ><p>' + tabTitle + ' content goes here.</p></div>');
   			 });

			url+= encodeURI($("#acc").html());
		
			$("#url").val(url);
		break;
		case "addtabs":
		
			$( "#tabsui ul.tabmenu li" ).remove();
			$("#tabsui div.udats-tabs div").remove();
			$("#editor div.udats-tabs > ul li").each(function(index, element) {
		
			var tabCounter = index+1;
			var tabTitle = $(this).text();
			$("#tabsui ul.tabmenu").append('<li><a href="tabs-'+tabCounter+'">'+tabTitle+'</a></li>' );
			$("#tabsui div.udats-tabs").append('<div id="tabs-'+tabCounter+'"><p>' + tabTitle + ' content goes here.</p></div>');
   			 });

	
			url+= encodeURI($("#tabsui").html());
		
			$("#url").val(url);
		break;
		case "dialog_ext":
		if($("#editor p:first a").length == 0){
			alert("Please use the link tool to create a link to the web page you want to open.");
			return false;
		}
			var settings = "title:"+ $("#editor a").text()+"|";
			$("#editor a").attr("title",settings).attr("target","udats-dialog");
			url += encodeURI($("#editor").html());
			$("#url").val(url);
		break;
		case "dialog_id":
		if($("#editor p:first a").length == 0){
			alert("Please use the link tool to create a unique #id.");
			return false;
		}
			var title = $("#editor a").text();
			var id = $("#editor a").attr("href").substr(1);
			var settings = $("#dialog_options").val() == "modal" ? "title:"+ title+"|" : "title:"+ title + "|modal:false|position:" + $("#dialog_options").val();
			$("#editor a").attr("title",settings).attr("target","udats-dialog");
			$("#editor div").attr("id",id);
			url += encodeURI($("#editor").html());
			$("#url").val(url);
		break;
		default:
		url += encodeURI($("#editor").html());
		$("#url").val(url);
		
	}
	return true;
	
}
</script>
<!-- start editor script -->
<script>
function updateRes(){
	
		var f = $("#frame");
	
		$("input[name=width]").val(f.width());
		$("input[name=height]").val(f.height());
	
}

function constructURL(str){
	var basepath="";
	if(str.substr(0,4)!="http"){
		basepath ="https://<?php echo $_REQUEST['custom_domain_url'] ?>/courses/<?php echo $_REQUEST['custom_canvas_courseid'] ?>/file_contents/course%20files/"
	}else if(str.substr(0,7)=="http://"){
		alert("trying https. if no content appears, this page can not be embedded");
		str = str.replace("http://","https://");
		$("#link").val(str);
	}
	$("#frame").attr("src",basepath + str);
	
}

</script>

</head>

<body>

<a onclick='$("#instructions").toggle();'><button>show/hide instructions</button></a><input type="submit" value="Done" form="done">
<div id="instructions">
<blockquote>
<p>Use this utility to open Flash content (such as Jing video)from your course files in an iframe. If desired, you can also embed external web pages, but only from a secure (https) server.
<p>To link to a page in your Canvas course files, enter the folder, subfolders(if any) and filename, eg. softchalk1/index.html </p>
<p>Please note that addresses starting with http:// (not http<strong>s</strong>://) will not be visible within Canvas due to browser security issues. To deploy content from an insecure web host, please contact ATS</p>
<p>You can adjust the height and width of your content frame by entering pixel values into the height and width fields. Click show/hide instructions if you need more space.</p></p></blockquote>
<form >
  <input id="link" name="link" onBlur="constructURL(this.value)"  type="text"   placeholder="enter file path here" size="60">
  <label>width: <input name="width" type="text" onkeyup='$("#frame").width(this.value)' size="5" maxlength="5" placeholder="778"></label>
  <label>height: 
    <input name="height" type="text" onkeyup='$("#frame").height(this.value)' size="5" placeholder="573"></label>
  

</form>
</div>

 
<div id="editor" >
<iframe id="frame" src="https://apps.ats.udel.edu/canvas/assets/embedding_jing.swf" width="778" height="573" scrolling="yes"  ></iframe></div>
<form id="done" onSubmit="return wrapAs()" action="<?php echo $returnurl ?>" method="GET">
  <input type="hidden" value="oembed" name="embed_type">
  <input type="hidden"  name="url" id="url">
  <input type="hidden" value="https://apps.ats.udel.edu/canvas/oembed/" name="endpoint" id="endpoint">
  
  <input type="submit" value="Done">
</form>

</body></html>