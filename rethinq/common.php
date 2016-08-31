<?php
/*So, this seems to fail on beta, and i'm hoping it's just because when I send the url back to Canvas from the resource selection, it is checking the certificate. Testing on git instead*/
/*here's the plan:
record all correct answers in a session variable when the page is first loaded. Then use a jquery ajax call to compare a given answer to the stored answer and return feedback. DONE

remaining issue is how to prevent a reload of the assignment page, or a second team member also loading the page. I think we need a database for this.
manually check for page loads?

replace db check with check for assignment submissions from any team member.
 - submit for each checked student instead of updating db
 - check for submissions before loading quiz, same logic as db

turns out group assignments can't be of type External Tool. Yikes. So, two options
 - XX - this solution results in tab spawn. make a regular group assignment, but use the submission tabs.
 - make an External Tool assignment,  and manually set score for all group members
   -- in resource selection, add group set id as a custom DONE
 - ad hoc group creation during class with check boxes
 
 either way I will have to append group set id or */

/*error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);



exit();*/
//$asinstructor=true;

//print_r($_REQUEST);

		$denied=true;

if($_POST['members']){//members have been selected
	$members=implode(",",$_POST['members']);
	if (!isset($link))include "/home/bkinney/includes/lti_mysqli.php";
	$ids = explode("-",$context->info['lis_result_sourcedid']);
	$query = sprintf("select members from fatt where contextid='%s' and assignmentid='%s' and groupid='%s'",
	$context->info['custom_canvas_course_id'],
	$ids[2],
	//this should be random for test student, otherwise match gid
	$_POST['sgid']
	);
	$result = mysqli_query($link,$query);
	$row = mysqli_fetch_array($result);
	//echo $query;
	//echo $row['members'];
 $existing = explode(",",$row['members']);
 $cheaters = array_intersect($existing,$_POST['members']);
 $cheaters = array();
	if(count($cheaters)){

		echo "Warning: one or more of your team members has logged into this quiz previously. You will not be permitted to continue";
		echo implode(", ",$cheaters);
	}else{
		$query=sprintf("insert into fatt (contextid, assignmentid, groupid, members) values('%s','%s','%s','%s') on duplicate key update members=concat(members,',','%s')",
		$context->info['custom_canvas_course_id'],
		$ids[2],
		//$context->info['gid'],
		$_POST['sgid'],
		mysqli_real_escape_string($link,$members),
		mysqli_real_escape_string($link,$members)
		);
		//echo $query;
		if(mysqli_query($link,$query)){
			$_SESSION['members']=$_POST['members'];
			echo "Your team has been entered in the database. You may proceed.";
			$denied=false;
		}
	}
		//echo $queryi
		//if($_POST['t']=="holder")initQuiz();
	
	exit();
}

