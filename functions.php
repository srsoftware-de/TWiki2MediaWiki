<?php
  function ask_for_source(){
    global $upperleft;
    $upperleft='<form method="POST" action=".">
      <input type="text" name="sourcewiki" /> Quell-Wiki (TWiki)
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
    $upperleft='<iframe src="'.$_SESSION['source_base'].'">Ihr Browser scheint keine IFrames zu unterstützen.</iframe>';
  }
?>
