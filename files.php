<?php
/*
*  Created by Thibaud Rohmer on 2010-12-23.
*/

session_start();

include "settings.php";

include "functions.php";


$action	= $_GET['action'];
$album 	= $_GET['album'];
$page 	= $_GET['page'];
$images = $_SESSION['images'];
$groups	= $_SESSION['groups'];

if(!isset($groups))  $groups = array();

if ($page < 1) echo ("<div id='null'></div><ul id='album_contents'>");


if($action=="album"){
	$images=array();
	/* Security */
	
	if (is_file(urldecode($album)."authorized.txt")){
		$lines=file(stripslashes(urldecode($album)."authorized.txt"));
		foreach($lines as $line_num => $line)
			$authorized[]=substr($line,0,strlen($line)-1);  // substr is here for taking car of the "\n" 
		if(sizeof(array_intersect($groups,$authorized))==0){
			echo("<div id='logindiv'>Only the groups : ");
			foreach($authorized as $line_num => $group_name)
				echo "$group_name ";
			echo("are allowed to view this album.</p><div id='logindivcontent'>");
			include "login.php";
			echo("</div></div>");
			die();
		}
	}
	
	$dir = scandir(urldecode($album)); 
	for($i=0;$i<sizeof($dir);$i++) 
	{
		$images[]=$album.$dir[$i];
	}
}elseif($action=="age"){
	$images=sort_by_date($groups);

}elseif($action=="virtual"){
	$images=array();
	$lines=file(stripslashes(urldecode($album)));
	foreach($lines as $line_num => $line)
		$images[]=$line;

}elseif($action=="go_on"){
// Do nothing
}

	$size_dir=sizeof($images);

$_SESSION['images']=$images;
display_thumbnails($images,$page*$limit,$limit);	


$imagesphp=array_to_get($images,"album");

$nextpage=$page+1;


if ($page < 1) echo ("</ul><div class='end'>More...</div>");


if($page<1) {
	echo("
	<script>
	$(document).ready(function() {
		
		change_display('init');	
		var page = 0;
		
		if((page+1)*$limit + 2 >= $size_dir) {
			$('.end').remove();
		}
		
		$('.end').click(function(){
			page++;
			display_more(page);
		});
		
		function display_more(){
			if((page+1)*$limit + 2 >= $size_dir) {
				$('.end').hide();
			}
			$.get('./files.php?action=go_on&page='+page,function(data){ 
			$(data).appendTo('#album_contents'); 
			});
		}
		
	});
	</script>
	");

}else{
	echo ("
		<script>
		$('#projcontent a').unbind();
		$('#projcontent a').click(function(){ 
			$('.select').removeClass('select');
			$(this).parent().addClass('select');
			refresh_img(this.title);
			change_display(this.title); 
			$('#menubar').show()
			return false;
		});	
		</script>
		");
}
?>


