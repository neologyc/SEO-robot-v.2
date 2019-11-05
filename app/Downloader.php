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
    public static function download($url, $useragent, $retries){
      	$ch = curl_init($url);
      	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
      	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      	curl_setopt($ch, CURLOPT_HEADER, true);
      	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
      	curl_setopt($ch, CURLOPT_ENCODING , "");

      	$ret = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $max_retries = globalSettings::$curl_max_retries;
        if(is_int($retries) && is_int($max_retries) && $retries >= 1 && preg_match("/5[0-9]{2}/", $httpCode) ) {
          $retry = 1;
          while(curl_errno($ch) == 28 && ($retry+1) < $retries && preg_match("/5[0-9]{2}/", $httpCode) ){
              usleep(1000000*$retry); // wait 1*retry seconds
              $ret = curl_exec($ch);
              $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
              $retry++;
          }

        }

      	list($headers, $response) = explode("\r\n\r\n", $ret, 2);
        $redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);

      	curl_close($ch);

      	return array('statusCode' => $httpCode,
                     'response' => $response,
                     'headers' => $headers,
                     'redir_url' => $redirect_url);
    }
}
