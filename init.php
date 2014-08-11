<?php
  session_start();
  if (isset($_POST['closesession'])){
    session_destroy();
  } 


  if (isset($_POST['sourcewiki'])){
    $_SESSION['source_base']=$_POST['sourcewiki'];
  }

  if (isset($_POST['destinationwiki'])){
    $_SESSION['destination_base']=$_POST['destinationwiki'];
  }

?>
