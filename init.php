<?php
  session_start();

  if (isset($_POST['sourcewiki'])){
    $_SESSION['source_base']=$_POST['sourcewiki'];
  }
?>
