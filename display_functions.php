<?php
  function show_revisions(){
    $revisions=$_SESSION['current']['revisions'];
    if (!empty($revisions)){
      $result='';
      $first=array_pop($revisions);
      foreach ($revisions as $revision){
        $result='<li>'.$revision.'</li>'.PHP_EOL.$result;
      }
      $revisions[]=$first;
      $result='<li><form class="edit_rev" method="POST" action="."><input type="submit" name="revision" value="'.$first.'"></input></form></li>'.PHP_EOL.$result;
      return '<ul class="revisions">'.PHP_EOL.$result.'</ul>'.PHP_EOL;
    }
  }

  function show_submit_form($code){
    $result='<form action="." method="POST" class="submission">'.PHP_EOL;
    $result.='<textarea name="submission">'.$code.'</textarea>'.PHP_EOL;
    $result.='<input type="submit"></form>'.PHP_EOL;
    return $result;
  }

  function ask_for_source(){
    return '<form method="POST" action=".">
      <input type="text" name="source[url]" /> Quell-Wiki (TWiki)<br/>
      <input type="text" name="source[user]" />Benutzername (optional)<br/>
      <input type="password" name="source[password]"/>Passwort (optional)<br/>
      <input type="submit"/>
    </form>';  
  }

  function ask_for_destination(){
    return '<form method="POST" action=".">
      <input type="text" name="destination[url]" /> Ziel-Wiki (Mediawiki)<br/>
      <input type="text" name="destination[user]" />Benutzername (optional)<br/>
      <input type="password" name="destination[password]"/>Passwort (optional)<br/>
      <input type="submit"/>
    </form>';
  }
?>
