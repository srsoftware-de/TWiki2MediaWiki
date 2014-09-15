<?php
function convert_t2m($source){
	$replace=array('&#037;'=>'%',
			'&lt;BR\&gt;'=>'<br/>',
			'%WIKITOOLNAME%'=>'TWiki',
			'%HOMETOPIC%'=>$_SESSION['current']['namespace'].$_SESSION['current']['page'],
			'%WIKIPREFSTOPIC%'=>'TWikiPreferences',
			'%TWIKIWEB%'=>'TWiki',
			'%TOPIC%'=>$_SESSION['current']['page'],
			'%WEB%'=>'[[:Category:'.$_SESSION['current']['namespace'].'|'.$_SESSION['current']['namespace'].']]');
	$source=str_replace(array_keys($replace),$replace,$source);
	$source=replace_weblinks($source);
	//$camelCaseLinks=read_camel_links($source);
	//$altered_source=str_replace(array_keys($camelCaseLinks),$camelCaseLinks,$source);
	$altered_source=replace_includes($source);
	$altered_source=replace_camel_links($altered_source);
	$altered_source=replace_anchors($altered_source);
	$altered_source=replace_codes($altered_source);	
	$altered_source=replace_headings($altered_source);
	$altered_source=replace_lists($altered_source);
	$altered_source=replace_formats($altered_source);
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
			'</verbatim>'=>'</pre>',
			'<br>'=>'<br/>');
	$altered_source=str_replace(array_keys($replace),$replace,$altered_source);
	return $altered_source;
}

function replace_camel_links($source){
	$source=preg_replace('/([^A-Za-z0-9:\[])([A-Za-z0-9]+)\.([A-Z][A-Za-z0-9]*[A-Z][A-Za-z0-9]*[a-z][A-Za-z0-9]*)([^A-Za-z0-9])/',"$1[[$2:$3|$3]]$4",$source); // Replace Namespace.CamelCase => [[Namespace:CamelCase]]
	$source=preg_replace('/([^A-Za-z0-9|:\[])([A-Z][A-Za-z0-9]*[A-Z][A-Za-z0-9]*[a-z][A-Za-z0-9]*)([^A-Za-z0-9\}])/',"$1[[".$_SESSION['current']['namespace'].":$2|$2]]$3",$source);
	//prefix:
	// the | is to avoid converting [[Somelink|CamelCase]]
	// the : is to avoid converting [[:SomeCaseCategory:Link]]
	// the [ is to avoid converting [[SomeCategory:Link]]
	//suffix:
	// the } is to avoid converting {{SomeCategory:CamelCase}}
	
	
	
	return $source;
}

// replaces <a name="anchorname">some text</a> by <span id="anchorname">some text</span>
function replace_anchors($source){
	return preg_replace('/&lt;a name="([^"])"&gt;(.*)&lt;\/a&gt;/',"<span id=\"$1\">$2</span>",$source);
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

function replace_weblinks($source){
	$source=preg_replace('|\[\[(https?:/+[^\]]+)\]\[([^\]]+)\]\]|',"[$1 $2]", $source);
	$source=preg_replace('|\[\[file:/*(/[^\]]+)\]\[([^\]]+)\]\]|',"<file>$1 $2</file>", $source);
	return $source;
}


function replace_codes($source){
	//$source=preg_replace("/([^ ])=/","$1</code>",$source);
	//$source=preg_replace("/=([^ ])/","<code>$1",$source);
	$source=preg_replace("/=([^ ].*[^ ])=/","<code>$1</code>",$source); // wirks well with Main:TWikiUsers	
	$source=str_replace('<code></code>','',$source); // cleanup
	return $source;
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
		if (strpos($line,'   1. ')===0){
			$line='#'.substr($line,5);
		}
		if (strpos($line,'      1. ')===0){
			$line='##'.substr($line,6);
		}
		if (strpos($line,'         1. ')===0){
			$line='###'.substr($line,11);
		}
		if (strpos($line,'            1. ')===0){
			$line='####'.substr($line,14);
		}
		
		if ((trim($line)=='') && (strpos(end($new_lines),'* ')===0)){
			// current line is empty, last line was an item, so empty line needs to be skipped
		} else {
			$new_lines[]=$line;
		}
	}
	return implode("\n",$new_lines);
}



function replace_formats($source){
	
	$source=preg_replace('/\*([A-Za-z0-9][^*]*)\*/',"'''$1'''",$source); // *some text* => '''some text'''
	
	$source=preg_replace('/__([A-Za-z0-9][^_]*)__/',"'''''$1'''''",$source); // __some text__ => '''''some text'''''
	
	$source=preg_replace('/_([A-Za-z0-9][^_]*)_/',"''$1''",$source); // _some text_ => ''some text''
		

//	$source=preg_replace("/\*([^\s*])/","'''$1",$source); // * followed by non-white-space
//	$source=preg_replace("/([^\s*])\*/","$1'''",$source); // non-white-space followed by *

//	$source=preg_replace("/__([^ ])/","'''''$1",$source);
//	$source=preg_replace("/([^ ])__/","$1''''''",$source);

//	$source=preg_replace("/_([^ ])/","''$1",$source);
//	$source=preg_replace("/([^ ])_/","$1''",$source);

	$source=str_replace('&lt;nop&gt;','',$source); // <nop>
	$source=str_replace('%TOC%','__TOC__',$source); // %TOC%
	return $source;

}

/*function  replace_includes($source){
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
}*/

function  replace_includes($source){
	return preg_replace('/%INCLUDE\{"([^"]+)"\}%/',"{{:$1}}",$source);
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