/* grab the quiz from the server, even before you need to write it- api call should only happen once, but I put in an overwrite option in case it turns out to be needed later. set $display=true if you are ready to write out the initial quiz
*/
function initQuiz($overwrite,$display){
	session_start();
	//echo "display= " . $display;
	if($overwrite || !array_key_exists('quiz',$_SESSION)){
		
		global $context, $api;
		if(isset($context->info['qid']) ){
			$cid=$context->info['custom_canvas_course_id'];
			$qid=$context->info['qid'];
			$quiz = get_mc_from_quiz($cid,$qid,$api);
			
			//$_SESSION['quiz'] = $quiz;//need this for total score, even before the quiz is written
		
			if($_SESSION['shuffle_answers']){
				//$quiz[]=$quiz[0];//weird workaround to get last question to display, rather than repeat second-to-last.
				foreach($quiz as &$question){
					//echo $question['answers'][0]['text'];
					shuffle($question['answers']);
					//echo $question['answers'][0]['text'];
				}
				unset($question);//correct fix, see http://stackoverflow.com/questions/3307409/php-pass-by-reference-in-foreach
				//array_pop($quiz);//second half of weird workaround
			}
		$_SESSION['quiz'] = $quiz;//need this for total score, even before the quiz is written
		}
		
			
		}else{
		$quiz=$_SESSION['quiz'];
		$one_at = $_SESSION['one_question_at_a_time'];
		if($one_at)$current_question_num = array_key_exists("current_question_num",$_SESSION) ? $_SESSION['current_question_num'] : 0;
		if($display){//write the quiz
		//echo "display= " . $display;
		if($one_at){
			$qnum=$current_question_num + 1;
			$quiz_shell = array();
			$quiz_shell[] = $quiz[$current_question_num];
			//$_SESSION['current_question_num']=$qnum;
		}else{
			$current_question_num="count";
			$qnum=1;
			$quiz_shell=$quiz;
			
		}
			//echo $_SESSION['shuffle_answers'];
			foreach($quiz_shell as $question){
				$question['points_earned']=0;
				$question['errors']="";
				echo '<div class="question" data-index="' . $current_question_num . '" ><div class="header">
			  <span class="name question_name" tabindex="0" role="heading">' . $question['question_name'] . '</span>
			  <span class="question_points_holder" style="">
				<span class="points question_points">' . $question['points_possible'] . '</span> pts
			</span>
				<span class="ui-helper-hidden-accessible">' . urlencode($question['question_text']) . '</span>
			</div><div class="text">';
				
				$prompt = $question['question_text'];
				$maxscore = $question['points_possible'];
				$answers = $question['answers'];//array
				
				$question['minscore']=$maxscore*Math.pow(.5,count($answers)-2);
				
				
				//print_r($answers) . "\r\r";
				echo '<div  class="question_text user_content enhanced">
				  <p>' . $prompt;
				echo '</p></div>';//option list
				echo '<div class="answers">';
				foreach($answers as $answer){
	/*				if(!$answer['html']){
						echo '<li class="unanswered">' . $answer['text'];//only put the onclick here
					}else{
						echo '<li class="unanswered">' . $answer['html'];
						$answer['text']=$answer['html'];
					}*/
				echo '<li class="unanswered">' . $answer['text'] . $answer['html'];//only put the onclick here
				
					echo '</li>';	}
				echo '</ol>';//close option list
				echo '</li></div></div></div>';//close question
			}//end foreach
			echo '<p align="right">';
			if(!$_SESSION['cant_go_back'] && $qnum > 1)echo '<button onclick="nextQuestion(-1)" class="udats-score" >Previous</button>';
			
			if($one_at && $current_question_num < count($quiz)-1)	echo '<button onclick="nextQuestion(1)" class="udats-score" >Next</button>';
			
			if(!$one_at || $current_question_num == count($quiz)-1){
				echo '<button onclick="submitScore()" class="udats-score" >Submit Score</button>';
			}
			echo '</p>';
		}
	}
}
/**
	here we show existing group members if any, and allow modifications. We are NOT pushing modified groups back to Canvas, so selections made here only create temporary groups for grading purposes.
**/
function displayMembers($api,$context){//show existing group members, plus unassigned
  //get all groups logged in student is in
/*  echo '<pre>';
  print_r($context->info);
  echo '</pre>';*/
  echo '<form id="mygroup" name="mygroup">';
  //enter the logged-in user with checked and disabled
	echo '<p><input type="checkbox" checked disabled
						id="' . $context->info['custom_canvas_user_id'] . '" >';
						echo $context->getUserName();
						echo '</p>';
	//now create $mygroup and others
  if($context->info['gid']=='none'){//form groups on the fly
  	$uri = '/api/v1/courses/'. $context->info['custom_canvas_course_id'].'/users';
  	$mygroup=false;
  	$others = $api->get_canvas($uri,true);
	 

  }else if($context->getUserName() == "Test Student"){
	  $mygroup=false;
	  $uri='/api/v1/group_categories/'.$context->info['gid'].'/users?unassigned=true';
    $others = $api->get_canvas($uri);
	
  }else if($context->info['gid'] != "none"){//we had a gid
    
    //okay, here is where we have to identify the group he's in
    
    //first, list all groups in the group category
    $uri = '/api/v1/group_categories/'.$context->info['gid'].'/groups';
	
    $cgroups = $api->get_canvas($uri,true);
    foreach($cgroups as $group){
      //test uri /api/v1/groups/96942/users?search_term=1294841
      	$uri='/api/v1/groups/'.$group['id'].'/users?search_term=' . $context->info['custom_canvas_user_id'];
  	    $me = $api->get_canvas($uri,false);
		
  	    //if($me[0]['id']==$context->info['custom_canvas_user_id']){
  	    if(count($me)){//need a better way to see whether or not an api get has returned data
		
		
  	      $sgid=$group['id'];
  	      $uri = '/api/v1/groups/'.$sgid . '/users';
  	      $mygroup = $api->get_canvas($uri,true);
		  
  	      break;
  	    }//end if
  	 }//end foreach cgroup, now get others
  	 $uri='/api/v1/group_categories/'.$context->info['gid'].'/users?unassigned=true';
    $others = $api->get_canvas($uri);
	//print_r($others);
  }//end if gid
  //okay, now we have a group, list them
  if($mygroup){
    	 echo '<p>Group Members</p><p>If any members of your group are absent, please uncheck their names.</p>';
  	foreach($mygroup as $member){//change the logic here. Always write logged-in user first
			if($member['id']==$context->info['custom_canvas_user_id']) continue;
			echo '<p><input type="checkbox" checked data-new="true" id="' . $member['id'] .'" >' ;
			echo $member['name'];
			echo '</p>';
  					
  	}
  }
    if(count($others[0])){
    	echo '<p>Unassigned Members</p><p>Select any new members your group may have.</p>';
    	//but warn if this is the test student
    	if($context->getUserName() == "Test Student"){
				//$sgid = rand(1000,9999);
				
			echo '<p class="ui-state-error">Caution: Do not select real students when testing a rethinq quiz. Any additonal students you select will receive a grade for this assignment.</p>';
			}
			
  	foreach($others as $member){//change the logic here. Always write logged-in user first
			//if($member['id']==$context->info['custom_canvas_user_id']) continue;
			
			echo '<p><input type="checkbox"  data-new="true" id="' . $member['id'] . '" >' ;
			echo $member['name'];
			echo '</p>';
  					
  	}
  }
  

	echo '<input type="button" onclick="startQuiz('.$sgid.')" value="Begin"/></form>';
	
}//end fn displayMembers
/*function displayMembers($api,$context){//show existing group members, plus unassigned
//get all groups logged in student is in
//echo '<p>' . $context->info['gid'];
echo '<form id="mygroup" name="mygroup">';
		
	$uri='/api/v1/users/self/groups?as_user_id=' . $context->info['custom_canvas_user_id'];

	$usergroups = $api->get_canvas($uri,false);
	//print_r($usergroups);
	if(count($usergroups)){
		foreach($usergroups as $group){
			if($context->info['gid'] == $group['group_category_id']){
				//get other members
				$sgid = $group['id'];
				$uri='/api/v1/groups/'.$group['id'].'/users';
				$mygroup=$api->get_canvas($uri,true);
				$mygroup=$api->get_canvas($uri,true);
				if(isset($mygroup[0]['id'])){
					 echo '<p>Group Members</p><p>If any members of your group are absent, please uncheck their names.</p>';
				
					foreach($mygroup as $member){
						$self = $member['id']==$context->info['custom_canvas_user_id'] ? " disabled " : "";
						echo '<p><input type="checkbox" checked data-new="true" id="' . $member['id'] . '"' . $self . ' >';
						echo $member['name'];
						echo '</p>';
						
					}
				}
				
				///api/v1/group_categories/:group_category_id/users
				
				
			}
		}//end foreach usergroup
		if(empty($sgid)){//no user groups that match the group category
			$sgid = $context->info['gid'];//??
			echo '<p><input type="checkbox" checked disabled
						id="' . $context->info['custom_canvas_user_id'] . '" >';
						echo $context->getUserName();
						echo '</p>';
			
		}
		
		//echo '</p>';
	}else{//logged in user is not in any groups, add his checkbox, checked and disabled
		echo '<p><input type="checkbox" checked disabled
						id="' . $context->info['custom_canvas_user_id'] . '" >';
						echo $context->getUserName();
						echo '</p>';
		
	}//end if count
	//get unassigned members
	$uri='/api/v1/group_categories/'.$context->info['gid'].'/users?unassigned=true';
	//echo $uri;
	//return;
			$mygroup=$api->get_canvas($uri,true);
			if(isset($mygroup[0]['id'])){
					if($context->getUserName() == "Test Student"){
				$sgid = rand(1000,9999);
				
			echo '<p class="ui-state-error">Caution: Do not select real students when testing a rethinq quiz. Any additonal students you select will receive a grade for this assignment.</p>';
			}else{
				$sgid=$context->info['gid'];//storing all real students in a single row
			}
				 echo '<p>Unassigned Members</p><p>Select any new members your group may have.</p>';
				 
				 
				foreach($mygroup as $member){
					if($member['id']!=$context->info['custom_canvas_user_id']){
						echo '<p><input type="checkbox"
						id="' . $member['id'] . '" >';
						echo $member['name'];
						echo '</p>';
					}
					
				}//end foreach unassigned
			
			}
		
	echo '<input type="button" onclick="startQuiz('.$sgid.')" value="Begin"/></form>';
	
}//end fn displayMembers*/
/**
	selected option is sent to this page for server-side evaluation. We are AJAXing the updated <li> back, and updating the quiz session array
**/
function evaluateAnswer(){
	$qnum=$_REQUEST['qnum'];
	$anum=$_REQUEST['anum'];
	$question = $_SESSION['quiz'][$qnum];
	
	$answer = $question['answers'][$anum];
	
	$answerstatus = $answer['weight']*1==100 ? "udats-correct" : "udats-incorrect";
	if($answerstatus=="udats-incorrect")$_SESSION['quiz'][$qnum]['points_possible']*=.5;
	if($_SESSION['quiz'][$qnum]['points_possible']<$_SESSION['quiz'][$qnum]['minscore'])$_SESSION['quiz'][$qnum]['points_possible']=0;
	//copy possible into earned to aid in total score update
	if($answerstatus =="udats-correct"){
		$_SESSION['quiz'][$qnum]['points_earned']=$_SESSION['quiz'][$qnum]['points_possible'];
	}else{//collect all incorrect responses
		$_SESSION['quiz'][$qnum]['errors'].= strip_tags($answer['text']) . " | ";
	}
	
	echo '<li class="'.$answerstatus . '">' . $answer['text'] . $answer['html'];
	//print_r($answer);
			if(!empty($answer['comments'])){
				echo '<ol><li>' . $answer['comments'] . '</li></ol>';
			}else if(!empty($answer['comments_html'])){
				echo '<ol><li>' . $answer['comments_html'] . '</li></ol>';
			}
			echo '</li>';
}
/**
	using Canvas multiple grade submissions endpoint to award points to the entire group - currently this is called every time a question is answered correctly.
**/
function postGrades($p,$c){
//global $api;
global $context, $api;


	$endpoint='/api/v1/courses/' . $context->info['custom_canvas_course_id'] .'/assignments/' . $context->info['custom_canvas_assignment_id'] . '/submissions/update_grades?';
	
	
	if(!empty($_SESSION['members'])){
		//echo count($_SESSION['members']);
			foreach($_SESSION['members'] as $member){
			$submission = '	/api/v1/courses/' . $context->info['custom_canvas_course_id'] .'/assignments/' . $context->info['custom_canvas_assignment_id'] . '/submissions';
				
		//echo "Student,ID,SIS User ID,SIS Login ID,Section,peer" . $projectid . "\r\n";
		//echo ",,,,," . $_GET['maxscore'] . "\r\n";
		
			//$args['grade_data'][$member]['posted_grade']=$p;
			//$args['grade_data'][$member]['text_comment']=$c;
			$endpoint .= '&grade_data['.$member.'][posted_grade]='.$p;
			$endpoint .= '&grade_data['.$member.'][text_comment]='.urlencode($c);
			
		
			}
	}else{
		echo "no members";
	}
	//echo $endpoint;
	/*$tendpoint ="/api/v1/users/self" . $owner;
	$me = $api->get_canvas($tendpoint);
	print_r($me);*/
	//$endpoint ="https://udel.instructure.com/api/v1/courses/1277963/assignments/4898912/submissions/5206541?submission[posted_grade]=8&comment[text_comment]=mycomment";
	//$result=$api->post_canvas($endpoint,"PUT");
		$result = $api->post_canvas($endpoint,"POST");
		//print_r($result);//put_canvas("/api/v1/courses/301991/assignments/4612095/submissions/1273346?submission[posted_grade]=8&comment[text_comment]=a: 5 b: 3 :");
			if($result){
		
		// echo $endpoint;
}else{//close if result
// echo "failure";
}
}
/**
	$init = true just for setting up the total score div in the quiz header
	Loop through the updated quiz session array to add up points and compile comments
	this is where we can turn down the frequency of grade posting if desired
**/
function updateTotalScore($init){
	session_start();
	if(!array_key_exists('quiz',$_SESSION))initQuiz(false,false);//creates the quiz in session if missing, does not display it
	$score=$maxscore=0;
	$comment="Errors: ";
	$qnum=0;
	foreach($_SESSION['quiz'] as $question){
		$score += $question['points_earned']*1;
		$maxscore += $question['points_possible']*1;
		$qnum++;
		if($question['errors'] !="") $comment .= "[" . $qnum . "] " . $question['errors'];
	}
	if($init){
		$_SESSION['maxpoints']=$maxscore;
		echo '0 out of ' . $maxscore;
	}else{
		$_SESSION['score']=$score;
		echo 'Score: ' . $score . ' out of ' . $_SESSION['maxpoints'] ;
	}
	$points = round($score,2);
	if($_REQUEST['final'])postGrades($points,$comment);
	
}
/**
	call the appropriate function if we see that this is a request for server-side evaluation of a student response
	evaluateAnswer if this is the original post, totalscore gets sent in a callback from js if the answer send was correct
**/
if(array_key_exists('anum',$_REQUEST) && array_key_exists('qnum',$_REQUEST)){
	evaluateAnswer();
	exit();
}else if(array_key_exists('totalscore',$_REQUEST)){
	updateTotalScore(false);
	exit();
}
/**
	called by initQuiz - probably should just be there inline, but that's a long story
**/
	function get_mc_from_quiz($courseid,$quizid,$api){
		$uri = '/api/v1/courses/' . $courseid . '/quizzes/' . $quizid;
		$quiz = $api->get_canvas($uri,false);
		$instructions = $_SESSION['instructions']=$quiz['description'];
		$_SESSION['shuffle_answers'] = $quiz['shuffle_answers'];
		$_SESSION['one_question_at_a_time'] = $quiz['one_question_at_a_time'];
		$_SESSION['cant_go_back'] = $quiz['cant_go_back'];
		$_SESSION['current_question_num']=0;
		$uri = '/api/v1/courses/' . $courseid . '/quizzes/' . $quizid . '/questions';
		//echo $uri;
		global $domain;
		$questions = $api->get_canvas($uri,true);
		$json = json_encode($questions);
		//fix broken site-relative links to images
		//echo $json;
		$fixed = str_replace('src=\"\/','src=\"\/\/' . $domain . '/',$json);
		//echo $fixed;
		$questions = json_decode($fixed,true);
		$mc = array();
		foreach($questions as $question){
			if($question['question_type']=="multiple_choice_question") $mc[]=$question;
		}
		//print_r($mc);
		return $mc;
	}
