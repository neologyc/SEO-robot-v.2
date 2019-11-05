<?php

/**
 * Downloads HTML response and status code of URL.
 *
 * @param string $url URL to download
 * @param string $useragent Useragent of the request. Default can be changed in settings file
 * @return array htmlContent and statusCode.
 */
function downloadURL($url, $useragent){
	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($ch, CURLOPT_ENCODING , "");

	$ret = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	list($headers, $response) = explode("\r\n\r\n", $ret, 2);

	curl_close($ch);

	return array('statusCode' => $httpCode, 'response' => $response, 'headers' => $headers );
}






function getRowsFromFile($filename){
  $data = file("./urls/".$filename, FILE_SKIP_EMPTY_LINES);
  $unused = array_splice($data,LIMITPERDAY-1);
  // rewrite file
  $string_data = implode($unused);
  file_put_contents("./urls/".$filename, $string_data);

  return array_splice($data,0,LIMITPERDAY);
}
