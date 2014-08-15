<?php
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
      <input type="text" name="destinationwiki" /> Ziel-Wiki (Mediawiki)
      <input type="submit"/>
    </form>';
  }

  function display_source(){
    return '<iframe src="'.$_SESSION['source']['url'].'">Ihr Browser scheint keine IFrames zu unterstützen.</iframe>';
  }

  function get_wiki_code(){
    $full_code=source_code('edit');
    $pos=strpos($full_code,'textarea');
    if ($pos){
      $pos=strpos($full_code,'>',$pos);
      $part_code=substr($full_code,$pos+1);
      $pos=strpos($part_code,'textarea');
      if ($pos){
        $result=substr($part_code,0,$pos-2);
        return $result;
      }
    }
    return $full_code;

  }

  /* fetches a page by url, replaces the "view" part by the action token, if given */
  function source_code($action=null,$use_auth=true){
    $url=$_SESSION['source']['url'];
    if ($action!=null){
      $url=str_replace('view',$action,$url);
    }
    if ($use_auth && isset($_SESSION['source']['user'])){
      $header='Authorization: Basic '.base64_encode($_SESSION['source']['user'].':'.$_SESSION['source']['password']).PHP_EOL.'Cookie: '.$_SESSION['source']['cookies']['cookie'][0];
      $auth=stream_context_create(array(
          'http' => array('header'  => $header )
          ));
      return file_get_contents($url,false,$auth).PHP_EOL.$url;
    } else {
      return file_get_contents($url);
    }
  }

  function display_destination(){
    return '<iframe src="'.$_SESSION['destination_base'].'">Ihr Browser scheint keine IFrames zu unterstützen.</iframe>';
  }

  function add_session_closer(){
    return '<form method="POST" action=".">
      <input type="submit" name="closesession" value="neue Session" />
    </form>';
  }

  function display_session(){
    $result='Session:<pre>'.print_r($_SESSION,true).'</pre>';
    $result.='POST:<pre>'.print_r($_POST,true).'</pre>';
    return $result;
  }

  /* parses the revision diff site for old revision numbers */
  function show_revisions(){
    $source=source_code('rdiff');
    $parts=explode("rev=",$source);
    $current=true;
    $result='<div class="flowleft"><ul>'.PHP_EOL;
    foreach ($parts as $part){
      if (!$current){
        $pos=strpos($part,'"');
        $part=substr($part,0,$pos);
        $result.='<li>'.$part.'</li>'.PHP_EOL;
        if ($part=='r1.1'){
          break;
        }
      }
      $current=false;
    }
    $result.='</ul></div>'.PHP_EOL;
    return $result;
  }

  function read_links($wikisource){
    $alphanumeric=preg_replace("/[^A-Za-z0-9 ]/", ' ', $wikisource);
    $word_source=str_replace(array("\r\n","\r","\n"),' ',$alphanumeric);
    $words=explode(' ',$word_source);
    $map=array();
    foreach ($words as $word){
      $camel=False;
      $len=strlen($word);
      if ($len>1){
        $lc=strtolower($word);
        for ($i=1; $i<$len; $i++){
          if ($word[$i]!=$lc[$i]){
            $camel=True;
            break;
          }
        }
        if ($camel){
          $map[$word]='[['.$word.']]';
        }
      }
    }
    return $map;
  }

  function show_links(){
    $source=get_wiki_code();
    $links=read_links($source);
    $altered_source=str_replace(array_keys($links),$links,$source);
    return '<pre>'.print_r($altered_source,true).'</pre>';
  }
?>
