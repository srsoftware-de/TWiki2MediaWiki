<?php

  function t($basetext){
    if (!isset($_SESSION['lang'])){
      return $basetext;
    }
    if (!array_key_exists($basetext,$_SESSION['lang'])){
      return $basetext;
    }
    return $_SESSION['lang'][$basetext];
  }

  function loadLanguage($code){
    $filename='translation.'.$code;
    if (!file_exists($filename)){
      return;
    }
    $raw=file($filename);
    $trans=array();
    foreach ($raw as $line){
      $parts=explode('=>',$line);
      $key=trim($parts[0]);
      $val=trim($parts[1]);
      $trans[$key]=$val;
    }
    $_SESSION['lang']=$trans;
  }


if (isset($_GET['lang'])){
  loadLanguage($_GET['lang']);
}
