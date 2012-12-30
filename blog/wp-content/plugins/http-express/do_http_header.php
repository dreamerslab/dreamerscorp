<?php
	// Get file abspath passed by htaccess
	$file = strip_tags($_GET['file']);
	
	// Wp root directory
	$root = dirname(dirname(dirname(dirname(__FILE__)))); // TODO fix
	
	// Try to get only paths relative to root
	$file_relative_path = strstr($file,$root);
	
	// Check for file extension
	preg_match("/\.(gif|png|jpeg|jpg|bmp|swf|css|js)$/i", $file_relative_path, $extension);
	
	// Switch extension
	$content_type = false;
	if(!empty($extension)){
		switch($extension[1]){
			case 'css':
				$content_type = 'text/css';
			break;
			case 'js':
				$content_type = 'application/javascript';
			break;
			case 'jpg':
			case 'jpeg':
				$content_type = 'image/jpeg';
			break;
			case 'gif':
				$content_type = 'image/gif';
			break;
			case 'png':
				$content_type = 'image/png';
			break;
			case 'bmp':
				$content_type = 'image/bmp';
			break;
			case 'swf':
				$content_type = 'application/x-shockwave-flash';
			break;
		}
	}
	
	// Output 404 if none of the necessary conditions are met
	if(!$file_relative_path || !file_exists($file_relative_path) || empty($extension) || $content_type === false ){
		header("HTTP/1.1 404 Not Found");
		die();
	}
	
	// Get file data
	$file_content = file_get_contents($file_relative_path);
	$stat = @stat($file_relative_path);
	$filesize = @filesize($file_relative_path);
	
	// Output headers
	header('Content-Type: '.$content_type);
	header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (86400 * 15))); // 15 days in the future
	header("Cache-Control: max-age=".(86400 * 15).", must-revalidate");
	header('Pragma: public');
	header('X-Powered-By: Kifulab HTTP Express');
	if(!empty($stat[9]))  header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T',$stat[9]));
	if(!empty($filesize)) header('Content-Length: '.$filesize);
	
	// Output file content and die
	echo $file_content;
	die();
?>
