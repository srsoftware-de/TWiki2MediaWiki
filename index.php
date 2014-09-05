<?php
  include_once "init.php";
  include_once "functions.php";
  include_once "display_functions.php";

  $left="";
  $right="";
  $top="";
  $bottom="";

  $bottom.=display_session();

  if (isset($_SESSION['current']) && isset($_SESSION['current']['namespace']) && $_SESSION['current']['namespace']=='TWiki'){
    $top='Warning! The current namespace is '.$_SESSION['current']['namespace'].' which is a default'.
    ' Mediawiki interwiki namespace. To create pages in that namespace, you first have to remove the '.
    'respective interwiki association. For more information, visit <a href="http://www.mediawiki.org/wiki/Manual:Interwiki">'.
    'http://www.mediawiki.org/wiki/Manual:Interwiki</a>.';
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
    $content=$_POST['submission'];
    if (isset($_POST['submitaddtodo'])){
      $content='[[Category:ConversionToDo]] Converted from '.$_SESSION['source']['url'].'/'.$_SESSION['current']['namespace'].'/'.$_SESSION['current']['page']."\n".$content;
    }
    submit_content($content);
    $top='<iframe src="'.$_SESSION['destination']['url'].'/'.$_SESSION['current']['namespace'].':'.$_SESSION['current']['page'].'"></iframe>';
  }

  if (isset($_SESSION['destination']['url'])){
    if (empty($_SESSION['links_open'])){
      $namespaces=get_destination_namespaces();
      foreach ($namespaces as $namespace){
        $_SESSION['links_open'][$namespace]=array('WebHome');
      }
    }
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
    <?php if (isset($_SESSION['autoscript']) && $_SESSION['autoscript']==true){
      print '<script type="text/javascript" src="autofeed.js"></script>'.PHP_EOL;
    }
    ?>
  </body>
</html>
