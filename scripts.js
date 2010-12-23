/*
*  Created by Thibaud Rohmer on 2010-12-23.
*/

/* 	refresh_img
*	loads given image url in the fullscreen (fs) and display (display_img) divs 
*	and updates exif panel
*/
function refresh_img(url){
	if(url==""){
		$('#display_img').html("");
		$('#exif .content').html("");
		return;
	}
	$('#display_img').html('<span></span><a href="'+url+'"><img src="'+url+'"/></a>');
	$('#fs').html('<img src="'+url+'"/>');

	$('#exif .content').load('exif.php?img='+url);
	$("#ex").show();

	$('#display_img a').click(function(){ 
		change_display();
		return false;
	});
	
	location.hash=url;
	
}

/* 	preload_next
*	loads next image in memory for future viewing
*	
*/
function preload_next(){
	if(typeof($(".select").next().attr("class")) == 'undefined'){
		$('.end').trigger('click');
	}
	
	nextImage = new Image(); 
	nextImage.src = $(".select").next().children("a").attr("title");
	
}

/* 	preload_prev
*	loads previous image in memory for future viewing
*	
*/
function preload_prev(){
	if(typeof($(".select").prev().attr("class")) != 'undefined'){
		nextImage = new Image(); 
		nextImage.src = $(".select").prev().children("a").attr("title");
	}
}

/* 	select_next
*	selects and displays next image
*	and preloads the next one
*/
function select_next(){
	if(typeof($(".select").attr("class")) == 'undefined'){
		$("#projcontent ul li").first().addClass("select");
		refresh_img($(".select a").attr('title'));
	}else{	
		if(typeof($(".select").next().attr("class")) == 'undefined'){
		    $('.end').trigger('click');
		}
		if(typeof($(".select").next().attr("class")) != 'undefined'){
			var act=$(".select");
			$(".select").next().addClass("select");
			act.removeClass("select");
			refresh_img($(".select a").attr('title'));
		}
	}
	preload_next();
}

/* 	select_prev
*	selects and displays previous image
*	and preloads the one even before
*/
function select_prev() {
	if(typeof($(".select").attr("class")) == 'undefined'){
		$("#projcontent ul li").last().addClass("select");
		refresh_img($(".select a").attr('title'));
	}else{
		if(typeof($(".select").prev().attr("class")) != 'undefined'){
			var act=$(".select");
			$(".select").prev().addClass("select");
			act.removeClass("select");
			refresh_img($(".select a").attr('title'));		  
		}
	}
	preload_prev();
}

/* 	change_display
*	toggles display_div visibility along with many other things...
*	
*/
function change_display(val){
	if(val!="init" && $("#display2").css("display")=="none"){

		$("#projcontent").removeClass("fullpage").addClass("inline");
		$("#menubar").show().removeClass("menubar-fullpage").addClass("menubar-inline");
		$(".end").hide();


		$("#display2").fadeIn();

		$('#projcontent a').unbind();
		$('#display_img a').unbind();

		$('#projcontent a').click(function(){
			if ($(this).parent().hasClass("select")) return false;
			$(".select").removeClass("select");
			$(this).parent().addClass("select");			
			refresh_img(this.title);
			return false;
		});

		$('#projcontent a').dblclick(function(){ 
			change_display(); 
			return false;
		});

		$('#display_img a').click(function(){ 
			change_display();
			return false;
		});

	}else{

		$('#projcontent a').unbind();
		$('#display_img a').unbind();
		
		$("#display2").fadeOut();
		$(".end").show();

		$("#projcontent").removeClass("inline").addClass("fullpage");
		$("#menubar").hide().removeClass("menubar-inline").addClass("menubar-fullpage");


		$('#projcontent a').click(function(){ 
			$(".select").removeClass("select");
			$(this).parent().addClass("select");
			refresh_img(this.title);
			change_display(this.title); 
			return false;
		});
	}
};

/* 	num_selected
*	returns the number of items selected
*
*/
function num_selected(){
	return $(".select a").size();
};

/* 	list_selected
*	returns a list of selected items
*/
function list_selected(){
	var mylist=new Array();
	$(".select a").each(function(){
		mylist[mylist.length] = ($(this).attr("href"));
	});
	return mylist;
};

