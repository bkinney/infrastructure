// JavaScript Document<script language="JavaScript" type="text/javascript">
var p_array = new Array();
var done_array = new Array(true)
var done_array2 = new Array();
var qscore=0;
var qmaxscore=14;
var currItem=0;
function updateScore(inc){
	qscore += Number(inc);
	$('#qscore').text("Your score: " + qscore + " out of " + qmaxscore);
}
function nextItem(inc){
	currItem+=inc;
	showItem(currItem);
}
function showQ(pnum){
	p_array.push(pnum);
	$('.conversation ol li').not('.conversation ol li ol li').each(function(ind){
												
	if(	ind == pnum){
		$(this).css('display','block');
		
		if(!done_array[pnum]){//only add the back link once
			$(this).children('ol').children('li:last').after('<li class="toggle" onclick="backup()">go back</li>');
			done_array[pnum]=true;
		}
	}else{
		$(this).css('display','none');
	}
	});											
$('.conversation ol li ol li ol li').css('display','none');											
												
} 
function showItem(pnum){
	
	$('.quiz ol li').not('.quiz ol li ol li').each(function(ind){
											
	if(	ind == pnum){
		var nextI = pnum+1;
		$(this).css('display','list-item');
		var str = String(nextI);
		$(this).parent().attr('start',str);
		if(!done_array2[pnum]){//only add the back link oncea
		var nstr = '<span class="toggle" onclick="nextItem(1)">Next</span>';
		var pstr = '<span class="toggle" onclick="nextItem(-1)">Previous </span>';
		var linktext = '';
		var last = $('.quiz ol li').not('.quiz ol li ol li').length;
			if(pnum==0){
				linktext = nstr;
			}else if(nextI < last){
				linktext = pstr + ' | ' + nstr;
			}else{
				linktext = pstr;
			}
			
			$(this).children('ol').after('<div align="right">' + linktext + '</div>');
			done_array2[pnum]=true;
			
		}
	}else{
		$(this).css('display','none');
	}
	});											
$('.quiz ol li ol li ol li').css('display','none');											
												
} 
function startOver(){
	p_array = new Array();
	showQ(0);
}
function backup(){
	p_array.pop();
	showQ(p_array.pop());
}
function showFeedback(li,t){
	var status =  $(li).parent().attr('class');
	var maxscore = $(li).parent().parent().attr('maxscore');
	if(!maxscore ){
		var numOptions = $(li).parent().parent().children('li').length;
		if(status=="correct"){
			
			$(li).parent().parent().attr('maxscore',str);
			updateScore(numOptions);
			
		}else{
			numOptions--;
			
			updateScore(-1);
		}
		var str = String(numOptions);
		$(li).parent().parent().attr('maxscore',numOptions);
	}else{
		var oldMax = Number(maxscore);
		if(status=="correct"){
			updateScore(maxscore);
		}else{
			oldMax--;
			updateScore(-1);
		}
		var str = String(oldMax);
		$(li).parent().parent().attr('maxscore',oldMax);
	}
	
	
	
	
$(li).text(t);
	$(li).parent().children('ol').children('li').toggle(100);
}
function findQ(h){
	$('.conversation ol li').not('.conversation ol li ol li').each(function(j){
		var n = h.substr(1);
		
		var name = $(this).find('a:first').attr("name");
		if(name == n){
			
			showQ(j);
			
		}
	});
}
$(document).ready(function() {
//------------- glossary -------------
jQuery.fn.sort = function() {  
   return this.pushStack( [].sort.apply( this, arguments ), []);  
 };  
  
function sortAlpha(a,b){  
    return a.innerHTML.toLowerCase() > b.innerHTML.toLowerCase() ? 1 : -1;  
};  
  
$('ul.glossary li').not('ul li ul li').sort(sortAlpha).appendTo('ul.glossary');

$('ul.glossary').delegate("li","click",function(){

		var def = $(this).html();
		
		//$(this).children('ul').children('li').text();
		//var cleaned =def.replace('display:none;','');alert(cleaned);
		$('#defdisplay').html(def);
		$('#defdisplay a[rel=conversation]').click(function(i){
		var h = $(this).attr('href');
		findQ(h);
	});
});
//$('#glist ul.glossary li ul li').attr('class','definition');
$("#glist").css('height','250px');
//------------branching----------------
	$('.conversation ol li ol li').each(function(i){
			var debug = $(this).children('ol').length;//.children('ol:first').children('li').length;
			//alert(debug);
			var old = $(this).html();
			if($(this).children('ol').length){
				var a = '<a  class="toggle">+ </a> ';
				$(this).html(a + old);
			}
												 });
	$('a[class=toggle]').toggle(function(){showFeedback(this,'-')},function(){showFeedback(this,'+')});
	$('.conversation a[href^=#]').click(function(i){
												 
		var h = $(this).attr('href');
		findQ(h);
	});
	

	showQ(0);
//-----------quiz---------
qmaxscore = $('.quiz ol li ol li').not('ol li ol li ol li').length;
updateScore(0);
	$('.quiz ol li ol li').each(function(i){
			var debug = $(this).children('ol').length;//.children('ol:first').children('li').length;
			//alert(debug);
			var old = $(this).html();
			if($(this).children('ol').length){
				var a = '<a  class="toggleQ">[open]</a> ';
				$(this).html(a + old);
			}
												 });
	$('a[class=toggleQ]').toggle(function(){showFeedback(this,'[close]')},function(){showFeedback(this,'[open]')});
	$('.quiz a[href^=#]').click(function(i){
		var h = $(this).attr('href');
		findQ(h);
	});
	

	showItem(0);//show just one quiz question at a time

	
});// end ready


function locateTerm(){
	var str = $('#searchfield').val();
	var len = str.length;
$('#glist ul li').not('ul li ul li').each(function(){
		var mystart = $(this).text().substr(0,len);
		if(mystart == str){
			var display = "block";
			}else{
				var display = "none";
			}
			$(this).css('display',display);
	});											   
}
function clearSearch(){
	$('#searchfield').val('');
	$('#glist ul li').not('ul li ul li').css('display','block');
}
