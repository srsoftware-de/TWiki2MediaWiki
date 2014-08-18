<?php
  include_once 'functions.php';
  session_start();
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

    /* prepare to recieve cookie from source wiki */
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $_SESSION['source']['url']);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0');
    curl_setopt($ch, CURLOPT_HEADER  ,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_USERPWD, $_SESSION['source']['user'] . ":" . $_SESSION['source']['password']);
    $content = curl_exec($ch);

    // actually recieve cookies
    $cookies = array();
    preg_match_all('/Set-Cookie:(?<cookie>\s{0,}.*)$/im', $content, $cookies);
    $_SESSION['source']['cookies']=$cookies;
    $url=$_SESSION['source']['url'];
    $namespace=basename(dirname($url));
    $link=basename($url);
    $_SESSION['source']['url']=dirname(dirname($url)); // this is rather bad and only works, with you start with http://server.com/path/to/wiki/namespace/some_page
    if (!isset($_SESSION['links_open']['namespace'])){
      $_SESSION['links_open'][$namespace]=array();
    }
    $_SESSION['links_open'][$namespace][]=$link;
  }
  
  if (isset($_POST['destination'])){
    /* store destination variables */
    $_SESSION['destination']=$_POST['destination'];
    $_SESSION['destination']['cookies']=tempnam('/tmp','mediawiki');

    /* recive login token from wiki login page */

    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL, $_SESSION['destination']['url'].'?title=Special:UserLogin');
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0');
    curl_setopt($ch, CURLOPT_HEADER  ,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $_SESSION['destination']['cookies']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $_SESSION['destination']['cookies']);

    $content = curl_exec($ch);

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
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $_SESSION['destination']['url'].'?title=Special:UserLogin&action=submitlogin&type=login');
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0');
    curl_setopt($ch, CURLOPT_HEADER  ,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_USERPWD, $_SESSION['destination']['user'] . ":" . $_SESSION['destination']['password']);
    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $_SESSION['destination']['cookies']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $_SESSION['destination']['cookies']);
    curl_exec($ch);
  }

  if (isset($_POST['edit'])){
    $_SESSION['current']=array();
    $_SESSION['current']['namespace']=dirname($_POST['edit']);
    $_SESSION['current']['page']=basename($_POST['edit']);
    $_SESSION['current']['revisions']=read_revisions();
  }

  if (isset($_POST['revision'])){
    array_pop($_SESSION['current']['revisions']);
  }
?>
