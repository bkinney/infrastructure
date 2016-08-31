<?php 
/**
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

<title>Content Editable</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
<script type="text/javascript" src="editor_files/listtricks.js"></script>
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
.glossary,.addtabs,#tabsui,.faq,.menu,.self-test,.dialog_ext,.dialog_id, .accordion{
	display:none;
}

-->
</style>
<script>
var type;


function begin(editor){//once user selects a widget type, created sample content, and put it into the editor.
	type = $("#uitype").val();
var div = "." + editor;
var sample = "#" + editor;
	$(div).show();
	$(sample).children().detach().appendTo($("#editor"));
	$("#step1").hide();
}
<!-- start oEmbed script -->
function wrapAs(){
/*putting the contents of the mini-editor into the proper widget environment*/	
	
	var url = "https://apps.ats.udel.edu/LTI436/html="
	
	switch(type){
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
			var settings = "title:"+ $("#editor a").text()+"|modal:true";
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

<body>
<div id="step1">
<p class="instructions">Select the type of UI Widget you would like to create. You can only create 1 UI widget per visit.</p>
<p class="instructions">Each type has it's own widget creation interface. Click done to return to Canvas. Once created, UI elements can be modified using the Canvas wiki editor.</p>
<select name="uitype" id="uitype" onChange="begin(this.value)">
  <option value="">select widget...</option>
  <option value="glossary">glossary</option>
  <option value="faq">faq</option>
  <option value="dialog_ext">Dialog link to url</option>
  <option value="dialog_id">Dialog link to #</option>
<option value="self-test">self-test</option>
<!--    <option value="menu">menu</option>-->
  <option value="addtabs">tabs</option>
  <option value="accordion">accordion</option>
</select>
</div><!-- step1 -->
<div class="dialog_ext instructions">
<p>A dialog link is used to open an external web page in an overlay window. The overlay prevents users from interacting with the original page contents while the dialog is open. Please note that most browsers will block insecure content from launching in the overlay. You should only link to https:// addresses with this tool. Create a link in the editor below.</p>
  <ol><li>Replace the words "Link Text" with the text you want users to click on to launch the overlay.</li><li>Using the link button, enter the url of the page you want to link to.</li>
  <li>Click the done 'Done' button located beneath the editor.</li>
</ol>
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

<div class="accordion instructions" >
<p>Enter the names of the panels you require as bullet points in the list below, one bullet per tab</p><p>Tab titles will match the text of each panel's header</p><p>Content for each panel can be edited after you return to Canvas.</p>
 
</div><!-- add accordion instr-->

 <div id="tabsui"><div id="tabs" class="udats-tabs"><ul class="tabmenu"><li><a href="#tabs-1">Tab 1</a></li></ul><div id="tabs-1"><p>Place your Tab 1 content here.</p></div></div></div><!--end tabsui -->
<div id="acc"><div class="udats-accordion"></div></div><!--end acc -->
 <div class="instructions glossary"><p>Create a glossary using the example below. Click view to see how it will work.</p>

<p>Click Done to return to Canvas with your new glossary!</p>
</div> <!--glossary instr -->
 
 <div class="faq"><p>The ATS FAQ tool is simply a bulleted list located inside a special portion of your Pages page. Starter questions have been provided. You can edit these and add your own questions once you return to Canvas.</p>

<p>Click Done to return to Canvas with your FAQ!</p> 
</div><!--faq instr -->
 <div class="self-test"><p>The ATS Self-Test tool is simply a numbered list located inside a special portion of your Pages page. A starter questions has been provided. You can edit this and add your own questions once you return to Canvas. When writing items, be sure to list the correct answer FIRST for every question. You may only have one correct answer per question. The order of answer choices will be randized each time the quiz is viewed.</p>

<p>Click Done to return to Canvas and get started</p> 
</div><!--faq instr -->
<div class="glossary menu addtabs dialog_ext dialog_id accordion" >


    <div id="toolset" style="height:30px;"><a href="#" onClick="setStyle('bold')" title="bold"><b>b</b></a><a href="#" onClick="createLink()" title="copy">link</a>
      <a href="#" onClick="setStyle('insertunorderedlist')" title="bullets">ul</a>
      <a href="#" onClick="setStyle('indent')" title="indent">&gt;&gt;</a>
      <a href="#" onClick="setStyle('outdent')" title="outdent">&lt;&lt;</a>
      
    <a align="right" href="#" id="viewbtn" class="glossary" onClick="toggleView()">preview</a>
    <select id="dialog_options" class="dialog_id">
    <option value="modal">modal</option>
    <option value="right top">top right</option>
    <option value="left top">top left</option>
    <option value="center">center (no overlay)</option>
    <option value="right bottom">bottom right</option>
    <option value="left bottom">bottom left</option>
    </select>
    </div><!-- toolset  -->

    <div id="editor" contenteditable="true" style="height:200px;">
    
    </div><!-- editor  -->
</div><!-- editor toolset -->
<div id="viewglossary" class="glossary">
    <div id="leftpanel" style="display:none"><input type="text" id="searchfield" onKeyUp="locateTerm()" value="search">
     
      <input type="submit" name="clearSearch" id="clearSearch" value="show all" accesskey="x" onClick="clearSearch()" tabindex="2">
    
        <div id="glist" style="height: 250px;">
          <ul class="glossary"></ul>
        
        </div><!--glist-->
        <div id="defdisplay"> definitions
          <ul>
            <li>appear here when you click a term</li>
          </ul>
          <p>&nbsp;</p>
        </div><!--defdisplay-->
    </div><!--leftpanel-->
</div><!--view glossary-->
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
<div class="udats-tabs">
   <ul >
  <li>Tab 1</li>
</ul>
</div><!-- end addtabs -->

<div id="accordion" class="accordion">

   <ul >
  <li>Panel 1</li>
</ul>
</div><!-- end accordion -->

<div id="dialog_id" class="dialog_id">

<p>Link Text</p>
<div id="id">Hidden should go here. </div>
</div>
</div>
<div id="dialog_ext" class="dialog_ext">

<p>Link Text</p>

</div>
<div id="glossary" class="glossary">
<div class="udats-glossary">
   <ul >
  <li>new term</li>
  <ul><li>Place definitions inside a second level bullet point directly below the question</li></ul>
  <li>jQuery</li>
  <ul><li>A javascript library that does all the heavy lifting inside Canvas</li></ul>
  </ul>
  </div>
</div>
<div id="faq" class="faq"> <div class="udats-faq" >
<ul>
  <li>Enter your questions as first level list-items</li>
  <ul><li>Answer each question inside a second level bullet point directly below the question</li></ul>
  <li>Second question goes here</li>
  <ul><li>Answers will be revealed each time the user clicks on question text</li></ul>
  </ul>
  </div>
  </div><!-- end id faq -->
  <div id="self-test" class="self-test">
  <div class="udats-quiz" style="clear: both;">
 <ol>
<li>Enter question text here
<ol>
<li>Always list the correct answer first. Responses will be randomized each time a student takes the self-test.</li>
  <ol>
    <li>Answer-specific feedback goes here. This is optional.</li>
  </ol>

<li>This is an incorrect response</li>
  <ol>
    <li>Feedback for this response</li>
  </ol>

<li>Another incorrect answer</li>
  <ol>
    <li>Feedback</li>
  </ol>

</ol>
</ol>
</div>
  </div>
</body></html>