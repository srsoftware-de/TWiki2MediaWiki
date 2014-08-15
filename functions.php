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

  function get_twiki_code($namespace,$page,$revision=null){
    $url=$_SESSION['source']['url'].'/'.$namespace.'/'.$page.'?raw=on';
    if ($revision!=null){
      $url.='&rev='.$revision;
    }
    $full_code=source_code($url,true);
    $pos=strpos($full_code,'textarea');
    $result= $url.PHP_EOL.$full_code;
    if ($pos){
      $pos=strpos($full_code,'>',$pos);
      $part_code=substr($full_code,$pos+1);
      $pos=strpos($part_code,'textarea');
      if ($pos){
        $result=substr($part_code,0,$pos-2);
        $result=$url.PHP_EOL.$result;
      }
    }
    return $result;
  }

  /* fetches a page by url, replaces the "view" part by the action token, if given */
  function source_code($url,$use_auth=true){
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
    return '<form class="session_closer" method="POST" action=".">
      <input type="submit" name="closesession" value="neue Session" />
    </form>';
  }

  function display_session(){
    $result='Session:<pre>'.print_r($_SESSION,true).'</pre>';
    $result.='POST:<pre>'.print_r($_POST,true).'</pre>';
    return $result;
  }

  /* parses the revision diff site for old revision numbers */
  function read_revisions(){
    $url=$_SESSION['source']['url'].'/'.$_SESSION['current']['namespace'].'/'.$_SESSION['current']['page'];
    $url=str_replace('view','rdiff',$url);
    $source=source_code($url);
    $parts=explode("rev=",$source);
    $current=true;
    $result=array();
    foreach ($parts as $part){
      if (!$current){
        $pos=strpos($part,'"');
        $part=substr($part,0,$pos);
        $result[]=$part;
        if ($part=='r1.1'){
          break;
        }
      }
      $current=false;
    }
    return $result;
  }

  function read_camel_links($wikisource){
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

  function replace_headings($source){
    $lines=explode("\n",$source);
    for ($i=0; $i<count($lines); $i++){
      $line=trim($lines[$i]);
      if (strpos($line,'---')==0){ // line starts with three dashes
        $line=ltrim($line,'-'); // remove dashes from beginning of line
        $delim="";
        while (strpos($line,'+')===0){ // count heading depth
          $delim.='=';
          $line=substr($line,1);
        }
        $line=$delim.' '.trim($line).' '.$delim; // actually add mediawiki tags
        $lines[$i]=$line;
      }
    }
    return implode("\n",$lines);
  }

  function convert_t2m($source){
    $camelCaseLinks=read_camel_links($source);
    $altered_source=str_replace(array_keys($camelCaseLinks),$camelCaseLinks,$source);
    $altered_source=replace_headings($altered_source);
    return $altered_source;
  }

  function show_links_open(){
    $result='<form class="editlink" method="POST" action="."><ul class="namespace">'.PHP_EOL;
    foreach ($_SESSION['links_open'] as $namespace=>$links){
      $result.='<li>'.$namespace.PHP_EOL.'<ul class="link">'.PHP_EOL;
      foreach ($links as $link){
        $result.='<li><input type="submit" name="edit" value="'.$namespace.'/'.$link.'"></input></li>'.PHP_EOL;
      }
      $result.='</ul>'.PHP_EOL.'</li>'.PHP_EOL;
    }
    $result.='</ul></form>'.PHP_EOL;
    return $result;
  }
?>
