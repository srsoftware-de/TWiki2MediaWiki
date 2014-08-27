<?php

  function pageDone(){
    $namespace=$_SESSION['current']['namespace'];
    $page=$_SESSION['current']['page'];
    if (!isset($_SESSION['links_done'])){
      $_SESSION['links_done']=array();
    }
    if (!isset($_SESSION['links_done'][$namespace])){
      $_SESSION['links_done'][$namespace]=array();
    }
    if (!in_array($page,$_SESSION['links_done'][$namespace])){
      $_SESSION['links_done'][$namespace][]=$page;
    }
    if(($key = array_search($page, $_SESSION['links_open'][$namespace])) !== false) {
      unset($_SESSION['links_open'][$namespace][$key]);
    }
    if (empty($_SESSION['links_open'][$namespace])){
      unset($_SESSION['links_open'][$namespace]);
    }
  }

  function addLink($link,$namespace=NULL){
    if ($namespace==NULL){
      $keys=array('/','.');
      $link=str_replace($keys,':',$link);
      $parts=explode(':',$link);
      if (count($parts)>2){
        return;
        die('Link "'.$link.'" enthält mehr als 2 Teile!?');
      }
      if (count($parts)>1){
        $namespace=$parts[0];
        $link=$parts[1];
      }
    }

    $link=preg_replace('/[#&\?].+/','//',$link);


    if (isset($_SESSION['links_done']) && isset($_SESSION['links_done'][$namespace]) && in_array($link,$_SESSION['links_done'][$namespace])){
      // dieser Link wurde schon abgearbeitet
    } else {
      if (!isset($_SESSION['links_open'])){
        $_SESSION['links_open']=array();
      }
      if (!isset($_SESSION['links_open'][$namespace])){
        $_SESSION['links_open'][$namespace]=array();
      }
      if (!in_array($link,$_SESSION['links_open'][$namespace])){
        $_SESSION['links_open'][$namespace][]=$link;
      }
    }
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
      $result=file_get_contents($url,false,$auth).PHP_EOL.$url;
    } else {
      $result=file_get_contents($url);
    }
    return mb_convert_encoding($result,'UTF-8',mb_detect_encoding($result,'UTF-8, ISO-8859-1', true));
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
        $part=trim(substr($part,0,$pos));
        if ($part==''){
          continue;
        }
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
    $nolinks=preg_replace("/\[(.*?)\]/",'',$wikisource); // ignore links in square brackets
    $nolinks=preg_replace("/\{(.*?)\}/",'',$nolinks); // ignore links in curly brackets
    $nolinks=preg_replace("/(&lt;nop&gt;.*?)\s/",'',$nolinks); // ignore links with <nop>InFront
    $alphanumeric=preg_replace("/[^A-Za-z0-9. ]/", ' ', $nolinks);
    $word_source=str_replace(array("\r\n","\r","\n",'/'),' ',$alphanumeric);
    $words=explode(' ',$word_source);
    $map=array();
    foreach ($words as $word){
      if (strtoupper($word)==$word){
        continue; // all uppercase = no camelcase
      }
      $word=trim($word,'.');
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
          if (strpos($word,'.')!==false){
            addLink($word);
          } else{
            $map[$word]='[['.$_SESSION['current']['namespace'].':'.$word.'|'.$word.']]';
            addLink($word,$_SESSION['current']['namespace']);
          }
        }
      }
    }
    $dummy=array();
    foreach ($map as $key => $entry){
      $len=strlen($key);
      if (!isset($dummy[$len])){
        $dummy[$len]=array();
      }
      $dummy[$len][$key]=$entry;
    }
    krsort($dummy);
    $map=array();
    foreach ($dummy as $len => $entries){
      foreach ($entries as $key => $entry){
        $map[$key]=$entry;
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
      if (trim($line)=='*'){
        continue;
      }
      $line=rtrim($line);
      if (strpos($line,'   * ')===0){ 
        $line=substr($line,3);
      }
      if (strpos($line,'      * ')===0){
        $line='*'.substr($line,6);
      }
      if (strpos($line,'         * ')===0){
        $line='**'.substr($line,9);
      }
      if (strpos($line,'            * ')===0){
        $line='***'.substr($line,12);
      }
      
      if ((trim($line)=='') && (strpos(end($new_lines),'* ')===0)){
        // current line is empty, last line was an item, so empty line needs to be skipped
      } else {
        $new_lines[]=$line;
      }
    }
    return implode("\n",$new_lines);
  }

  function replace_weblinks($source){
    $source=preg_replace('/-- Main.([^ ]+)/',"[[User:$1|$1]]",$source);
    
    $pos=strpos($source,'[[');
    while ($pos!==false){
      $end=strpos($source,']]',$pos)+2;
      $original_link=substr($source,$pos,$end-$pos);
      $mid_pos=strpos($original_link,'][');
      if ($mid_pos!==false){
        if (strpos($original_link,':/')!==false){
          $new_link=str_replace('][',' ',$original_link);
          $new_link=substr($new_link,1,-1);
        } else {
          $original_link_dest=substr($original_link,0,$mid_pos);
          $original_link_dest=str_replace('.',':',substr($original_link_dest,2));
          if (strpos($original_link_dest,':')===false){
            $original_link_dest=$_SESSION['current']['namespace'].':'.$original_link_dest;
          }
          $original_link_text=substr($original_link,$mid_pos+2);
          addLink($original_link_dest);
          $new_link='[['.$original_link_dest.'|'.$original_link_text;
        }
        $source=substr($source,0,$pos) . $new_link . substr($source,$end);
      }
      $pos=strpos($source,'[[',$end);
    }
    return $source;
  }

  function replace_formats($source){

    $source=preg_replace("/\*([^\s*])/","'''$1",$source); // * followed by non-white-space
    $source=preg_replace("/([^\s*])\*/","$1'''",$source); // non-white-space followed by *
    
    $source=preg_replace("/__([^ ])/","'''''$1",$source);
    $source=preg_replace("/([^ ])__/","$1''''''",$source);
    
    $source=preg_replace("/_([^ ])/","''$1",$source);
    $source=preg_replace("/([^ ])_/","$1''",$source);




    $source=str_replace('&lt;nop&gt;','',$source); // <nop>
    $source=str_replace('%TOC%','__TOC__',$source); // %TOC%
    return $source;
    
  }

  function  replace_includes($source){
    $key='%INCLUDE{';
    $pos=strpos($source,$key);
    while ($pos!==false){
      $end=strpos($source,'}%',$pos)+2;
      $link=substr($source,$pos+9,$end-$pos-11);
      $link=trim($link,'"');
      addLink($link);
      $source=substr($source,0,$pos).'{{:'.$link.'}}'.substr($source,$end);
      $pos=strpos($source,$key);
    }
    return $source;
  }

  function convert_tables($source){
    $lines=explode("\n",$source);
    $in_table=false;
    for ($i=0;$i<count($lines);$i++){
      $line=trim($lines[$i]);
      if (substr($line,0,1) == '|' && substr($line,-1,1) == '|'){
        $line='|'.str_replace('|','||',substr($line,1,-1))."\n|-";
        if (!$in_table) {
          $line='{| class="wikitable"'."\n".$line;
        }
        $in_table=true;
        $lines[$i]=$line;
      } else {
        if ($in_table){
          $lines[$i-1]=substr($lines[$i-1],0,-1).'}';
        }
        $in_table=false;
      }
    }
    return implode("\n",$lines);
  }

  function replace_codes($source){
    $source=preg_replace("/([^ ])=/","$1</code>",$source);
    $source=preg_replace("/=([^ ])/","<code>$1",$source);
    $source=str_replace('<code></code>','',$source); // cleanup 
    return $source;
  }

  function convert_t2m($source){
    $replace=array('&#037;'=>'%',
                   '&lt;BR\&gt;'=>'<br/>',
                   '%WIKITOOLNAME%'=>'[[TWiki]]',
                   '%HOMETOPIC%'=>$_SESSION['current']['namespace'].$_SESSION['current']['page'],
    							 '%WIKIPREFSTOPIC%'=>'TWikiPreferences',
                   '%TWIKIWEB%.'=>'TWiki:',
                   '%TOPIC%'=>$_SESSION['current']['page'],
                   '%WEB%'=>'[[:Category:'.$_SESSION['current']['namespace'].']]');
    $source=str_replace(array_keys($replace),$replace,$source);
    $source=replace_weblinks($source);
    $camelCaseLinks=read_camel_links($source);
    $altered_source=str_replace(array_keys($camelCaseLinks),$camelCaseLinks,$source);
    $altered_source=replace_codes($altered_source);
    $altered_source=replace_headings($altered_source);
    $altered_source=replace_lists($altered_source);
    $altered_source=replace_formats($altered_source);
    $altered_source=replace_includes($altered_source);
    $altered_source=convert_tables($altered_source);
    $altered_source.="\n".'[[Category:'.$_SESSION['current']['namespace'].']]';
    $replace=array('%YELLOW%'=>'<font color="yellow">',
                   '%ORANGE%'=>'<font color="orange">',
                   '%RED%'=>'<font color="red">',
                   '%PINK%'=>'<font color="pink">',
                   '%PURPLE%'=>'<font color="purple">',
                   '%TEAL%'=>'<font color="teal">',
                   '%NAVY%'=>'<font color="navy">',
                   '%BLUE%'=>'<font color="blue">',
                   '%AQUA%'=>'<font color="aqua">',
                   '%LIME%'=>'<font color="lime">',
                   '%GREEN%'=>'<font color="green">',
                   '%OLIVE%'=>'<font color="olive">',
                   '%MAROON%'=>'<font color="maroon">',
                   '%BROWN%'=>'<font color="brown">',
                   '%BLACK%'=>'<font color="black">',
                   '%GRAY%'=>'<font color="gray">',
                   '%SILVER%'=>'<font color="silver">',
                   '%WHITE%'=>'<font color="white">',
                   '%ENDCOLOR%'=>'</font>',
                   '<verbatim>'=>'<pre>',
                   '</verbatim>'=>'</pre>');
    $altered_source=str_replace(array_keys($replace),$replace,$altered_source);
    return $altered_source;
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
