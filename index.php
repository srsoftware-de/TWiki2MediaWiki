<?php
  include_once "init.php";
  include_once "functions.php";

  $left="";
  $right="";
  $top="";
  $bottom="";

//  $upperright=add_session_closer();
  $bottom.=display_session();
  
  if (isset($_SESSION['links_open'])){
    $left.=show_links_open();
  }

  if (!isset($_SESSION['source'])) {
    $top=ask_for_source();
  } else {
    $left.=add_session_closer();
    if (!isset($_SESSION['destination_base'])){
      $top=ask_for_destination();
    }
  } 

//else {
//    $upperleft=display_source();
//  }

//  if (!isset($_SESSION['destination_base'])){
//    $lowerleft=ask_for_destination();
//  } else {
//    $lowerleft='<pre>'.get_wiki_code().'</pre>';
//  }

//  if (isset($_SESSION['source'])){
//    $upperright.=show_revisions();
//    $lowerright.=convert_source();
//  }

?>

<html>
  <head>
    <title>TWiki to Mediawiki converter</title>
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
    <div class="left"><?php print $left;?></div>
    <div class="center">
      <div class="top"><?php print $top; ?></div>
      <div class="bottom"><?php print $bottom; ?></div>
    </div>
    <div class="right"><?php print $right; ?></div>
  </body>
</html>
