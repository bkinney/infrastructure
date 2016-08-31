<?php 
ini_set('session.gc_maxlifetime', 3600);

// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params(3600);
// Load up the Basic LTI Support code, no oAuth dance
session_start();
require_once '../ims-blti/blti.php';
include "/home/bkinney/includes/lti_mysqli.php";
include "/www/canvas/apptracker.php";
/*error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);*/
header('Content-Type: text/html; charset=utf-8'); 
//echo mysql_real_escape_string("none"); //apparently, "none" is converted to empty string by this fn
// Initialize, set session, and do not redirect
$secret = array("table"=>"blti_keys","key_column"=>"oauth_consumer_key","secret_column"=>"secret","context_column"=>"context_id");
$context = new BLTI($secret, true, false);
if($context->valid){
	//echo($_REQUEST['ext_ims_lis_basic_outcome_url']);
	$info = $context->info;
	if(!empty($info['lis_outcome_service_url'])){
		$get_string="?usecase=grade";
		$logosrc = "ud_lti52.jpg";

	}else{
		$context->dump();
	}
	//echo $context->message;
	//die("<br>this assignment must be accessed from within an LTI provider, such as the Canvas LMS");
}else{
	echo "<p>Preview mode. Grade passback is not available</p>";
		$get_string="?usecase=preview";
		$logosrc="udlogo52.jpg";
}
?>

<html >
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>flashpower</title>
		<script type="text/javascript" src="swfobject.js"></script>
		<style type="text/css">
			body
			{
	background-color: #ffffff;
	margin-left: 0px;
	margin-top: 0px;
			}
		p {
	margin-left: 12px;
}
        </style>
	</head>
	<body><div><img src="spacer.jpg" height="52" width="1%"><img src="<?php echo $logosrc ?>" alt="University of Delaware"  hspace="0"><img src="spacer.jpg" height="52" width="20%"><img src="collegelogo700.gif" alt="UD College of Agriculture" width="388" height="52"></div>
<div id="flashpowerContainer"> 
		  <p>this page requires the Flash plugin, version 8. If you have a problem viewing the content on this page, please download the <a href="http://macromedia.com/go/getflashplayer">free plugin</a>.</p>
    </div>
		<script type="text/javascript">
			// <!--
			//var ignoredParams = {src: 1, bgcolor: 1};
			//var params = String('src="flashpower.swf" quality="high" bgcolor="#ffffff" ').split(" ");
			var swf = new SWFObject("flashpower3.swf<?php echo $get_string ?>", "flashpower", "100%", "100%", "8", "#ffffff");
			
			swf.addParam("salign", "lt");
			swf.addParam("align", "left");
			swf.addParam("xml","slideshow.xml");
			swf.addParam("glossary","glossary.xml");
			
			//for(var i = 0; i < params.length; i++)
			//{
				//var paramName = params[i].split("=")[0];
				//var paramValue = params[i].split("\"")[1];
				
			//	if(ignoredParams[paramName] != 1 && paramName != "")
				//{
				//	swf.addParam(paramName, paramValue);
				//}
			//}
			swf.write("flashpowerContainer");
			// -->
		</script>
	</body>
</html>
