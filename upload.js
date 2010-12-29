// -*- tab-width: 4; c-basic-offset: 4; indent-tabs-mode: t;-*-

var backend_url = 'upload-backend.php';

$(document).ready(function() {
	$("#uploader").pluploadQueue({
		// General settings
		runtimes : 'html5,flash,gears,html4',
		url : backend_url,
		max_file_size : '10mb',
		chunk_size : '1mb',
		unique_names : false,
		
		// Resize images on clientside if we can 
		resize : settings.resize_images ? settings.resizeto : false,
		// Specify what files to browse for
		filters : [
			{title : "Image files", extensions : "jpg,gif,png"},
			{title : "Zip files", extensions : "zip"}
		],
		
		// Flash settings
		flash_swf_url : 'plupload/js/plupload.flash.swf',
		
		// Silverlight settings
		silverlight_xap_url : 'plupload/js/plupload.silverlight.xap',

		// HTML5 settings
		/* Allows to set jpeg quality in mozilla but is slower
		 * see https://bugzilla.mozilla.org/show_bug.cgi?id=531185
		 * and http://www.bytestrom.eu/blog/2009/1120a_jpeg_encoder_for_javascript
		 */
		html5_use_purejs_jpeg : true,
		html5_purejs_jpeg_url : 'plupload/src/javascript/jpeg_encoder_basic.js',

		preinit : function(up) {
			up.bind('BeforeUpload', function(up, file) {
				var selectedAlbum = $('.album.selected');
				setUploadFolder(up, selectedAlbum.attr('title'));
			});
		}
	});
	
	// Client side form validation
	
	$('a.plupload_start').unbind();
	$('a.plupload_start').click(function(e) {
	    if ($(this).hasClass('plupload_disabled')) {
			return;
	    }
		var uploader = $('#uploader').pluploadQueue();

		var selectedAlbum = $('.album.selected');
		if (selectedAlbum.length != 1) {
			alert('Select an album to upload your files');

		} else {
		    setUploadFolder(uploader, selectedAlbum.attr('title'));
			//console.log('uploader', uploader);
			// Validate number of uploaded files
			if (uploader.total.uploaded == 0) {
				// Files in queue upload them first
				if (uploader.files.length > 0) {
					// When all files are uploaded submit form
					uploader.bind('UploadProgress', function() {
						if (uploader.total.uploaded == uploader.files.length) {
							$('form').submit();
						}
					});

					uploader.start();
				} else {
					alert('You must at least upload one file.');
				}
			}
		}
	    e.preventDefault();
	});
    
    $('.album').click(function() {selectAlbum(this);});

	$('.future-folder a').click(addNewFolder);
});

function selectAlbum(album) {
	$('.album').removeClass('selected');
	$(album).addClass('selected');
}

function setUploadFolder(uploader, folder) {
	uploader.settings.url = backend_url + '?folder=' + folder;
}

function addNewFolder() {
	var newFolder = $(
		"<li class='folder editing'>"
			+"<input type='text' class='folder-name' value='New Folder name'/>"
			+"<a href='#' class='addalbum hidden' onclick='addNewAlbum();return false'>Add album</a>"
			+"<ul class='albums'></ul>"
		+"</li>");

	$(this).before(newFolder);
	
	var input = newFolder.find('.folder-name');
	input.select();
	input.change(function() {
		$(this).parents('.folder')[0].title =
			encodeURI(settings.dirname + '/' + this.value);
	});
	input.keypress(function(e) {
		$('.addalbum.hidden').show();
		$(this).unbind(e)
	});
}

function addNewAlbum() {
	var relatedFolder = $('.folder:has(a:hover)');
	var relatedFolderInput = (relatedFolder.find('.folder-name'))[0];
	var relatedList = relatedFolder.find('ul.albums');
	var newAlbum = $("<li class='album'><input type='text' class='album-name' value='Enter album name'></li>'");
	// We make it impossible to modify the folder name from now
	if (relatedFolder.hasClass("editing")) {
		$(relatedFolderInput).replaceWith(relatedFolderInput.value);
		relatedFolder.removeClass("editing");
	}

	relatedList.append(newAlbum);
	selectAlbum(newAlbum);
	newAlbum.click(function(){selectAlbum(this)});

	var input = newAlbum.find('input');
	input.select();
	
	input.change(function() {
		newAlbum[0].title = relatedFolder[0].title + '/' + encodeURI(this.value);
	});

}
