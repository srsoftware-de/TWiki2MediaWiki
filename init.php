<?php
  session_start();
  if (isset($_POST['closesession'])){
    session_destroy();
  } 


  if (isset($_POST['source']) && isset($_POST['source']['url'])){
    $_SESSION['source_base']=$_POST['source']['url'];
    if (isset($_POST['source']['user']) && !empty($_POST['source']['user']) && isset($_POST['source']['password']) && !empty($_POST['source']['password'])){
      $_SESSION['source_auth']=stream_context_create(array('http' => array('header'  => "Authorization: Basic " . base64_encode($_POST['source']['user'].':'.$_POST['source']['password']))));
      unset($_POST['source']['password']); 
    }
  }

  if (isset($_POST['destinationwiki'])){
    $_SESSION['destination_base']=$_POST['destinationwiki'];
  }

?>
