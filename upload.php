<?php//-*- mode: c;tab-width: 4; c-basic-offset: 4; indent-tabs-mode: t;-*- ?>
<?php require_once('settings.php'); ?>

<html>
<head>
<!-- Load Queue widget CSS and jQuery -->
<style type="text/css">@import url(plupload/examples/css/plupload.queue.css);</style>
<style type="text/css" src="stylesheet.css"></style>
<script type="text/javascript" src="jQuery/jquery.min.js"></script>

<!-- Thirdparty intialization scripts, needed for the Google Gears and BrowserPlus runtimes -->
<script type="text/javascript" src="plupload/js/gears_init.js"></script>
<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
<script type="text/javascript" src="plupload/js/plupload.full.min.js"></script>
<script type="text/javascript" src="plupload/src/javascript/plupload.html5.js"></script>
<script type="text/javascript" src="plupload/js/jquery.plupload.queue.min.js"></script>


<script type="text/javascript">
// Make javascript aware of some of the PhotoShow settings 
var settings = {dirname: '<?php echo $dirname ?>'};
</script>

<script type="text/javascript" src="upload.js"></script>

<style>
.hidden {display:none}
.album.selected {background: red}
</style>
</head>
<body>

<ul id="folders">
<?php
$dir = scandir(urldecode($dirname),1); 
for($i=0;$i<sizeof($dir);$i++) {
	$subdirname=$dir[$i];
	if($subdirname != '.' && $subdirname != '..' && is_dir($dirname.$subdirname)) {
	  echo("<li class='folder' title='".urlencode($subdirname)."' >$subdirname"
		   ."<a href='#' class='addalbum' onclick='addNewAlbum()'>Add album</a>"
		   ."<ul class='albums'>");
	  
	  $subdir=scandir($dirname.$subdirname,1);
	  for($j=0;$j<sizeof($subdir);$j++) {
		$file=$subdir[$j];
		if($file != '.' && $file != '..' && is_dir($dirname.$subdirname."/".$file) && $dirname.$file!=$virtual)
		  {
			$myname=str_replace("_"," ",$file);
			
			$files=scandir($dirname.$subdirname."/".$file);
			$count=0;
			for($k=0;$k<sizeof($files);$k++) {
			  $myfile=$files[$k];
			  if(substr($myfile,0,6)!="thumb_" && substr($myfile,0,1)!="." 
				 && substr($myfile,-3,3) != "txt") {
						$count++;
					}
				}

				echo("<li class='album'	title='"
					 .urlencode($dirname).urlencode($subdirname)
					 ."/".urlencode($file)."/'>"
					 .$myname." (".$count." photos)</li>");
			}
		}
		echo ("</ul></li>");
	}
}
?>
<li class='future-folder'><a href="#">Add folder</a></li>
</ul>
<form>
<div id='uploader'>
<p>You must enable javascript to use the upload feature</p>.
</div>
</form>							    
</body>
</html>
