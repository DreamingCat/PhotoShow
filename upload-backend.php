<?php //-*- mode: c;tab-width: 4; c-basic-offset: 4; indent-tabs-mode: t;-*-
  /**
   *
   * Copyright 2009, Moxiecode Systems AB
   * modified by Jocelyn Delalande, 2010
   * Released under GPL License.
   *
   * License: http://www.plupload.com/license
   * Contributing: http://www.plupload.com/contributing
   */

require_once('settings.php');

function log_and_die($msg) {
	/* Uncomment to log all request answers
	$logFile = fopen("/tmp/plupload.log", 'a');
	fwrite($logFile, $msg."\n");
	fclose($logFile);
	//*/

	die($msg);
}

function stripslash($str) {
	$lastchar = $str[strlen($str)-1];
	return  ($lastchar != '/' ) ? $str : substr($str, 0, strlen($str)-1);
}

/// Returns parent dir, wether the argument is a file or dir.
function parentdir($path) {
	return dirname(stripslash($path));
}

/** Returns the destination folder path if valid, die otherwise
 *
 * creates the folder if non-existent.
 */
function validate_folder($folder) {
	global $dirname;
	// Checks if the selected folder is a child of the photos dir.
	if (fileinode(parentdir(parentdir($folder))) == fileinode($dirname)) {
		if (file_exists($folder)) {
			if (! is_dir($folder)) {
				log_and_die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "'.$folder.' is not a folder."}, "id" : "id"}');
			}

			if (!is_writable($folder)) {
				log_and_die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "'.$folder.' is not writable."}, "id" : "id"}');
			}
		} else {
			if (!mkdir($folder, 0744, true)) {
				log_and_die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Unable to create '.$folder.'."}, "id" : "id"}');
			}
		}
	} else {
		log_and_die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Illegal destination folder, '
					.$folder.' is not a child of '.$dirname.'"}, "id" : "id"}');
	}
	return $folder;
}



function http_request_headers() {
	foreach($_SERVER as $key=>$value) {
		if (substr($key,0,5)=="HTTP_") {
			$key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
			$out[$key]=$value;
		}
	}
	return $out;
}

// Security check
if (!$allow_upload) {
	log_and_die('{"jsonrpc" : "2.0", "error" : {"code": -1, "message": "upload disabled"}, "id" : "id"}');
}

// HTTP headers for no cache etc
header('Content-type: text/plain; charset=UTF-8');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Uncomment to log every the last request
//file_put_contents('/tmp/last_plupload_req.log',var_export(apache_request_headers(), true).'\n'.var_export($_GET,true).'\n'.var_export($_POST,true));

// Settings
$cleanupTargetDir = true; // Remove old files
$maxFileAge = 60 * 60; // Temp file age in seconds

// Get sanely the destination folder from  user request
$finalDir  = validate_folder($_GET['folder']); //"/tmp/uploads2/"; //final directory <- need these to be variable

// 5 minutes execution time
@set_time_limit(5 * 60);
// usleep(5000);

// Get parameters
$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

// Clean the fileName for security reasons
$fileName = preg_replace('/[^\w\._]+/', '', $fileName);

// Create target dir
if (!file_exists($tmp_upload_dir))
	@mkdir($tmp_upload_dir);

// Remove old temp files
if (is_dir($tmp_upload_dir) && ($dir = opendir($tmp_upload_dir))) {
	while (($file = readdir($dir)) !== false) {
		$filePath = $tmp_upload_dir . DIRECTORY_SEPARATOR . $file;

		// Remove temp files if they are older than the max age
		if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge))
			@unlink($filePath);
	}

	closedir($dir);
 } else
	log_and_die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');

// Look for the content type header
if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
	$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

if (isset($_SERVER["CONTENT_TYPE"]))
	$contentType = $_SERVER["CONTENT_TYPE"];

if (strpos($contentType, "multipart") !== false) {
	if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
		// Open temp file
		$out = fopen($tmp_upload_dir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
		if ($out) {
			// Read binary input stream and append it to temp file
			$in = fopen($_FILES['file']['tmp_name'], "rb");

			if ($in) {
				while ($buff = fread($in, 4096))
					fwrite($out, $buff);
			} else
				log_and_die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

			fclose($out);
			unlink($_FILES['file']['tmp_name']);
		} else
			log_and_die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
	} else
		log_and_die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
 } else {
	// Open temp file
	$out = fopen($tmp_upload_dir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
	if ($out) {
		// Read binary input stream and append it to temp file
		$in = fopen("php://input", "rb");

		if ($in) {
			while ($buff = fread($in, 4096)){
				fwrite($out, $buff);
			}

		} else
			log_and_die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');


		fclose($out);
	} else {
		log_and_die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
	}
 }
//Moves the file from $tmp_upload_dir to $finalDir after receiving the final chunk
if($chunk == ($chunks-1)){
	rename($tmp_upload_dir . DIRECTORY_SEPARATOR . $fileName, $finalDir . DIRECTORY_SEPARATOR . $fileName);
}

// Return JSON-RPC response
log_and_die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
?>
