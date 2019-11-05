<?php
// required functions
if(!function_exists('curl_version') || !extension_loaded('curl')) {
  die('Your PHP/webserver doesn´t support CURL. It is a must-have for SEO robot. Please install it, or ask your hosting provider for help.');
}

// LOAD settings
require_once(__DIR__.'/globalSettings.php');
require_once(__DIR__.'/projectsSettings.php');
require_once(__DIR__.'/app/functions.php');

// INIT
if(isset($_GET['id']) && !empty($_GET['id']) ) {
  $testid = htmlspecialchars($_GET['id'], ENT_QUOTES);
  $log = '';
  $hasError = FALSE;
  $globalSettings = new GlobalSettings;
  $projectsSettings = new ProjectsSettings($globalSettings);

  require_once(__DIR__.'/app/run.php');

} else { // EMPTY DOCUMENT
  require_once(__DIR__.'/frontpage.php');
}
