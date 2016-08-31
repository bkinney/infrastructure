<?php 
/**
the udats_mm version has an onload that strips out step 1 (choosing a widget). Push to embed_udats.php without the onload to update existing tool.

This app has a mini-editor used to generate lists that serve as a datasource for a variety of widgets. Each widget requires a different wrapper, but all are based on lists except for the dialog. Once users are done adding their content, we package it up into the proper div with namespaced classes, so our custom js can operate on them by calling the appropriate jQuery widget object. Some of these widgets come straight from jqueryui, the rest are owned by moonlight multimedia. 
Dependent files:
/canvas/oembed/index.php - formats html as JSON
editor_files/listtricks.css|.js
**/
// Load up the Basic LTI Support code. We don't need the blti library for this anonymous tool
$returnurl = $_REQUEST['launch_presentation_return_url'];

?>
<!-- users complete this form to create the widget -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>Module Menu</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>

<!--listtricks copyright moonlight multimedia, NOT UD -->

<link href="editor_files/liststricks.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
.udats-faq, .udats-glossary{}
div{
	background-color:white;
}
.udats-quiz ol li ol li{
	display:list-item;
}
#step1{padding:10px;}
.instructions{font-size:80%; margin:10px;}

pre{
	white-space:normal;
}
#editor, #toolset {
	margin: 0px;
	padding: 5px;
	width: 600px;
	height:auto;
	border:#333 solid thin;
}
#toolset{
	background-color:#CCC;
	
}
#toolset a{
	color:#000;
	position:relative;
	top:2px;
	margin-right:5px;
	
	background-color: #FFE;
	text-decoration: none;
	font-family:"Times New Roman", Times, serif;
	font-size:12px;
	padding:5px;
	border:2px #FC0 outset;
	-webkit-border-radius: 6px;
	-khtml-border-radius: 6px;
	-moz-border-radius: 6px;
	border-radius: 6px;
}

#editor div{
	border:none;
	width:90%;
}
.glossary,.addtabs,#tabsui,.faq,.menu,.self-test,.dialog_ext,.dialog_id, .accordion, .udats_mm{
	display:none;
}

-->
</style>
<script>
var type;


