<?php
  session_start();
  if (isset($_POST['closesession'])){
    session_destroy();
  } 


  if (isset($_POST['source'])){
    $_SESSION['source']=$_POST['source'];
  }

  if (isset($_POST['destinationwiki'])){
    $_SESSION['destination_base']=$_POST['destinationwiki'];
  }

?>
