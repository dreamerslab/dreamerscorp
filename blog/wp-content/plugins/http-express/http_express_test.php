<?php
	$root = dirname(dirname(dirname(dirname(__FILE__)))); // TODO fix
	require_once $root . '/wp-blog-header.php';
	
	if(!current_user_can('manage_options')){
		die('Silence is golden');
	}
	// Test HTTP
	function http_express_test_url($url){
		$content = false;		
		if(function_exists('curl_init')){
			// Use curl
			$ch = curl_init();
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
			curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11');
			curl_setopt($ch, CURLOPT_HEADER, true); // header will be at output
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD'); // HTTP request is 'HEAD'
			$content = curl_exec ($ch);
			curl_close ($ch);
		}
		elseif(function_exists('fsockopen')){
			$portno = 80;
			$method = "HEAD";
			$path = "/";
			$http_request .= $method." ".$path ." HTTP/1.1\r\n";
			$http_request .= "\r\n";
			$fp = fsockopen($url, $portno, $errno, $errstr);
			if($fp){
				fputs($fp, $http_request);
				while (!feof($fp)) $content .= fgets($fp, 128);
				fclose($fp);
			}		
		}
		
		return $content;
	}
	
	
	echo http_express_test_url($_GET['url']);
?>
