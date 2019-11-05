<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>SEO Robot</title>
  </head>
<?php
?>
  <body>
    <h1>SEO robot works!</h1>
    <p>Now you can do these steps:</p>
    <ol>
      <li>Read <a href="https://github.com/neologyc/seo-robot#readme">manual for help</a>.</li>
      <li>Setup general setting in <i>SEO Robot folder</i>/generalSettings.php file.</li>
      <li>Setup first project setting in <i>SEO Robot folder</i>/projectSettings.php file.</li></li>
      <li>Test run the project using URL <i><?php echo $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; ?></i>?id=projectname (project name was setted up in previous step and the name is same as settings of project array key) or <i><?php echo $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; ?></i>index.php?id=projectname (depends on your PHP settings)</li>
      <li>Set cronjob for continual testing of this project.</li>
    </ol>
  </body>
</html>
