<?php
/*
*  Created by Thibaud Rohmer on 2010-12-23.
*/

include "settings.php";

$dir = scandir(urldecode($dirname),1); 
$vf_size=0;

// Generated folders
echo("	<div class='year'> Generated </div><div class='albums'><ul>
		<li class='age'> By age </li>
		</ul>
		</div>
	");


// This is for handling virtual folders
if(is_dir($virtual)){
	echo ("<div class='year'> Virtual </div><div class='albums'><ul>");
	$virtual_dir=scandir($virtual);

	for($j=0;$j<sizeof($virtual_dir);$j++) {
		$file=$virtual_dir[$j];
		if(substr($file,strrpos($file,"/")+1,1) != '.' && !is_dir($file))
		{
			echo("	<li 
				class='virtual'
				title='$virtual$file'
				> ".substr($file,strrpos($file,"/"),strrpos($file,"."))." </li>
				");
		}
	}
	echo "</ul></div>";
}

// This part is for all of our real folders
for($i=0;$i<sizeof($dir);$i++) {
	$subdirname=$dir[$i];
	if($subdirname != '.' && $subdirname != '..' && is_dir($dirname.$subdirname))
	{
		
		echo("<div class='year'> $subdirname </div><div class='albums'><ul>");

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
					if(substr($myfile,0,6)!="thumb_" && substr($myfile,0,1)!="." )
					{
						$count++;
					}
				}

				echo("
				<li 
				class='album' 
				title='".urlencode($dirname).urlencode($subdirname)."/".urlencode($file)."/'
				>
				".$myname."
				<div class='countfloat'><span class='count'>".$count."</span></div>
				</li>");
			}
		}
		echo ("</ul></div>");
	}
}

?>

