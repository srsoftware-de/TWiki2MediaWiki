<?php

include_once 'converter.php';

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

function addLinks($mediawiki_source){
	$treffer=array();
	preg_match_all("/\[\[[^\]]+\]\]/",$mediawiki_source,$treffer);
	foreach ($treffer[0] as $num => $link){
		$link=preg_replace('/\[\[:?([^\]]+)\]\]/',"$1",$link);
		if (strpos($link,'Category:')!==0){
			addLink($link);				
		}
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

function get_twiki_code($namespace,$page,$revision=null){
	$url=$_SESSION['source']['url'].'/'.$namespace.'/'.$page.'?raw=on';
	if ($revision!=null){
		$url.='&rev='.$revision;
	}
	$full_code=source_code($url,$_SESSION['source']);
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
function source_code($url,$auth=null,$postData=null){
	if ($auth!=null && isset($auth['user'])){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0');
		//      curl_setopt($ch, CURLOPT_HEADER  ,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, $auth['user'] . ":" . $auth['password']);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $auth['cookies']);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $auth['cookies']);
		if ($postData!=null){
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);
		}
		$result=curl_exec($ch);
	} else {
		$result=file_get_contents($url);
	}
	return mb_convert_encoding($result,'UTF-8',mb_detect_encoding($result,'UTF-8, ISO-8859-1', true));
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
			$pos=min(strpos($part,'"'),strpos($part,'&'));

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

	$url=$_SESSION['destination']['url'].'?action=submit&title='.$_SESSION['current']['namespace'].':'.$_SESSION['current']['page'];
	$content=source_code($url,$_SESSION['destination'],$postdata);
	return $content;
}

function get_source_namespaces(){
	$source=source_code($_SESSION['source']['url'],$_SESSION['source']);
	$pos=strpos($source,'<strong>TWiki Webs</strong>');
	$source=substr($source,$pos);
	$pos=strpos($source,'<ul>');
	$source=substr($source,$pos);
	$pos=strpos($source,'</ul>');
	$source=strip_tags(substr($source,0,$pos+5));
	$namespaces=explode("\n",$source);
	$list='<div class="flowLeft"><ul>'.PHP_EOL;
	$code='<div class="flowLeft">'.t('Use te following settings in LocalSettings to add all these name spaces:').'<br/><code>'.PHP_EOL;
	$num=100;
	foreach ($namespaces as $namespace){
		$namespace=trim($namespace);
		if (!empty($namespace)){
			$list.='<li>'.$namespace.'</li>'.PHP_EOL;
			$code.='$wgExtraNamespaces['.$num.']=\''.$namespace.'\';<br/>'.PHP_EOL;
			$num+=1;
			$code.='$wgExtraNamespaces['.$num.']=\''.$namespace.'_talk\';<br/>'.PHP_EOL;
			$num+=1;
		}
	}
	$list.='</ul></div>'.PHP_EOL;
	$code.='</code></div>'.PHP_EOL;
	return $list.$code;
}

function get_destination_namespaces(){
	$source=source_code($_SESSION['destination']['url'].'api.php?action=query&meta=siteinfo&siprop=namespaces&format=xml',$_SESSION['destination']);
	$source=strip_tags(str_replace('</ns>',"\n",$source));
	$namespace_candidates=explode("\n",$source);
	$namespaces=array();
	foreach ($namespace_candidates as $candidate){
		$candidate=trim($candidate);
		if ($candidate==''){
			continue;
		}
		if (strpos($candidate,'talk') === false){
			$namespaces[]=$candidate;
		}
	}
	sort($namespaces);
	return $namespaces;
}
?>
