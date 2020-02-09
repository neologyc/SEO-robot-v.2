<?php
/**
 *
 */
require __DIR__.'/../vendor/autoload.php';
use KubAT\PhpSimple\HtmlDomParser;


class URLsTests {
  public $log = '';
  public $errorsLog = '';
  public $errorLogTmp = '';
  public $userAgent = NULL;
  public $download = NULL;
  public $testList = NULL;
  public $downloadedData = NULL;
  public $errorCount = 0;
  public $errorDebugData = array();


  function __construct( Project $projectData )
  {
      //echo '<pre>';
      //print_r($projectData);
      $this->testList = $projectData->urlsTests;
      $this->userAgent = $projectData->userAgent;
      self::runTest();
      $projectData->log .= $this->log;
      $projectData->errorLog .= $this->errorsLog;
      $projectData->errorCount = $this->errorCount;
      $projectData->errorDebugData = $this->errorDebugData;
  }

  function runTest() {

    if ( !empty( $this->testList ) ){
      $this->log .= logger::log('URLS TESTS:', 'boldInfo');

      foreach ($this->testList as $key => $test) {
          $hasError = FALSE;
          $tmp_error = '';
          $downloadedData = NULL;
          $errorLogTmp = '';

          $downloadedData = Downloader::download($test['url'], $this->userAgent);
          $this->downloadedData = $downloadedData;


          $this->log .= logger::log('URL: <a href="'.$test['url'].'">'.$test['url'].'</a> on line: '.$test['line'] , 'boldInfo');

          // STATUS CODE TEST - only if setted up
          if ( !empty($test['http']) ) {
              if ($downloadedData['statusCode']==$test['http']
                  && preg_match("/3[0-9]{2}/", $test['http'])
                  && !empty( $test['redirect_url'] )  ) { // HTTP code is 3XX and redirect_url was set and http is as expected

                    if ( $downloadedData['redir_url'] == $test['redirect_url'] ) {
                        $this->log .= logger::log('HTTP status code (expected: '.$test['http'].') AND target URL (expected: '.$test['redirect_url'].') are as expected.');
                    } else {
                        $tmp_error = 'HTTP status code (expected: '.$test['http'].') is as expected, BUT target URL (expected: '.$test['redirect_url'].') is different: '.$downloadedData['redir_url'].' (online) ';
                        $this->log .= logger::log($tmp_error, 'error');
                        $errorLogTmp = logger::log('Line: '.$test['line']. ' ' . $tmp_error, 'error');
                        $hasError = TRUE;
                        $this->errorCount++;
                    }
              } else if ($downloadedData['statusCode']==$test['http']) {
                        $this->log .= logger::log('HTTP status code (expected: '.$test['http'].') is as expected.');
              } else { // HTTP code is not as expected
                        $tmp_error = 'HTTP status code (expected: '.$test['http'].') is NOT as expected: '.$downloadedData['statusCode'].' (online)';
                        $this->log .= logger::log($tmp_error, 'error');
                        $errorLogTmp = logger::log('Line: '.$test['line']. ' ' . $tmp_error, 'error');
                        $hasError = TRUE;
                        $this->errorCount++;
              }
          }

          // HTML TESTS - only if setted up
          if ( !empty($test['rules']) ) {
            
              // It tests only URLs with 200 OK HTTP status and with not-empty HTML response
              if ( $this->downloadedData['statusCode'] != 200 ) {
                  $tmp_error = 'HTTP status code (expected: 200 OK) is NOT as expected: '.$this->downloadedData['statusCode'].' (online). SEO robot tests custom rules only when URL returned HTTP 200 OK.';
                  $this->log .= logger::log($tmp_error, 'error');
                  $errorLogTmp = logger::log('Line: '.$test['line']. ' ' . $tmp_error, 'error');
                  $hasError = TRUE;
                  $this->errorCount++;

              } else if ( empty( $this->downloadedData['response'] ) ) {
                  $tmp_error = 'Blank HTL response from the server. SEO robot tests custom rules only when URL returned HTTP 200 OK with not empty response.';
                  $this->log .= logger::log($tmp_error, 'error');
                  $errorLogTmp = logger::log('Line: '.$test['line']. ' ' . $tmp_error, 'error');
                  $hasError = TRUE;
                  $this->errorCount++;

              } else {
                  try {
          				    $html = HtmlDomParser::str_get_html( $this->downloadedData['response'] );


          				} catch (Exception $e) {
          				    echo 'Caught exception: ',  $e->getMessage(), "\r\n"; //TODO
          				}

                  foreach ($test['rules'] as $testLineId => $testItem) {
                      switch ($testItem['type']) {
                        case 'hrefExact':
                            $expected = $testItem['expected'];

                            if( $testItem['position'] != "" ) {
                              $tmp = $html->find($testItem['xpath'], $testItem['position']);
                            } else {
                              $tmp = $html->find($testItem['xpath']); // TODO if position is not set, then take latest }what is not correct - because for meta robots noindex order doesnt matter - worse gets priority
                              if( count($tmp) > 1 ){
                                $latest = count($tmp)-1;
                                $tmp = $html->find($testItem['xpath'], $latest);
                              }
                            }

                            if( !empty($tmp) ) {
                              $found = trim ($tmp->href);
                            } else {
                              $found = NULL;
                            }

                            if( $expected == $found) {
                                $this->log .= logger::log($testItem['xpath'].' '.$testItem['position'].' is as expected: '.$testItem['expected']);

                            } else {
                                $tmp_error = $testItem['xpath'].' '.$testItem['position'].' is NOT as expected: "'.$testItem['expected']. '". Online is: <xmp style="display:inline-block">"'.$found.'"</xmp>';
                                $this->log .= logger::log($tmp_error, 'error');
                                $errorLogTmp = logger::log('Line: '.$testLineId. ' ' . $tmp_error, 'error');
                                $hasError = TRUE;
                                $this->errorCount++;
                            }
                            break;

                        case 'hrefContains':
                            $expected = $testItem['expected'];

                            if( $testItem['position'] != "" ) {
                              $tmp = $html->find($testItem['xpath'], $testItem['position']);
                            } else {
                              $tmp = $html->find($testItem['xpath']); // TODO if position is not set, then take latest }what is not correct - because for meta robots noindex order doesnt matter - worse gets priority
                              if( count($tmp) > 1 ){
                                $latest = count($tmp)-1;
                                $tmp = $html->find($testItem['xpath'], $latest);
                              }
                            }

                            if( !empty($tmp) ) {
                              $found = trim ($tmp->href);
                            } else {
                              $found = NULL;
                            }

                            if( strpos($found, $expected) !== FALSE ) {
                                $this->log .= logger::log($testItem['xpath'].' '.$testItem['position'].' is as expected: '.$testItem['expected']);

                            } else {
                                $tmp_error = $testItem['xpath'].' '.$testItem['position'].' is NOT as expected: "'.$testItem['expected']. '". Online is: <xmp style="display:inline-block">"'.$found.'"</xmp>';
                                $this->log .= logger::log($tmp_error, 'error');
                                $errorLogTmp = logger::log('Line: '.$testLineId. ' ' . $tmp_error, 'error');
                                $hasError = TRUE;
                                $this->errorCount++;
                            }
                            break;

                        case 'plaintextExact':
                            $expected = $testItem['expected'];

                            if( $testItem['position'] != "" ) {
                              $tmp = $html->find($testItem['xpath'], $testItem['position']);
                            } else {
                              $tmp = $html->find($testItem['xpath']); // TODO if position is not set, then take latest }what is not correct - because for meta robots noindex order doesnt matter - worse gets priority
                              if( count($tmp) > 1 ){
                                $latest = count($tmp)-1;
                                $tmp = $html->find($testItem['xpath'], $latest);
                              }
                            }

                            if( !empty($tmp) ) {
                              $found = trim ($tmp->plaintext);
                            } else {
                              $found = NULL;
                            }

                            if( $expected == $found) {
                                $this->log .= logger::log($testItem['xpath'].' '.$testItem['position'].' is as expected: '.$testItem['expected']);

                            } else {
                                $tmp_error = $testItem['xpath'].' '.$testItem['position'].' is NOT as expected: "'.$testItem['expected']. '". Online is: <xmp style="display:inline-block">"'.$found.'"</xmp>';
                                $this->log .= logger::log($tmp_error, 'error');
                                $errorLogTmp = logger::log('Line: '.$testLineId. ' ' . $tmp_error, 'error');
                                $hasError = TRUE;
                                $this->errorCount++;
                            }
                            break;

                        case 'plaintextContains':
                            $expected = $testItem['expected'];

                            if( $testItem['position'] != "" ) {
                              $tmp = $html->find($testItem['xpath'], $testItem['position']);
                            } else {
                              $tmp = $html->find($testItem['xpath']); // TODO if position is not set, then take latest }what is not correct - because for meta robots noindex order doesnt matter - worse gets priority
                              if( count($tmp) > 1 ){
                                $latest = count($tmp)-1;
                                $tmp = $html->find($testItem['xpath'], $latest);
                              }
                            }

                            if( !empty($tmp) ) {
                              $found = trim ($tmp->plaintext);
                            } else {
                              $found = NULL;
                            }

                            if( strpos($found, $expected) !== FALSE ) {
                                $this->log .= logger::log($testItem['xpath'].' '.$testItem['position'].' is as expected: '.$testItem['expected']);

                            } else {
                                $tmp_error = $testItem['xpath'].' '.$testItem['position'].' is NOT as expected: "'.$testItem['expected']. '". Online is: <xmp style="display:inline-block">"'.$found.'"</xmp>';
                                $this->log .= logger::log($tmp_error, 'error');
                                $errorLogTmp = logger::log('Line: '.$testLineId. ' ' . $tmp_error, 'error');
                                $hasError = TRUE;
                                $this->errorCount++;
                            }
                            break;

                        case 'contentExact':
                            $expected = $testItem['expected'];

                            if( $testItem['position'] != "" ) {
                              $tmp = $html->find($testItem['xpath'], $testItem['position']);
                            } else {
                              $tmp = $html->find($testItem['xpath']); // TODO if position is not set, then take latest }what is not correct - because for meta robots noindex order doesnt matter - worse gets priority
                              if( count($tmp) > 1 ){
                                $latest = count($tmp)-1;
                                $tmp = $html->find($testItem['xpath'], $latest);
                              }
                            }

                            if( !empty($tmp) ) {
                              $found = trim ($tmp->content);
                            } else {
                              $found = NULL;
                            }

                            if( $expected == $found) {
                                $this->log .= logger::log($testItem['xpath'].' '.$testItem['position'].' is as expected: '.$testItem['expected']);

                            } else {
                                $tmp_error = $testItem['xpath'].' '.$testItem['position'].' is NOT as expected: "'.$testItem['expected']. '". Online is: <xmp style="display:inline-block">"'.$found.'"</xmp>';
                                $this->log .= logger::log($tmp_error, 'error');
                                $errorLogTmp = logger::log('Line: '.$testLineId. ' ' . $tmp_error, 'error');
                                $hasError = TRUE;
                                $this->errorCount++;
                            }
                            break;

                        case 'contentContains':
                            $expected = $testItem['expected'];

                            if( $testItem['position'] != "" ) {
                              $tmp = $html->find($testItem['xpath'], $testItem['position']);
                            } else {
                              $tmp = $html->find($testItem['xpath']); // TODO if position is not set, then take latest }what is not correct - because for meta robots noindex order doesnt matter - worse gets priority
                              if( count($tmp) > 1 ){
                                $latest = count($tmp)-1;
                                $tmp = $html->find($testItem['xpath'], $latest);
                              }
                            }

                            if( !empty($tmp) ) {
                              $found = trim ($tmp->content);
                            } else {
                              $found = NULL;
                            }

                            if( strpos($found, $expected) !== FALSE ) {
                                $this->log .= logger::log($testItem['xpath'].' '.$testItem['position'].' is as expected: '.$testItem['expected']);

                            } else {
                                $tmp_error = $testItem['xpath'].' '.$testItem['position'].' is NOT as expected: "'.$testItem['expected']. '". Online is: <xmp style="display:inline-block">"'.$found.'"</xmp>';
                                $this->log .= logger::log($tmp_error, 'error');
                                $errorLogTmp = logger::log('Line: '.$testLineId. ' ' . $tmp_error, 'error');
                                $hasError = TRUE;
                                $this->errorCount++;
                            }
                            break;
                        default:
                          // code...
                          break;
                      }
                  }
              }
          }

          // ERROR LOGS if at least one of tests has error
          if($hasError){
                $this->errorsLog .= logger::log('Line: '.$test['line']. ' URL: '. $test['url'], 'boldInfo');
                $this->errorsLog .= $errorLogTmp;
                $this->errorDebugData[ $test['line'] ]['response'] = $this->downloadedData['response'];
                $this->errorDebugData[ $test['line'] ]['headers'] = $this->downloadedData['headers'];
          }
        }
    } else {
      $this->log .= logger::log('Not testing URLs, because the test is not set up.', 'info');
    }
  }
}
