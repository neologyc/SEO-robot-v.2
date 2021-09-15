<?php

// PROJECTS SETTINGS

class ProjectsSettings
{
  public $robotsDir = '';
  public $testsDir = '';
  public $tests = NULL;

  function __construct($gs)
  {
    $this->robotsDir = $gs->robotsDir;
    $this->testsDir = $gs->testsDir;

    // EDIT PROJECTS HERE
    $this->tests =
    array(
        'jarda' => array(
            'robotsTxtURL'                => 'https://www.firmy.cz/robots.txt', // set to FALSE if you don´t want to test robots.txt
            'robotsTxtLocalFilename'      => $gs->robotsDir.'firmz.txt', // set to FALSE if you don´t want to test robots.txt
            'testRules'                   => $gs->testsDir.'firmy.txt',
            'curl_useragent'              => 'Googlebot SEO monitoring - Jaroslav.hlavinka@firma.seznam.cz',
            'notificationEmail'           => 'email@seznam.cz;email@gmail.com',
            //'notificationEmail'         => $gs->defaultEmailForNotifications,
            // use semicolon to delimit more than one email
        ),
        // ADD NEW PROJECTS HERE

        /* // UNCOMMENT
        'jarda' => array(
            'robotsTxtURL'                => 'https://www.firmy.cz/robots.txt', // set to FALSE if you don´t want to test robots.txt
            'robotsTxtLocalFilename'      => $gs->robotsDir.'firmz.txt', // set to FALSE if you don´t want to test robots.txt
            'testRules'                   => $gs->testsDir.'firmy.txt',
            'curl_useragent'              => 'Googlebot SEO monitoring - Jaroslav.hlavinka@firma.seznam.cz',
            'notificationEmail'           => 'email@seznam.cz;email@gmail.com',
            //'notificationEmail'         => $gs->defaultEmailForNotifications,
            // in case you want different email
            // use semicolon to delimit more than one email
        ),
        */



     );
  }
}
