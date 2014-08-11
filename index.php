<?php
  include_once "init.php";
  include_once "functions.php";

  $upperleft="";
  $upperright="";
  $lowerleft="";
  $lowerright="";

  add_session_closer();
  display_session();

  if (!isset($_SESSION['source_base'])) {
    ask_for_source();
  } else {
    display_source();
  }

  if (!isset($_SESSION['destination_base'])){
    ask_for_destination();
  } else {
    display_destination();
  }

?>


<html>
  <head>
    <title>TWiki to Mediawiki converter</title>
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
    <div class="upper left half"><?php print $upperleft;?></div>
    <div class="upper right half"><?php print $upperright; ?></div>
    <div class="lower left half"><?php print $lowerleft; ?></div>
    <div class="lower right half"><?php print $lowerright; ?></div>
  </body>
</html>
