<?php

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
    $result= $full_code;
    if ($pos){
      $pos=strpos($full_code,'>',$pos);
      $part_code=substr($full_code,$pos+1);
      $pos=strpos($part_code,'textarea');
      if ($pos){
        $result=substr($part_code,0,$pos-2);
        $result=$result;
      }
    }
    return $result;
  }

  /* fetches a page by url */
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
    return '<iframe src="'.$_SESSION['destination']['url'].'">Ihr Browser scheint keine IFrames zu unterstützen.</iframe>';
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
          $map[$word]='[['.$_SESSION['current']['namespace'].':'.$word.'|'.$word.']]';
        }
      }
    }
    return $map;
  }

  function replace_headings($source){
    $lines=explode("\n",$source);
    for ($i=0; $i<count($lines); $i++){
      $line=trim($lines[$i]);
      if (strpos($line,'---')===0){ // line starts with three dashes
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

  function replace_lists($source){
    $lines=explode("\n",$source);
    $new_lines=array();
    foreach ($lines as $line){
      $line=rtrim($line);
      if (strpos($line,'   * ')===0){
        $line=substr($line,3);
      }
      if ((trim($line)=='') && (strpos(end($new_lines),'* ')===0)){
        // current line is empty, last line was an item, so empty line needs to be skipped
      } else {
        $new_lines[]=$line;
      }
    }
    return implode("\n",$new_lines);
  }

  function convert_t2m($source){
    $replace=array('&#037;'=>'%',
                   '%WIKITOOLNAME%'=>'TWiki',
                   '%WEB%'=>'[['.$_SESSION['current']['namespace'].']]');
    $source=str_replace(array_keys($replace),$replace,$source);
    $camelCaseLinks=read_camel_links($source);    
    $altered_source=str_replace(array_keys($camelCaseLinks),$camelCaseLinks,$source);
    $altered_source=replace_headings($altered_source);
    $altered_source=replace_lists($altered_source);
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

  function getInputs($wiki,$form){
    $url=$wiki['url'].$form;
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0');
    curl_setopt($ch, CURLOPT_HEADER  ,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
    curl_setopt($ch, CURLOPT_USERPWD, $wiki['user'] . ":" . $wiki['password']);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $wiki['cookies']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $wiki['cookies']);
    $content = curl_exec($ch);

    $inputs=array();
    
    /* search for regular inputs */

    $key='<input';
    $pos=strpos($content,$key);
    while ($pos !== false){
      $name_start=strpos($content,'name="',$pos)+6;
      $name_end=strpos($content,'"',$name_start);

      $value_start=strpos($content,'value="',$pos)+7;
      $value_end=strpos($content,'"',$value_start);
      
      $value='';
      $name=substr($content,$name_start,$name_end-$name_start);
      $value=substr($content,$value_start,$value_end-$value_start);

      $inputs[$name]=$value;

      $pos=strpos($content,$key,$pos+1);
    }

    /* search for textareas */
    
    $key='<textarea';
    $pos=strpos($content,$key);
    while ($pos !== false){
      $name_start=strpos($content,'name="',$pos)+6;
      $name_end=strpos($content,'"',$name_start);
      $name=substr($content,$name_start,$name_end-$name_start);
      $inputs[$name]='';
      $pos=strpos($content,$key,$pos+1);
    }
    
    return $inputs;
  }

  function submit_content($content){

    /* use data from preset input fields */
    $data=getInputs($_SESSION['destination'],'?action=submit&title='.$_SESSION['current']['namespace'].':'.$_SESSION['current']['page']); 

    $data['wpTextbox1']=$content; // apply new content to textbox
    $data['wpSummary']='Revision '.$_SESSION['current']['revision'];

    /* omit some input fields that belong to unused buttons or search fields */
    unset($data['wpMinoredit']);
    unset($data['wpWatchthis']);
    unset($data['wpPreview']);
    unset($data['wpDiff']);
    unset($data['search']);
    unset($data['title']);
    unset($data['fulltext']);
    unset($data['go']);

    $postdata = http_build_query($data);

    $ch = curl_init();
    $url=$_SESSION['destination']['url'].'?action=submit&title='.$_SESSION['current']['namespace'].':'.$_SESSION['current']['page'];
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0');
    curl_setopt($ch, CURLOPT_HEADER  ,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_USERPWD, $_SESSION['destination']['user'] . ":" . $_SESSION['destination']['password']);
    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $_SESSION['destination']['cookies']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $_SESSION['destination']['cookies']);
    $content = curl_exec($ch);
    $content=curl_error($ch).PHP_EOL.$content;
    return $content;
  }
?>
