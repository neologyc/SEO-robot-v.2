<?php
/**
 *
 */
class RobotsTest {
  public $log = '';
  public $robotsTxtLocalFilename = NULL;
  public $robotsTxtURL = NULL;
  public $userAgent = NULL;
  public $download = NULL;

  function __construct( Project $projectData )
  {
      $this->robotsTxtLocalFilename = $projectData->robotsTests['robotsTxtLocalFilename'];
      $this->robotsTxtURL = $projectData->robotsTests['robotsTxtURL'];
      $this->userAgent = $projectData->userAgent;

      self::runTest();
      $projectData->log .= $this->log;
  }

  function runTest() {
    if ( !empty( $this->robotsTxtLocalFilename ) && !empty( $this->robotsTxtURL ) ){

      // setup logs
      $this->log .= logger::log('TESTING ROBOTS.TXT:', 'boldInfo');

      // download file
      $this->download = Downloader::download($this->robotsTxtURL, $this->userAgent);
      if($this->download['statusCode'] != 200 ){
          $this->log .= logger::log('Robots.txt was not downloaded correctly. Webserver responded HTTP status code : '. $this->download['statusCode'] , 'error');
      } else {
          // compare files
          try {
              $localFile = file_get_contents($this->robotsTxtLocalFilename);
          } catch (\Exception $e) {
            require_once __DIR__.'/CustomError.php';
            $customError = new CustomError("Something went wrong while opening the file ".filter_var($this->robotsTxtLocalFilename, FILTER_SANITIZE_SPECIAL_CHARS)." : ".$e->message);
          }
          if ($this->download['response'] == $localFile) {
            $this->log .= logger::log('Robots.txt correctly matches with local file.');
          } else {
            $this->log .= logger::log('Robots.txt does NOT match with local file.', 'error');
          }
      }

    } else {
      $this->log .= logger::log('Not testing robots.txt file, because it is not set up.', 'info');
    }
    return $this->log;


  }

}
