<?php
  include_once 'functions.php';
  session_start();
  include_once 'lang.php';

  $left="";
  $right="";
  $top="";
  $bottom="";

  if (isset($_POST['closesession'])){
    session_destroy();
  } 

  if (!isset($_SESSION['links_open'])){
    $_SESSION['links_open']=array();
  }

  if (!isset($_SESSION['links_done'])){
    $_SESSION['links_done']=array();
  }

  if (isset($_POST['source'])){
    /* store source variables */
    $_SESSION['source']=$_POST['source'];
    $_SESSION['source']['cookies']=tempnam('/tmp','twiki');
    source_code($_SESSION['source']['url'],$_SESSION['source']); // get cookies
  }
  
  if (isset($_POST['destination']) && !empty($_POST['destination']['url'])){
    /* store destination variables */
    $url=$_POST['destination']['url'];
    $needle='index.php';
    if (substr($url,-strlen($needle))===$needle){
      $url=substr($url,0,-strlen($needle));
      $_POST['destination']['url']=$url;
    }
    $_SESSION['destination']=$_POST['destination'];
    $_SESSION['destination']['cookies']=tempnam('/tmp','mediawiki');

    /* recive login token from wiki login page */

    $content=source_code($_SESSION['destination']['url'].'?title=Special:UserLogin',$_SESSION['destination']);

    $content=explode("\n",$content);

    foreach ($content as $line){
      if (strpos($line,'wpLoginToken')>0){
        $start=strpos($line,'value="')+7;
        $end=strpos($line,'"',$start);
        $token=substr($line,$start,$end-$start);
        break;
      }
    }

    $data=array('wpName'=>$_SESSION['destination']['user'],
                'wpPassword'=>$_SESSION['destination']['password'],
                'wpRemember'=>'1',
                'wpLoginToken'=>$token);
    $postdata = http_build_query($data);

    /* recieve cookie from destination wiki */
    source_code($_SESSION['destination']['url'].'?title=Special:UserLogin&action=submitlogin&type=login',$_SESSION['destination'],$postdata);
  }

  if (isset($_POST['edit'])){
    $parts=explode(':',$_POST['edit']);
    $_SESSION['current']=array();
    $_SESSION['current']['namespace']=$parts[0];
    $_SESSION['current']['page']=$parts[1];
    $_SESSION['current']['revisions']=read_revisions();
    if (empty($_SESSION['current']['revisions'])){
      pageDone();
      $top=str_replace('<page>','<strong>'.$_SESSION['current']['namespace'].':'.$_SESSION['current']['page'].'</strong>',t('The page <page> could not be found. Skipping it.'));
    }
  }

  if (isset($_POST['revision'])){
    array_pop($_SESSION['current']['revisions']);
    if (empty($_SESSION['current']['revisions'])){
      pageDone();
    }
  }

  if (isset($_GET['auto'])){
    if ($_GET['auto']=='true'){
      $_SESSION['autoscript']=1;
    }
  }
 if (isset($_POST['startAuto'])){
    $_SESSION['autoscript']=1;
  }
  if (isset($_POST['stopAuto'])){
    $_SESSION['autoscript']=0;
  }

?>
