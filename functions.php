<?php
  function ask_for_source(){
    global $upperleft;
    $upperleft='<form method="POST" action=".">
      <input type="text" name="source[url]" /> Quell-Wiki (TWiki)<br/>
      <input type="text" name="source[user]" />Benutzername (optional)<br/>
      <input type="password" name="source[password]"/>Passwort (optional)<br/>
      <input type="submit"/>
    </form>';  
  }

  function ask_for_destination(){
    global $lowerleft;
    $lowerleft='<form method="POST" action=".">
      <input type="text" name="destinationwiki" /> Ziel-Wiki (Mediawiki)
      <input type="submit"/>
    </form>';
  }

  function display_source(){
    global $upperleft;
    if (isset($_SESSION['source_auth'])){
      $upperleft=file_get_contents($_SESSION['source_base'],false,$_SESSION['source_auth']);
    } else {
      $upperleft=file_get_contents($_SESSION['source_base']);
    }
  }

  function display_destination(){
    global $lowerleft;
    $lowerleft='<iframe src="'.$_SESSION['destination_base'].'">Ihr Browser scheint keine IFrames zu unterstützen.</iframe>';
  }

  function add_session_closer(){
    global $lowerright;
    $lowerright='<form method="POST" action=".">
      <input type="submit" name="closesession" value="neue Session" />
    </form>';
  }

  function display_session(){
    global $upperright;
    $upperright='Session:<pre>'.print_r($_SESSION,true).'</pre>';
    $upperright.='POST:<pre>'.print_r($_POST,true).'</pre>';
  }
?>
