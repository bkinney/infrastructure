<?php

error_reporting(E_ALL & ~E_NOTICE);
session_start();

//trying to use this same file for all scenarios where a token is required

//both $context and $api are created in canvas_dance_include_shared file
if(!$context->valid ){
	
	die("<p>This page must be loaded from within Canvas. For help using this tool contact Becky Kinney or her replacement at ATS.</p>");
}

if(array_key_exists("courseid",$_REQUEST)){//form submission from within the tool. this is where the real functionality of this tool begins

function sortByName($a, $b) {
   				 return $b['user']['sortable_name']>$a['user']['sortable_name'];
			}

			

	$submissions = $api->get_canvas('/api/v1/courses/'.$_REQUEST['courseid'].'/assignments/'.$_REQUEST['asstid'].'/submissions?include[]=rubric_assessment&include[]=user');
	myecho('/api/v1/courses/'.$_REQUEST['courseid'].'/assignments/'.$_REQUEST['asstid'].'/submissions?include[]=rubric_assessment&include[]=user',false);
	//myecho(print_r($submissions));
	if($submissions['errors']){
		echo "<pre><blockquote>";
		foreach($submissions['errors'] as $error){
			echo $error['message'] . "\r";
		}
		echo "</blockquote></pre>";
		echo 'There seems to be a problem with this request. Perhaps you are not authorized to view grades for this assignment.';
	}else if($submissions[0]['rubric_assessment']){
		//sort alphabetically by student name
		uasort($submissions,"sortByName");
	
		//set header for csv export
		header("content-disposition:attachment;filename=rubric_scores_" . $_REQUEST['asstid'] . ".csv");
		header("content-type:text/csv");
		$rows = array();
		$firstrow="name,sisid,asstid,attempt,late,submit date,";
		//$api = new CanvasAPI($token,'udel.instructure.com');
	
	

/*$user = $submissions[0]['user'];
foreach ($user as $key => $value){
	echo $key . ':' . $value;
}*/
	$sample = $submissions[0]['rubric_assessment'];
	$n=0;
	//create column headers for rubric values. I don't have a way to get the real names from this endpoint, plus theiy will tend to be long, so I'm just calling them "r#"
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
	
	ob_end_clean();
	echo implode("\r",$rows);
	exit();
	}else{//no rubric
		echo "<p>This assignment does not appear to be associated with a rubric.</p>";
	}
}else{
	
}
?>
<html>
<head>

<style>
p {
    padding-left: 1em;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 90%;
}
body{max-width:650px}
blockquote{
	  background-color: cornsilk;
  padding: 20px;
  margin: 5px;
}
</style>
<style>
.logout{
	background-color: rgb(252, 229, 140);
padding: 2;
text-align: right;
font-family:Arial, Helvetica, sans-serif;
font-size:70%;
color:#559;name=
border-bottom: double;
margin-bottom: 5px;
 }
 .logout button{
	margin:0 1px 0 5px;

 }
</style>
</head>
<body>
  <?php echo $header ?>
<!--conditional html, depending on whether this is the admin or instructor tool-->
<?php if($tempenabled):?>

<!-- removed blockquote content -->

<?php else : ?>
<blockquote>

<p>This app is only available to Canvas admins, and it utilizes an admin authorization token. Output will contain all rubric scores submitted by instructors for the assignment chosen. It is your responsibility to ensure that the instructor to whom you send the output has instructor access to the course. Please do not send data directly to TAs.</p>
<?php endif ?>
</blockquote>
<p>Select an assignment then click <strong>Submit</strong> to download your data. Repeat as necessary. </p>
<p>If you do not see the assignment you want, check to be sure that it employs a rubric.</p>
<!--By now we shuld have a token stored as a session variable, so just post back to index, see line 57-->
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" >
  <p>
    <input type="hidden" name="courseid" id="one" value="<?php echo $context->info['custom_canvas_course_id'] ?>"/>
  </p>
  <p>Assignment ID:
    <select  name="asstid" id="two" >
    <?php
	function sortByDate($a,$b){
	//echo $a['due_at']. ", " . $b['due_at'] ;
			
			return strcmp($a['due_at'],$b['due_at']);
		}
	$uri = "/api/v1/courses/". $context->info['custom_canvas_course_id'] ."/assignments";
	
	$assignments = $api->get_canvas($uri);
	//$assignments=array_reverse($assignmentsR);//hard to sort this, because of empty
	uasort($assignments,"sortByDate");
	foreach($assignments as $assignment){
		
		//if(in_array('rubric',$assignment)){
		if(!empty($assignment['rubric'])){
			$foundone=true;
			echo '<option value="' .$assignment['id'] . '">' . $assignment['name'] . '</option>';
		}
	}
	?>
    </select>
  </p>
  <p><input type="submit"></p>
</form>

</body>
</html>