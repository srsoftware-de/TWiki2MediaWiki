<?php
  function show_revisions(){
    $revisions=$_SESSION['current']['revisions'];
    if (!empty($revisions)){
      $result='';
      $first=array_pop($revisions);
      foreach ($revisions as $revision){
        $result='<li>'.$revision.'</li>'.PHP_EOL.$result;
      }
      $result='<li><form class="edit_rev" method="POST" action="."><input type="submit" name="revision" value="'.$first.'"></input></form></li>'.$result;
      return '<ul class="revisions">'.PHP_EOL.$result.'</ul>'.PHP_EOL;
    }
  }
?>
