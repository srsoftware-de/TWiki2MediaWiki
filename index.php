<?php
  include_once "init.php";
  include_once "functions.php";

  $upperleft="";
  $upperright="";
  $lowerleft="";
  $lowerright="";

  $upperright=add_session_closer();
  $upperright.=display_session();

  if (!isset($_SESSION['source'])) {
    $upperleft=ask_for_source();
  } else {
    $upperleft=display_source();
  }

  if (!isset($_SESSION['destination_base'])){
    $lowerleft=ask_for_destination();
  } else {
    $lowerleft='<pre>'.get_wiki_code().'</pre>';
  }

  if (isset($_SESSION['source'])){
    $upperright.=show_revisions();
    $lowerright.=show_links();
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