/* 	list_selected_as_php
*	returns a php list of selected items in a string
*/
function list_selected_as_php(){
	var mylist="?";
	$(".select a").each(function(){
		mylist=mylist+"mylist[]="+($(this).attr("href"))+"&";
	});
	return mylist;
};



$(document).ready(function() {
	
/* Left menu */
	
	accordionCache = $('div#accordion');
  	$('.year', accordionCache).click( function () {
    	$('div.albums', accordionCache).removeClass('open');
		$('.year').removeClass('menu_selected');
		$(this).addClass('menu_selected');
    	$(this).next().addClass('open').slideDown('slow');
    	$('div.albums:not(.open)', accordionCache).slideUp();
  	} );


	$("#leftcolumn li").click(function() {
		$("#projcontent").load("./files.php?action="+$(this).attr("class")+"&album="+$(this).attr("title"));
		$('#ex').hide();
		$('#exif').hide();	
		location.hash="action="+$(this).attr("class")+"&album="+$(this).attr("title");
	});
	
/* Menubar */

	$("#next a").click(function(){		
		select_next();
	});
	
	$("#prev a").click(function(){
		select_prev();
	});
	
	$("#ex a").click(function(){
		$("#exif").toggle("slow");
	});
	
	$("#wtf a").click(function(){
		$('#help .content').load('help.txt');
		if($("#help").is(":visible")){
			$("#help").fadeOut("slow");
		}else{
			$("#help").fadeIn("slow");
		}
	});
	
	$( "#exif" ).draggable();
		
/* Diaporama */

	var diapo=0;
	var diapoId=0;
	
	function diaporama(){
		if(diapo==1){
			$("#fs").show();
			diapoId = setInterval(select_next,3000);
		}else{
			$("#fs").hide();
			clearInterval(diapoId);
			diapoId=0;
		}
	}	

	function pause_diaporama(){
		if(diapo==1){
			if (diapoId==0){
				diapoId = setInterval(select_next,3000);
			}else{
				clearInterval(diapoId);
				diapoId=0;
			}
		}
	}

/* Keyboard events */
	
	$('body').keydown(function(event) {

		if (event.keyCode == '39') { // arrow right
			select_next();
	   	}
		if (event.keyCode == '37') { // arrow left
				select_prev();
		}
		if (event.keyCode == '40') { //arrow down
			var number=($(window).width()-360)/170;
			for(i=0;i<number;i++){
				select_next();
			}
	   	}
		if (event.keyCode == '38') { //arrow up
			var number=($(window).width()-360)/170;
			for(i=0;i<number;i++){
				select_prev();
			}
	   	}
	  	if (event.keyCode == '70') { // f
			if(diapo!=1){
				$("#fs").toggle();
			}
	   	}
	  	if (event.keyCode == '72') { // h
			$('#help .content').load('help.txt');
			if($("#help").is(":visible")){
				$("#help").fadeOut("slow");
			}else{
				$("#help").fadeIn("slow");
			}
	   	}
	  	if (event.keyCode == '13') { // enter
			change_display();
	   	}

	  	if (event.keyCode == '27') { // escape
			if($("#fs").is(":visible")) {
				$("#fs").hide();
				if(diapo==1){
					diapo=0;
					diaporama();
				}
			}else{
				if($("#projcontent").hasClass("inline")) {
					change_display();
				}else{
					$(".select").removeClass("select");
   				}
			}
		}

	  	if (event.keyCode == '68') { // d
			diapo=1-diapo;
			diaporama();
		}
		
		if (event.keyCode == '32') { // space
			pause_diaporama();
		}

		if (event.keyCode == '79') { // o

		}
		
	});

	
// Anchor

	if(location.hash.length>2){
		var parsed_hash=location.hash.substr(1);
		$('#ex').hide();
		$('#exif').hide();	
		$("#menubar").show();
	
		if(parsed_hash.charAt(0)=='a'){ // It's an album
			$("#projcontent").load("./files.php?"+parsed_hash,function(){
				change_display("init");
			});
		
		}else{ // It's an image
			var album = parsed_hash.substr(0,parsed_hash.lastIndexOf("/")+1);
			
			$("#projcontent").load("./files.php?action=display_dir&album="+album,function(){
				refresh_img(parsed_hash);
				change_display();				
			});
		}
	}else{
		$("#projcontent").load("./files.php?action=sort_by_age",function(){
			change_display("init");
			$(".end").hide();
		});
	}


});
