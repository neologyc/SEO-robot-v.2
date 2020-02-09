<?php
/**
 *
 */
class Downloader
{
  public $url_to_download = '';
  public $user_agent = '';
  public $retry = 0;

  function __construct( $url, Project $projectData )
  {
      $this->url_to_download = $projectData->robotsTests['robotsTxtLocalFilename'];
      $this->user_agent = $projectData->robotsTests['userAgent'];

      self::runTest();
      $projectData->log .= $this->log;

  }

  /**
   * Downloads HTML response and status code of URL.
   *
   * @param string $url URL to download
   * @param string $useragent Useragent of the request. Default can be changed in settings file
   * @return array htmlContent and statusCode.
   */
    public static function download($url, $useragent){
      	$ch = curl_init($url);
      	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
      	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      	curl_setopt($ch, CURLOPT_HEADER, true);
      	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
      	curl_setopt($ch, CURLOPT_ENCODING , "");
             
        $max_retries = globalSettings::$curl_max_retries > 0 ? (int) globalSettings::$curl_max_retries : 0;

        
        $retryCount = -1;
        do {
            // CURL
            $ret = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErrno = curl_errno($ch);
            $retryCount++;

            /*echo 'retry '; var_dump($retryCount);echo "<br>\r\n"; 
            echo 'ch '; var_dump($ch);echo "<br>\r\n"; 
            echo 'httpCode '; var_dump($httpCode);echo "<br>\r\n"; 
            echo 'max_retries '; var_dump($max_retries);echo "<br>\r\n"; 
            echo 'curl_errno($ch) '; var_dump( $curlErrno );echo "<br>\r\n"; */

        } while ( ($curlErrno != 0 || preg_match("/5[0-9]{2}/", $httpCode) ) && $retryCount < $max_retries );


      	list($headers, $response) = explode("\r\n\r\n", $ret, 2);
        $redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);

      	curl_close($ch);

      	return array('statusCode' => $httpCode,
                     'response' => $response,
                     'headers' => $headers,
                     'redir_url' => $redirect_url);
    }
}
