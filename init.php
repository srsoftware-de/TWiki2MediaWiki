<?php
  session_start();
  if (isset($_POST['closesession'])){
    session_destroy();
  } 


  if (isset($_POST['source'])){
    $_SESSION['source']=$_POST['source'];


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $_SESSION['source']['url']);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0');
    curl_setopt($ch, CURLOPT_HEADER  ,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_USERPWD, $_SESSION['source']['user'] . ":" . $_SESSION['source']['password']);
    $content = curl_exec($ch);

    // get cookies
    $cookies = array();
    preg_match_all('/Set-Cookie:(?<cookie>\s{0,}.*)$/im', $content, $cookies);
    $_SESSION['source']['cookies']=$cookies;
  }

  if (isset($_POST['destinationwiki'])){
    $_SESSION['destination_base']=$_POST['destinationwiki'];
  }

?>
