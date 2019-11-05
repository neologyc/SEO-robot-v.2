<?php
/**
 *
 */
class CustomError
{
  public $message = '';
  public $note = '';

  function __construct  ( $message, $note = NULL )
  {
    $this->message = $message;
    $this->$note = $note;

    self::returnError();
  }

  function returnError() {
    echo '<h1>Error</h1>';
    echo '<h2>'.$this->message.'</h2>';
    echo '<p>'.$this->note.'</p>';
    die();

  }
}