function begin(editor){//once user selects a widget type, created sample content, and put it into the editor.
	type = editor;//$("#uitype").val();
var div = "." + editor;
var sample = "#" + editor;
	$(div).show();
	$(sample).children().detach().appendTo($("#editor"));
	$("#step1").hide();
}
<!-- start oEmbed script -->
function wrapAs(){
/*putting the contents of the mini-editor into the proper widget environment*/	
	
	//var url = "https://apps.ats.udel.edu/LTI436/html="
	var url = "https://apps.ats.udel.edu/canvas/html="
	switch(type){
		case "accordion":
		
			$( "#acc div.accordion" ).children().remove();
			
			$("#editor > ul li").each(function(index, element) {
		
			var tabTitle = $(this).text();
			$("#acc div.accordion").append('<h3>'+tabTitle+'</h3>' )
			.append('<div ><p>'+ tabTitle + ' content goes here.</p></div>');
   			 });

			url+= encodeURI($("#acc").html());//widget start/end content is added by oembed/index.php
		
			$("#url").val(url);
		break;
		case "addtabs":
		
			$( "#tabsui ul.tabmenu li" ).remove();
			$("#tabsui div.tabs div").remove();
			$("#editor div.tabs > ul li").each(function(index, element) {
		
			var tabCounter = index+1;
			var tabTitle = $(this).text();
			$("#tabsui ul.tabmenu").append('<li><a href="tabs-'+tabCounter+'">'+tabTitle+'</a></li>' );
			$("#tabsui div.tabs").append('<div id="tabs-'+tabCounter+'"><p style="display:none">---- COMMENT begin ' + tabTitle + ' content. Do not delete. Users will not see comments ---------</p><p>' + tabTitle + ' content goes here.</p><p style="display:none">---- COMMENT end ' + tabTitle + ' content. Do not delete.---------</p></div>');
   			 });

	
			url+= encodeURI($("#tabsui").html());
		
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
			$("#editor a").attr("title",settings).addClass("enhanceable_content").addClass(".dialog");
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
//these functions power the mini html editor
function setStyle(tag){
	document.execCommand(tag,false,null);
	
}
function createLink(){
	var url = type=="dialog_id" ? prompt("Enter id:","#") : prompt("Enter URL:","https://");
	if(url){
		document.execCommand('createlink',false,url);
	}
}
/*preview only works for the glossary*/
function toggleView(){

  if($("#viewbtn").text()=="preview"){
    //$("#editor").attr("contentEditable",false);
    $("#viewbtn").text("edit");
  }else{
   // $("#editor").attr("contentEditable",true);
    $("#viewbtn").text("preview");
  }
 $("#editor").toggle();
  $("#leftpanel").toggle();

  switch(type){
	  case "glossary":
	  var glossary = $("#editor div ul:first").html();
		 
		 $("#glist ul:first").html(glossary);
		  jQuery.fn.sort = function() {  
		   return this.pushStack( [].sort.apply( this, arguments ), []);  
		 };  
		  
		function sortAlpha(a,b){  
			return a.innerHTML.toLowerCase() > b.innerHTML.toLowerCase() ? 1 : -1;  
		};  
		$('ul.glossary ul').not('ul.glossary li ul').each(function(){
										  var sublist = "<ul>" + $(this).html() + "</ul>";
			$(this).prev('li').append(sublist);
			$(this).remove();
										  });
		$('ul.glossary li').not('ul li ul li').sort(sortAlpha).appendTo('ul.glossary');
		var glossary = $("#glist").html();
	  break;
	  case "faq":
	  break;
  }

}
var watchspace = false;
/*$(document).ready(function() {
	
		$("#editor").keyup(function(e) {
		e.preventDefault();
  		if(e.which == 13 && !e.shiftKey){//just hit enter
			watchspace=true;


		}else if(watchspace && e.shiftKey) {
				
			if(e.which==190){
				document.execCommand("indent",false,null);
			}else if(e.which==188){
				document.execCommand("outdent",false,null);
			}
			
			
			$("#editor").find("li").filter(function(){
				
				return $(this).text()=="<" || $(this).text()==">";
			}).text("");
		
			watchspace=false;
		}
		
	 });
	
});*///end ready

</script>

</head>

<body >
<div id="step1">
<p class="instructions">Select the type of UI Widget you would like to create. You can only create 1 UI widget per visit.</p>
<p class="instructions">Each type has it's own widget creation interface. Click done to return to Canvas. Once created, UI elements can be modified using the Canvas wiki editor.</p>
<select name="uitype" id="uitype" onChange="begin(this.value)">
<option value="dialog_id">Dialog</option>
 <!--  <option value="">select widget...</option>
  <option value="glossary">glossary</option>
  <option value="faq">faq</option>
  <option value="dialog_ext">Dialog link to url</option>
  
  <option value="udats_mm">Module Nav Menu</option>
<option value="self-test">self-test</option>
   <option value="menu">menu</option>-->
   <option value="sortable">sortable list</option>
  <option value="addtabs">tabs</option>
  <option value="accordion">accordion</option>
</select>
</div><!-- step1 -->

<div id="instructions"> 
<div class="sortable instructions">
<p>Enter the number of sortable boxes you need, and then click <strong>done</strong></p>
</div>
<div class="dialog_id instructions">
<p>A dialog link can also be used to display hidden content from within the current page. To create this effect, you must create a link that begins with a pound sign "#". The text following the pound must uniquely identify the hidden segment of content, and should contain no spaces or special characters. You do not need to create any anchor tags or hidden content at this time. Placeholder text for the hidden content will be created for you, and may be edited upon your return to Canvas.</p>
  <ol><li>Replace the words "Link Text" with the text you want users to click on to launch the overlay.</li><li>Using the link button, enter the #id for the hidden content. If you have created other dialogs, be sure NOT to use any #id you have used in the past.</li>
    <li>[optional] By default, your link-to content will appear centered, with a semi-transparent background partially hiding the original page content. To allow simultaneous access to hidden and original page content, select a deployment other than 'modal' from the dropdown in the editor toolbar. </li>
  <li>Click the done 'Done' button located beneath the editor.</li>
</ol>
</div>

<div class="addtabs instructions" >
<p>Enter the names of the tabs you require as bullet points in the list below, one bullet per tab</p><p>Tab titles will match the text of each bullet</p><p>Content for each tab panel can be edited after you return to Canvas.</p>
 
</div><!-- add tabs instr-->



 <div id="tabsui"><div id="tabs" class="enhanceable_content tabs"><ul class="tabmenu"><li><a href="#tabs-1">Tab 1</a></li></ul><div id="tabs-1"><p>Place your Tab 1 content here.</p></div></div></div><!--end tabsui -->
<div id="acc"><div class="enhanceable_content accordion"></div></div><!--end acc -->

 


</div>
<div class="glossary menu addtabs dialog_ext dialog_id accordion udats_mm" >


    <div id="toolset" style="height:30px;"><a href="#" onClick="setStyle('bold')" title="bold"><b>b</b></a><a href="#" onClick="createLink()" title="copy">link</a>
      <a href="#" onClick="setStyle('insertunorderedlist')" title="bullets">ul</a>
      <a href="#" onClick="setStyle('indent')" title="indent">&gt;&gt;</a>
      <a href="#" onClick="setStyle('outdent')" title="outdent">&lt;&lt;</a>
      

    <input type="submit" form="done" value="done" style="float:right">
    </div><!-- toolset  -->

    <div id="editor" contenteditable="true" style="height:200px;">
    
    </div><!-- editor  -->
</div><!-- editor toolset -->

<!-- this form is used to set up the return url required by an editor btn. See documentation 

https://canvas.instructure.com/doc/api/file.editor_button_tools.html 
The /canvas/oembed/ file returns JSON. 
-->
<form id="done" onSubmit="return wrapAs()" action="<?php echo $returnurl ?>" method="GET">
  <input type="hidden" value="oembed" name="embed_type">
  <input type="hidden"  name="url" id="url">
  <input type="hidden" value="http://apps.ats.udel.edu/canvas/oembed/" name="endpoint" id="endpoint">
  <input type="submit" value="Done">
</form>
<div id="addtabs" class="addtabs">
<div class="enhanceable_content tabs">
   <ul >
  <li>Tab 1</li>
 
</ul>

</div><!-- end addtabs -->

<div id="accordion" class="enhanceable_content accordion">

   <ul >
  <li>Panel 1</li>
</ul>

</div><!-- end accordion -->
<div class="sortable">
<input name="boxes" type="number">
</div>
<div id="sortable" class="enhanceable_content sortable" style="display:none">
</div>
<div id="dialog_id" class="dialog_id">

<p>Link Text</p>
<div id="id">Hidden should go here. </div>
</div><!-- end dialog -->
</div>

</div>




</body></html>