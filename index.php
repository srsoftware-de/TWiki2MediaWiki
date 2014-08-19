<?php
  include_once "init.php";
  include_once "functions.php";
  include_once "display_functions.php";

  $left="";
  $right="";
  $top="";
  $bottom="";

  $bottom.=display_session();

  if (isset($_SESSION['destination']['url'])){
    $left.=show_links_open();
  }

  if (isset($_SESSION['current']['revisions'])){
    $right.=show_revisions();
  }

  if (!isset($_SESSION['source'])) {
    $top=ask_for_source();
  } else {
    $left.=add_session_closer();
    if (!isset($_SESSION['destination']['url'])){
      $top=ask_for_destination();
    }
  } 

  if (isset($_POST['revision'])){
    $_SESSION['current']['revision']=$_POST['revision'];
    $revision_code=get_twiki_code($_SESSION['current']['namespace'],$_SESSION['current']['page'],$_SESSION['current']['revision']);
    $top='<pre>'.$revision_code.'</pre>';
    if (strpos($revision_code,'topic does not exist')!==false){
      pageDone();
      $bottom='Diese Seite exisitert nicht und kann deshalb auch nicht übertragen werden!';
    } else {
      $media_wiki_code=convert_t2m($revision_code);
      $bottom=show_submit_form($media_wiki_code);
    }
  }

  if (isset($_POST['submission'])){
    submit_content($_POST['submission']);
    $top='<iframe src="'.$_SESSION['destination']['url'].'/'.$_SESSION['current']['namespace'].':'.$_SESSION['current']['page'].'"></iframe>';
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
    <meta http-equiv="Content-Type" content="text/html; charset=utf8" />
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