/**
	post from the startQuiz javascript fn - this write the actual quiz into the quiz-holder div
**/
if($_REQUEST['t']=="holder"){

	if(array_key_exists('inc',$_POST)){
		$_SESSION['current_question_num'] = $_SESSION['current_question_num'] + $_POST['inc'];
	}else{
		$_SESSION['current_question_num']=0;
	}
	//echo $_SESSION['current_question_num'];
	initQuiz(false,true);//overwrite, display
	
	exit();
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
 <!-- <script src="ifat.js"></script>
   <script src="listtricks-quiz.js"></script>
  <script src="https://www.udel.edu/it/canvas/branding-inc/beta/udats-plugins.js"></script>
    
<link href="/canvas/mashup/mashup.css" rel="stylesheet" type="text/css">
<link href="https://www.udel.edu/it/canvas/branding-inc/beta/udats-plugins.css" media="all" rel="stylesheet" type="text/css">
<link href="https://udel.instructure.com/assets/quizzes_legacy_high_contrast.css" rel="stylesheet" type="text/css" media="all">-->
<link href="https://apps.ats.udel.edu/canvas/rethinq/quizzes.css" rel="stylesheet" type="text/css" media="all">
<style>
.logout{
	background-color: rgb(252, 229, 140);
padding: 2px;
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
<style>
.ui-state-error{  border: 1px solid #cd0a0a;
  background: #fef1ec;
  color: #cd0a0a;
    padding: .7em;
  border-radius: 5px;
  width: 400px;
  }
 #footer {
background-color: #ffe;
text-align: center;
border-top: thick solid #039;
padding: 0;
width: 100%;
position:fixed;
bottom:0px;

}
#unload{

  top: 0;
  width: 99%;
  text-align: right;
  }
