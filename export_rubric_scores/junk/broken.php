<?php
//I'm experimenting with this, putting it in as a user menu item is shared.xml- hopefully this creates a session token which will bypass any db search

if(array_key_exists("custom_token",$_POST)|| array_key_exists('temptoken',$_GET) || array_key_exists('logout',$_GET)){
	if($_POST['custom_token']=="temp"){
		session_start();
		$domain = $_REQUEST['custom_domain_url'];
		setcookie('domain',$domain,0,'/');


		//$_SESSION['temptoken']='25~meMpbK6vs5e1OmYkdvXlDP8E8uZftOwBCqJUKCrbQQ9F6Iemnmxz9n7QLGYQ08JJ';//this will never match a true token
		//print_r($_SESSION);
		$temptoken=true;
		//$_SESSION['temptoken']=true;
		//echo $_SESSION['token'];\
		
	}
	include "/www/canvas/temp_token.php";
}else{
	include "/www/canvas/canvas_dance_include_shared.php";
}
	



if(!$context->valid ){
	die("<p>This page must be loaded from within Canvas. For help using this tool contact Becky Kinney or her replacement at ATS.</p>");
}
if(array_key_exists("courseid",$_REQUEST)){
error_reporting(E_ALL | E_PARSE);
//ini_set('display_errors', 1);

header("content-disposition:attachment;filename=rubric_scores_" . $_REQUEST['asstid'] . ".csv");
header("content-type:text/csv");
	$rows = array();
	$firstrow="name,sisid,asstid,attempt,late,submit date,";
	//$api = new CanvasAPI($token,'udel.instructure.com');
	
	$submissions = $api->get_canvas('/api/v1/courses/'.$_REQUEST['courseid'].'/assignments/'.$_REQUEST['asstid'].'/submissions?include[]=rubric_assessment&include[]=user');
/*$user = $submissions[0]['user'];
foreach ($user as $key => $value){
	echo $key . ':' . $value;
}*/
	$sample = $submissions[0]['rubric_assessment'];
	$n=0;
	foreach ($sample as $value){
		$firstrow .= 'r'.++$n . ",";
	}
	foreach($submissions as $key => $submission){
		if($submission['user']['sortable_name']=="Student, Test")continue;
		$column = array();
		$column[]='"' . $submission['user']['sortable_name'] . '"';
		$column[]=$submission['user']['login_id'];
		$column[]=$submission['assignment_id'];
		$column[]=$submission['attempt'];
		$column[]=$submission['late'];
		$column[]=$submission['submitted_at'];
		
		$rubric = $submission['rubric_assessment'];
		
		foreach($rubric as $r){
			
			
				$column[]=$r['points'];
			
		}
		
		//echo implode(',',$column) . "\r";
		$rows[]=implode(',',$column);
	}
	function byname($a,$b){
		
		return strcmp($a[0],$b[0]);
	}
	uasort($rows,"byname");
	array_unshift($rows,$firstrow);
	echo implode("\r",$rows);
	exit();
}else{
	
}
?>
<html>
<head>
<script>
function parseurl(url){
	//alert(url);
	//var url=document.getElementById('url').value;
	var arr = url.split("/");
	var courseid = arr[4];
	var asstid = arr[6];
	document.getElementById('one').value = courseid;
	document.getElementById('two').value = asstid;
}


</script>
<style>
body{max-width:650px}
blockquote{
	  background-color: cornsilk;
  padding: 20px;
  margin: 5px;
}
</style>
</head>
<body>
 
<?php if($context->info['custom_token']):?>
<blockquote>


<div id="unload"> <a href="index.php?logout=1" ><button  title="Failure to delete will result in a clutter of unusable tokens in your user settings." >Delete my token</button></a></div>


<p>It is highly recommended that you delete the access token you have just authorized after completing all desired downloads.</p>
<p>It is possible to exit and then re-enter this tool to obtain assignment urls without issuing a new token, as long as you do not delete your token or close your browser, however, you may find it easier to browse to assignments in a <a href="//<?php echo $domain ?>" target="browse">second browser window or tab</a>.</p>

<?php else : ?>


<p>This app is only available to Canvas admins, and it utilizes an admin authorization token. Output will contain all rubric scores submitted by instructors for the assignment chosen. It is your responsibility to ensure that the instructor to whom you send the output has instructor access to the course. Please do not send data directly to TAs.</p>
<?php endif ?>
</blockquote>
<p>Enter an assignment url and click <strong>extract</strong>. Check course and assignment ids and correct if necessary, and then click <strong>Submit</strong> to download your data. Repeat as necessary. </p>

<form action="index.php" >
  <p>Assignment URL:
    <input type="text" size="70" id="url" name="parseme" > <input type="button" value="extract" onClick="parseurl(parseme.value)">
    
  </p>
  <p>Course ID:
    <input type="text" name="courseid" id="one" />
  </p>
  <p>Assignment ID:
    <input type="text" name="asstid" id="two" />
  </p>
  <p><input type="submit"></p>
</form>

</body>
</html>