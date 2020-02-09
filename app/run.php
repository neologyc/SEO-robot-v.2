<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once __DIR__.'/../vendor/autoload.php';
use Sunra\PhpSimple\HtmlDomParser;


require_once __DIR__.'/Project.php';

require_once __DIR__.'/Logger.php';

require_once __DIR__.'/Downloader.php';

$project = new Project($testid, $projectsSettings, $globalSettings);
$project->runTests();
// show error page

require_once __DIR__.'/Mailer.php';
$mailer = new Mailer($project, $globalSettings, $projectsSettings );
$mailer->send();
