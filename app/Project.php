<?php
/**
 *
 */
class Project
{
  public $id = '';
  public $userAgent = '';
  public $robotsTests = NULL;
  public $urlsTests = NULL;
  public $log = '';
  public $projectLog = '';
  public $parseErrorLog = '';
  public $errorLog = '';
  public $errorCount = 0;
  public $errorDebugData = array();
  public $notificationEmail = '';

  function __construct( $id, $projectsSettings, $globalSettings )
  {
    $this->id = self::validateTestId($id, $projectsSettings);
    $this->userAgent = self::setUserAgent($projectsSettings->tests[$this->id], $globalSettings->curl_useragent);
    $this->robotsTests = self::parseRobotsSettings($projectsSettings->tests[$this->id]);
    $this->urlsTests = self::parseURLsSettings($projectsSettings->tests[$this->id]['testRules']);
    $this->notificationEmail = $projectsSettings->tests[$this->id]['notificationEmail'];


  }

  function runTests() {

    $this->projectLog = Logger::log('Service: ' . $this->id, 'info' );
    $this->projectLog .= Logger::log('User-agent: ' . $this->userAgent, 'info' );
    $this->projectLog .= Logger::log('Test started at: ' . date("Y-m-d H:i:s "), 'info' );


    if (!empty($this->robotsTests) ) {
      self::runRobotsTests();
    }
    if (!empty($this->urlsTests) ) {
      self::runURLsTests();
    }

    $this->projectLog .= Logger::log('Test ended at: ' . date("Y-m-d H:i:s"), 'info' );
    echo $this->projectLog;
    echo $this->parseErrorLog;
    echo $this->errorLog;
    echo $this->log;
    echo $this->errorCount;
    
  }

  function runRobotsTests() {
    require_once __DIR__.'/RobotsTest.php';
    $robotsTests = new RobotsTest($this);
  }

  function runURLsTests() {
    require_once __DIR__.'/URLsTests.php';
    $urlsTests = new URLsTests($this);
  }

  function parseURLsSettings ($testRules) {
    if(empty($testRules) ) {
      $message = 'The test setup filename in ./projectsSettings.php (line 17+ ) is empty. Please provide filename with URL tests.';
      $note = '';

      require_once __DIR__.'/CustomError.php';
      $customError = new CustomError($message, $note);
    }
    $tests_by_lines = @file( $testRules );
    if ( $tests_by_lines === FALSE ) {
      $error = error_get_last();
      $message = 'The file '. filter_var($testRules, FILTER_SANITIZE_SPECIAL_CHARS) .' was not loaded correctly. ';
      $note = $error['message'];
      require_once __DIR__.'/CustomError.php';
      $customError = new CustomError($message, $note);
    }
    if ( empty($tests_by_lines) ) {
      $message = 'The file '. filter_var($testRules, FILTER_SANITIZE_SPECIAL_CHARS) .' is empty. ';
      $note = '';
      require_once __DIR__.'/CustomError.php';
      $customError = new CustomError($message, $note);
    }
    // THE LOOP over file
    $tests = NULL; $line = 1; $previousWasURL = FALSE; $arrayIndex = 0;
    foreach ($tests_by_lines as $key => $test_line) {
        $test_line = trim($test_line);
        if ( preg_match("/^#.*/", $test_line) ) { // is comment
            $previousWasURL = FALSE;
            $line++;
            continue;
        } else if ( empty( $test_line ) ) { // is empty
            $previousWasURL = FALSE;
            $line++;
            continue;
        } else if (preg_match("/^http.*$/", $test_line) ) { // is URL
            $previousWasURL = TRUE;
            $tests[++$arrayIndex]['url'] = $test_line;
            $tests[$arrayIndex]['line'] = $line;
        } else if ( preg_match("/^[0-9]{3}$/", $test_line) && $previousWasURL ) { // is HTTP code
            $previousWasURL = FALSE;
            $tests[$arrayIndex]['http'] = $test_line;
        } else if ( preg_match("/^30[0-9]{1};http.*$/", $test_line) ) { // is redirect with check if matching target URL
            $previousWasURL = FALSE;
            $status_code = preg_replace("/;http.*/", '', $test_line);
            $tests[$arrayIndex]['http'] = trim($status_code);
            $target_url = preg_replace("/^30[0-9]{1};/", '', $test_line);
            $tests[$arrayIndex]['redirect_url'] = trim($target_url);
        } else if (preg_match("/^(hrefExact|hrefContains|plaintextExact|plaintextContains|contentExact|contentContains).*/", $test_line)) { // is rule

            $test_line_chunks = explode(';;', $test_line);
            if( count($test_line_chunks) != 4 ) {
                $this->parseErrorLog .= logger::log('Line '.$line.' skipped - not valid count of parameters. Expected 4 params, but got '.count($test_line_chunks), 'notice');
                continue;
            }
            if( !preg_match("/^(hrefExact|hrefContains|plaintextExact|plaintextContains|contentExact|contentContains)$/", $test_line_chunks[0] ) ) {
                $this->parseErrorLog .= logger::log('Line '.$line.' skipped - not valid type of action. Expected href or hrefContains or plaintext or plaintextContains or content or contentContains, but got '.$test_line_chunks[0], 'notice');
                continue;
            }
            if ( (!is_numeric($test_line_chunks[2]) || $test_line_chunks[2] < 0) && !empty($test_line_chunks[2]) ) {
                $this->parseErrorLog .= logger::log('Line '.$line.' skipped - must be numeric and greater than 0. But got '.$test_line_chunks[2], 'notice');
                continue;
            }
            $previousWasURL = FALSE;
            $tests[$arrayIndex]['rules'][$line]['type'] = $test_line_chunks[0];
            $tests[$arrayIndex]['rules'][$line]['xpath'] = $test_line_chunks[1];
            $tests[$arrayIndex]['rules'][$line]['position'] = $test_line_chunks[2];
            $tests[$arrayIndex]['rules'][$line]['expected'] = $test_line_chunks[3];

        } else {
                $this->parseErrorLog .= logger::log('Line '.$line.' skipped - For unknown reason. We got line: "'.$test_line.'"', 'notice');
                continue;
        }
        $line++;
    }
    return $tests;
  }

  function parseRobotsSettings ($tests) {
    if( !isset($tests['robotsTxtURL']) || empty($tests['robotsTxtURL'])
        || !isset($tests['robotsTxtLocalFilename']) || empty($tests['robotsTxtLocalFilename']) ){
        return NULL;
    } else {
        return array('robotsTxtLocalFilename' => $tests['robotsTxtLocalFilename'],
                     'robotsTxtURL' => $tests['robotsTxtURL'] );
    }
  }

  function validateTestId($id, $projectsSettings){
    // test if the testid is defined in settings file - settings.php
    	if ( !array_key_exists( $id, $projectsSettings->tests ) ) {
        $message = 'There is no test id "'.filter_var($id, FILTER_SANITIZE_SPECIAL_CHARS).'" in ./projectsSettings.php (line 17 - approximately). Please, provide correct test id.';
        $note = '';

        require_once __DIR__.'/CustomError.php';
        $customError = new CustomError($message, $note);
    	} else {
    		return $id;
    	}

  }

  function setUserAgent($psUA, $gsUA) {
    if( isset($psUA['curl_useragent']) && !empty($psUA['curl_useragent']) ) {
          return $psUA['curl_useragent'];
    } else if (isset($gsUA) && !empty($gsUA) ) {
          return $gsUA;
    } else {
          return '';
    }
  }


  function sendErrorEmail(){

  }


}
