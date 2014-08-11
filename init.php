<?php
  session_start();
  if (isset($_POST['closesession'])){
    session_destroy();
  } 


  if (isset($_POST['source'])){
    $_SESSION['source']=$_POST['source'];
    $ckfile = tempnam ("/tmp", "CURLCOOKIE");
    echo $_SESSION['source']['url'];
    $ch = curl_init ($_SESSION['source']['url']);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $_SESSION['source']['user'] . ":" . $_SESSION['source']['password']);
    $output = curl_exec($ch);
    echo file_get_contents($ckfile);
    die();
  }

  if (isset($_POST['destinationwiki'])){
    $_SESSION['destination_base']=$_POST['destinationwiki'];
  }

?>