#ajax{display:none}
.message{border:solid thin red; padding:6px;font-family:Arial, Verdana, Geneva, sans-serif;}
.q-container{ border-bottom:double;}
.ui-helper-hidden-accessible, .flag_question{ display:none }
.udats-correct ol, .udats-incorrect ol{padding:0;list-style:none;margin:4px 15px;}
.udats-correct ol li, .udats-incorrect ol li{ padding:5px; margin-bottom:2px}
.answers li.unanswered {
	
	list-style:none;
	cursor:pointer;
}
.answers li.answered {
	
	list-style:none;
	
}
.answers li{
	list-style-position:inside;
	border-top: 1px solid #ddd;
	padding:7px;
}
.udats-correct{
	
	list-style-image:url(https://www.udel.edu/it/canvas/branding-inc/beta/check.gif);
	color:#063;
}

.udats-incorrect{
	
		list-style-image:url(https://www.udel.edu/it/canvas/branding-inc/beta/x.gif);
	color:#f00;
}
.udats-correct ol li{
	border-top: 1px solid #ddd;
	color:black;
	border:solid thin #063;
}
.udats-incorrect ol li{color:black;border:solid thin #F00;}
.udats-score{
  text-align: center;
  margin-right: 30px;
  margin-left: auto;
  background-color: #f5f5f5;
  padding: 10px;
  padding: 10px 0;
  border: blue solid 1;
  border: 1px solid #aaa;

  width: 150px;
  text-wrap:none;
  overflow:visible;

}
button.udats-score{
	position:relative;
	bottom:20px;
}
.twocol{
 float:left;
 margin: 0;
 min-width:400px;
 width:400px;
 overflow:scroll;
}
#questions.twocol #question_holder .question{
	min-width:370px;
	width:100%;
	margin:40px 0;
	}
.hidden{display:none}
.col2{
 display:block;
 margin-left:470px;
}
</style>
<script>
/**
	redirect to global logout page. delete session vars and cookies


function endSession(){
	$("#unload").load("../logout.php",function(){
		//$(window).unbind('beforeunload');
		$("body").html("Your session has ended. Refresh your browser to re-load your group members");
		});
}**/
/*function sendScore(obj){

var post = new Object();
	for(var x in obj){
		post[x]=obj[x];
		
	}
	
		$("#success").load("reportScore.php",post,function(response){
		$("body").html("<p>Your grade has been submitted</p>");
	});//.hide();;
	
}*/
/**set onclick event for answer options. Once a question is answered correctly, unchosen options adopt the 'answered' class instead of unanswered, and lose their click behavior.**/
function tileInstructions(){
	var qheight = $("#footer").position().top-50;
	$("#questions").toggleClass("twocol").height(qheight);
	$("#wide").toggleClass("col2").toggleClass("hidden");
}
function delegateClick(){
		$(".answers").on("click","li.unanswered",function(obj){
		if($(obj.target).attr("href"))return true;

		var anum=$(this).index();
		var question = $(this).parents("div.question");
		
		//var qnum = question.index();
		var qnum = question.data("index")
		if(qnum=="count"){//all questions are displayed
			qnum = question.index();
			//alert(qnum);
		}
		var ans = question.find("li:not(li li)").get(anum);
		$(ans).addClass("removeme");
		$("#ajax").load("index.php","anum=" + anum + "&qnum=" + qnum,function(response){
			
			$("li.removeme").after(response);
			$("li.removeme").removeClass("removeme").remove();
			//$(obj.target).remove();
			var status = $("#ajax li").attr("class");
			var ph =  question.find("span.question_points");
			var value = ph.text();
		
		if(status=="udats-incorrect"){
			
			var pp = question.find("li.unanswered").length<=1 ? 0 : value*.5;
			ph.text(pp);
			//alert("points remaining " + pp + question.find("li.unanswered").length);
		}else{
			question.find("li.unanswered").removeClass("unanswered").addClass("answered");
		/*	var mydiv = $(".udats-quiz");
			var score = mydiv.data("score") + Number(value);
			var qp = mydiv.data("quizPoints")
			mydiv.data("score",score);
			mydiv.children(".udats-score").text("Score: "+ score + " out of " + qp);*/
			$("#running").load("index.php","totalscore=1");
		}
		});
		
		//var outcome = $(this).parents("li").attr("title");
		//alert("add " + points + " to " + outcome);
		//target_outcome = $("#" + outcome);
		
		
		
		
		//$("#next_btn").show();
		
	});//delegate
}
/** finalScore
 only update score on button push
**/
function submitScore(){
	//alert("submitting");
	$("#success").text("");
	$("#question_holder").load("index.php","totalscore=1&final=1");
}
/**
	javascript. called when a student clicks the begin btn. POSTs member to this page to be checked against db entries, if OK, load quiz into target
**/
var questionBackups = new Array();
var qindex=0;
function nextQuestion(inc){
	questionBackups[qindex]=$("#question_holder").html();
	qindex+=inc;
	
	if(inc == 1 && questionBackups[qindex]==undefined ){//new question
		
		var p= new Object();
		p.inc = inc;
		p.t="holder";
					
		$("#question_holder").load("index.php",p,delegateClick);
	}else{//redisplay
		$("#question_holder").html(questionBackups[qindex]);
		delegateClick();
	}
}
function startQuiz(sgid){
	var ready=confirm("Double check to be sure you have selected your team correctly. Once you begin this quiz, you will not be able to change your group members or restart the quiz in any way. Your progress will be updated  each time you select an answer option.");
	if(ready){
	
		var members = new Array();
		$("#mygroup p input:checked").each(function(){
			
			members.push($(this).attr('id'));
		});
		console.log(members.toString());
			obj = new Object();
			$("#questions").attr("data-members",members.toString());
			//if(members.length==0)members.push(Math.random());//get rid of this
			if(!sgid)sgid="nogroups";
			obj.members=members;
			obj.sgid = sgid;
			$("#success").load("index.php",obj,function(response){
				if(response.indexOf("Warning")==-1){
					$("#mygroup").hide();
					var p= new Object();
					p.t="holder";
					
					$("#question_holder").load("index.php",p,delegateClick);
					$("#questions").show();
					
				}else{
					//$("#questions").detach();
				}
			});
		
	}else{
		
	}
	
}
<?php if($context->isInstructor()): ?>
/**
 php this is only relevant when the instructor is in edit assignment mode. returns quiz and group set ids to the resource selection endpoint
**/
function return_url_to_canvas(){
	var qid=$("#quiz_id option:selected").val();
	var gid = $("#group_id option:selected").val();
	

	
	var ready = "<?php echo $_SESSION['toolserver'] . $_SESSION['canvashtml'] ?>rethinq/index.php?qid="+qid + "&gid=" + gid;
	//	alert(ready);
		$("#url").val(ready);
		$("#return_url").submit();
	
	
}

<?php else: ?>

<?php endif ?>


$(document).ready(function(e) {
	delegateClick();//not sure why this is necessary, but onclick was absent when quiz is loaded into a div after ready has already been hit. strange.


/*
 calculate value of each question and total quiz. moved server side
	var maxpoints=0;
	$(".udats-quiz ol li ol").not("ol li ol li ol").each(function(index, element) {
		var mypoints = $(this).children("li").length-1;
		//$(this).parents("div").data("quizPoints",$(this).children("li").length);
		$(this).before(" [ points possible: <span class='value'>" + mypoints + "</span> ] ")
		$(this).children('li:first').attr("data-points",1).siblings('li').attr("data-points",0);
		maxpoints += mypoints;
	   //$(this).children('li:first').data("points",1);
	});//each
	$("div.udats-quiz").each(function(index, element) {
		//$(this).data("score",0);
		
		//$(this).data("quizPoints",maxpoints);
	
		$('<div id="running" class="udats-score">Score:</div>').prependTo($(this));
		//this.children(".udats-score").text("Score: "+ score + " out of " + qp);
	 });*/


//$("#quiz").listtricksQuiz({sendScore:sendScore});//,addClass:"left"

});

</script>

</head>

<body>
<div id="success"></div>



<?php

$editing=$context->isInstructor() && !isset($context->info['gid']);//resource selection
if(!$editing){//display a logout option- this is a failsafe for situations where sessions are clashing or groups need to be switched around
 //echo '<div id="unload"><p class="logout"><img src="https://apps.ats.udel.edu/UDcircle20.png" align="left" border="0"/> <button onclick="endSession()" title="Log out before testing in student view, masquerading, or switching to another UD LTI tool, such as Post&apos;Em or Peer Evaluation" >Log out of this quiz session</button></p></div>';
 echo $header;
}
//student is logged in, show him his group members + unassigned
if($context->isInstructor()==false && !$editing)displayMembers($api,$context);
if($context->isInstructor() && !$editing){//just hide the quiz area, which is empty in any case
	$questionstyle = '';
}else{
	$questionstyle ='style="display:none"';
}
if($editing){//resource selection
	$uri = "/api/v1/courses/". $context->info['custom_canvas_course_id'] ."/quizzes";
	$rs_url = $_REQUEST['launch_presentation_return_url'];
	$assignments = $api->get_canvas($uri);
	//print_r($assignments[0]);
	$foundone=false;
	echo '<form action="' .$rs_url . '" id="return_url" method="GET">
	<input type="hidden" value="lti_launch_url" name="return_type">
	<input type="hidden" name="url" id="url"></form>
	<form>
	
	<p>Select a quiz: <select name="quiz_id" id="quiz_id">';
	
	foreach($assignments as $assignment){
		
		//print_r($assignment['submission_types']);
		if(isset($assignment['id'])){
			$foundone=true;
			echo '<option value="' .$assignment['id'] . '"';
			//if($assignment['id']==$context->info['qid'])echo ' selected="selected"';
			echo '>' . $assignment['title'] . '</option>';
		}
	}
	if(!$foundone){ echo "<p>No quizzes found. Please create a multiple choice quiz and then return to this tool.</p></form>";//close the form with no other inputs
	exit();
	}else{
	}
	echo '</select>';
	//repeat the logic for group sets
	$uri = "/api/v1/courses/". $context->info['custom_canvas_course_id'] ."/group_categories";
	
	$assignments = $api->get_canvas($uri);
	
	echo '</p><p>Select a group set: <select name="group_id" id="group_id"><option value="none">No Groups</option>';
	foreach($assignments as $assignment){
		//print_r($assignment['submission_types']);
		if(isset($assignment['id'])){
			
			echo '<option value="' .$assignment['id'] . '"';
			//if($assignment['id']==$context->info['gid'])echo ' selected="selected"';
			echo '>' . $assignment['name'] . '</option>';
		}
	}
	echo '</select> <input type="button" value="Go" onclick="return_url_to_canvas()"> </form>';
	;
}
?>
<div id="ajax"></div>

<div id="questions" class="assessing" <?php echo $questionstyle ?> ><button onclick="tileInstructions()">toggle instructions</button>
<div id="running" class="udats-score">Score: <?php updateTotalScore(true) ?></div>
<div class="question_holder" id="question_holder">
<?php

if($context->isInstructor()){
	
	initQuiz(false,true);
}
?>
</div>
</div>
<div id="wide" class="hidden"><?php echo $_SESSION['instructions'] ?></div>
<div id="footer"><a href="https://sites.google.com/a/udel.edu/fatt/bug-reports" target="bugreports">Bug Reports</a> | <a href="https://docs.google.com/a/udel.edu/forms/d/1iujOdz3jR9saeALKQn_skgLKhWoVkCAjZ3Fq203st8c/viewform" target="featurerequests">Feature Requests</a> | <a href="/canvas/rethinq/help.html" target="help">Help</a> </div>
</body>
</html>