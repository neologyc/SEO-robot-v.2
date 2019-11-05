<?php
/**
 *
 */
class GlobalSettings
{
	public $curl_useragent = 'SEO testing robot - made with ♥ by Jaroslav Hlavinka jaroslav@hlavinka.cz';

	// sending email SMTP settings
	public $emailHost = 'smtp.gmail.com';
	public $emailPort = 587;
	public $emailSMTPSecure = '';
	public $emailSMTPAuth = TRUE;
	public $emailUsername = 'email@gmail.com';
	public $emailPassword = 'yourpass';
	public $emailFrom = 'email@gmail.com';
	public $smtpDebug = FALSE; // TRUE (debug on) or FALSE (debug off)

	public $robotsDir = './projects_settings/robots.txt/'; // you don´t need to change this typically
	public $testsDir = './projects_settings/tests/'; // you don´t need to change this typically
	public $debug = FALSE; // SET to TRUE, to debug
	public $defaultEmailForNotifications = 'email@gmail.com'; // default email - it can be overwritten in custom tests settings
	public static $curl_max_retries = 5;

	function __construct()
	{
		// GENERAL SETTINGS
		define('MAX_FILE_SIZE', 100000000); // DONT change this if you have enough memory limit > 128MB
		ini_set('max_execution_time', 5*60); // max runtime

		// DEBUGGING SETTINGS
		if ( $this->debug === TRUE ) {
			ini_set('display_errors', 1);
			error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
		} else {
		  ini_set('display_errors', 0);
		  error_reporting(0);
		}
	}

}
