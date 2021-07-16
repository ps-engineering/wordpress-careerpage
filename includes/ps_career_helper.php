<?php

function makePluralTables($singularTable){
	if($singularTable === 'city'){
		$pluralTable = 'cities';
	} elseif($singularTable === 'template'){
		$pluralTable = 'templates';
	} elseif($singularTable === 'department'){
		$pluralTable = 'departments';
	} elseif($singularTable === 'positiontype'){
		$pluralTable = 'positiontypes';
	} elseif($singularTable === 'seniority'){
		$pluralTable = 'seniorities';
	} elseif($singularTable === 'instance'){
		$pluralTable = 'instances';
	} elseif($singularTable === 'team'){
		$pluralTable = 'teams';
	} elseif($singularTable === 'industry'){
		$pluralTable = 'industries';
	} else {
		$pluralTable = $singularTable;
	}	
	return $pluralTable;
}


function makeSingularTables($pluralTable){
	if($pluralTable === 'countries'){
		$singularTable = 'country';
	} elseif($pluralTable === 'cities'){
		$singularTable = 'city';
	} elseif($pluralTable === 'templates'){
		$singularTable = 'template';
	} elseif($pluralTable === 'departments'){
		$singularTable = 'department';
	} elseif($pluralTable === 'positiontypes'){
		$singularTable = 'positiontype';
	} elseif($pluralTable === 'seniorities'){
		$singularTable = 'seniority';
	} elseif($pluralTable === 'instances'){
		$singularTable = 'instance';
	} elseif($pluralTable === 'teams'){
		$singularTable = 'team';
	} elseif($pluralTable === 'industries'){
		$singularTable = 'industry';
	} else {
		$singularTable = $pluralTable;
	}
	return $singularTable;
}



function strip_tags_content($text, $tags = '', $invert = FALSE) { 
	//print_r($text);
	
	preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags); 
	$tags = array_unique($tags[1]); 
	
	
	if(is_array($tags) AND count($tags) > 0) { 
		
		if($invert == FALSE) { 
			$text = json_decode( json_encode($text), true);
			$text = preg_replace("/<\/?div[^>]*\>/i", "", $text); 
			$text = str_replace('Array ( )', '', $text);
			if(is_string($text)){ 
				return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text); 
			}
		} else { 
			$text = json_decode( json_encode($text), true);
			$text = preg_replace("/<\/?div[^>]*\>/i", "", $text); 
			$text = str_replace('Array ( )', '', $text);
			if(is_string($text)){ 
				return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
			}

		} 
	} elseif($invert == FALSE) { 
		return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text); 
	} 
	if(is_string($text)){ 
		return $text; 
	}
	
}

function nl2p_html($str) {
	$out = '';
	// If we find the end of an HTML header, assume that this is part of a standard HTML file. Cut off everything including the
	// end of the head and save it in our output string, then trim the head off of the input. This is mostly because we don't
	// want to surrount anything like the HTML title tag or any style or script code in paragraph tags. 
	if(strpos($str,'</head>')!==false) {
		$out=substr($str,0,strpos($str,'</head>')+7);
		$str=substr($str,strpos($str,'</head>')+7);
	}

	// First, we explode the input string based on wherever we find HTML tags, which start with '<'
	$arr=explode('<',$str);

	// Next, we loop through the array that is broken into HTML tags and look for textual content, or
	// anything after the >
	for($i=0;$i<count($arr);$i++) {
		if(strlen(trim($arr[$i]))>0) {
			// Add the '<' back on since it became collateral damage in our explosion as well as the rest of the tag
			$html='<'.substr($arr[$i],0,strpos($arr[$i],'>')+1);

			// Take the portion of the string after the end of the tag and explode that by newline. Since this is after
			// the end of the HTML tag, this must be textual content.
			$sub_arr=explode("\n",substr($arr[$i],strpos($arr[$i],'>')+1));

			// Initialize the output string for this next loop
			$paragraph_text='';

			// Loop through this new array and add paragraph tags (<p>...</p>) around any element that isn't empty
			for($j=0;$j<count($sub_arr);$j++) {
				if(strlen(trim($sub_arr[$j]))>0)
					$paragraph_text.='<p>'.trim($sub_arr[$j]).'</p>';
			}

			// Put the text back onto the end of the HTML tag and put it in our output string
			$out.=$html.$paragraph_text;
		}

	}

	// Throw it back into our program
	return $out;
}


?>