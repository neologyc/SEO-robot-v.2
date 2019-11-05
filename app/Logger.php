<?php

/**
 *
 */
class Logger
{

  /**
   * logging errors
   *
   * @param string $text Description of an error
   * @param string $type
   * @return html string
   */
  public static function log($text, $type = "ok")
  {
      $htmlLog = '';
      if ( $type == "ok" ) {
          $htmlLog = "<span style='color:lime;font-weight:bold'>OK: </span><span>".$text."</span><br>";
      } else if ( $type == "error") {
          $htmlLog = "<span style='color:red;font-weight:bold'>ERROR: </span><span>".$text."</span><br>";
      } else if ( $type == "notice") {
          $htmlLog = "<span style='color:orange;font-weight:bold'>NOTICE: </span><span>".$text."</span><br>";
      } else if ( $type == "boldInfo") {
          $htmlLog = "<br><b>".$text."</b><br>";
      } else if ( $type == "info") {
          $htmlLog = "".$text."<br>";
      }
      return $htmlLog;
  }
}
