<?php
  function show_revisions(){
    $revisions=$_SESSION['current']['revisions'];
    if (!empty($revisions)){
      $result='';
      $first=array_pop($revisions);
      if ($first==''){
        return '';
      }
      foreach ($revisions as $revision){
        $result='<li>'.$revision.'</li>'.PHP_EOL.$result;
      }
      $revisions[]=$first;
      $result='<li><form class="edit_rev" method="POST" action="."><input id="first_revision" type="submit" name="revision" value="'.$first.'"></input></form></li>'.PHP_EOL.$result;
      return $_SESSION['current']['namespace'].'/<br/>'.$_SESSION['current']['page'].'<br/><ul class="revisions">'.PHP_EOL.$result.'</ul>'.PHP_EOL;
    }
  }

  function show_submit_form($code){
    $result='<form action="." method="POST" class="submission">'.PHP_EOL;
    $result.='<textarea name="submission">'.$code.'</textarea>'.PHP_EOL;
    $result.='<div class="submits">'.PHP_EOL;
    if (isset($_SESSION['autoscript']) && ($_SESSION['autoscript']==1)){
      $result.='<input type="submit" name="stopAuto" value="'.t('stop automatic submissions').'">'.PHP_EOL;
    } else {
      $result.='<input type="submit" name="startAuto" value="'.t('start automatic submissions').'">'.PHP_EOL;
    }
    $result.='<input id="todo-submit" type="submit" name="submitaddtodo" value="'.t('submit and add TODO').'">'.PHP_EOL;
    $result.='<input id="plain-submit" type="submit"></div></form>'.PHP_EOL;
    return $result;
  }

  function ask_for_source(){
    $result ='<form method="POST" action=".">'.PHP_EOL;
    $result.='<input type="text" name="source[url]" />'.t('Source Wiki (Twiki)').'<br/>'.PHP_EOL;
    $result.='<input type="text" name="source[user]" />'.t('User name (optional)').'<br/>'.PHP_EOL;
    $result.='<input type="password" name="source[password]"/>'.t('Password (optional)').'<br/>'.PHP_EOL;
    $result.='<input type="submit"/>'.PHP_EOL;
    $result.='</form>';
    return $result;
  }

  function ask_for_destination(){
    $result ='<form method="POST" action=".">'.PHP_EOL;
    $result.='<input type="text" name="destination[url]" />'.t('Destination Wiki (Mediawiki)').'<br/>'.PHP_EOL;
    $result.='<input type="text" name="destination[user]" />'.t('User name (optional)').'<br/>'.PHP_EOL;
    $result.='<input type="password" name="destination[password]"/>'.t('Password (optional)').'<br/>'.PHP_EOL;
    $result.='<input type="submit"/>'.PHP_EOL;
    $result.='</form>'.PHP_EOL;
    $result.=t('Note').': '.t('The pages to be transferred will be determined by the namespaes present in the destination MediaWiki.').'<br/>'.PHP_EOL;
    $result.=t('Make sure to <a href="http://www.mediawiki.org/wiki/Manual:Namespace">create approprate namespaces</a> in the destination wiki before going to the next step!').'<br/>'.PHP_EOL;
    $result.=t('The following namespaces are defined in the source TWiki:').'<br/>'.PHP_EOL;
    $result.=get_source_namespaces();
    return $result;
  }

  function show_links_open(){
    $result='<form class="editlink" method="POST" action="."><ul class="namespace">'.PHP_EOL;
    $first=false;
    foreach ($_SESSION['links_open'] as $namespace=>$links){
      $result.='<li>'.$namespace.PHP_EOL.'<ul class="link">'.PHP_EOL;
      foreach ($links as $num => $link){
        $result.='<li><input ';
        if (!$first){
          $first=true;
          $result.='id="first_open_page" ';
        }
        $result.='type="submit" name="edit" value="'.$namespace.':'.$link.'"></input></li>'.PHP_EOL;
      }
      $result.='</ul>'.PHP_EOL.'</li>'.PHP_EOL;
    }
    $result.='</ul></form>'.PHP_EOL;
    return $result;
  }

?>
