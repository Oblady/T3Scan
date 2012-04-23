<?php

function t3scan_matchBranchInMetatag($html){

	$branchMatches = array();
	preg_match('#name="generator" content="TYPO3 ([0-9.-]+)#', $html, $branchMatches);
	
	return isset($branchMatches[1]) ? str_replace('-', '.', $branchMatches[1]) : FALSE;
}

function t3scan_matchVersionInChangelog($html){
	$versionMatch = array();
	
	preg_match('#Release of TYPO3 ([0-9.-]+)#', $html, $versionMatch);
	return isset($versionMatch[1]) ? $versionMatch[1] : FALSE;
}

function t3scan_matchVersionsByFingerprints($target, $prints, &$reqCount=0){
	
	$versionsFilter = array();
	$fileFound = false;
	
	do{
		//Sort by diff factors
		$diffFactors = array();
		foreach(array_keys($prints) as $f){
			$hashes = $prints[$f];
			if(!is_array($hashes)) {
				$hashes = array($hashes=>array('1'));
			}
			if($diff = t3scan_calcFingerprintsDiffFactor($hashes, $versionsFilter)){
				$diffFactors[$f] = $diff;
				$prints[$f] = $hashes;
			}
		}
		
		if(!empty($diffFactors)){
			arsort($diffFactors);
			
			//Get the most "different" file and search its hash
			$f = array_shift(array_keys($diffFactors));
			$reqCount++;
			
			$code = 0;
			if(($content = t3scan_httpget($target.$f, $code)) && isset($prints[$f][md5($content)])){
				
				if(sizeof($prints[$f][md5($content)]) == 1) {
					$foundVersion = array_shift($prints[$f][md5($content)]);
				} else {
					$versionsFilter = $prints[$f][md5($content)];
				}
			};
			if($code != 404) {
				$fileFound = true;
			}
			unset($prints[$f]);
		}
		
	} while(!isset($foundVersion) && !empty($diffFactors));
	
	return (isset($foundVersion) ? $foundVersion : (empty($versionsFilter) ? $fileFound : $versionsFilter));
}


function t3scan_calcFingerprintsDiffFactor(&$hashes, $versionsFilter){
    $sum = 0;
		//var_dump($versionsFilter);
    foreach(array_keys($hashes) as $h){
        if(!empty($versionsFilter)){
            //var_dump($hashes[$h]);
			$hashes[$h] = array_filter($hashes[$h], create_function('$v', 'return in_array($v, array("'.implode('","', $versionsFilter).'"));'));
			//var_dump($hashes[$h]);
        }
        if(empty($hashes[$h]) || sizeof($hashes[$h]) == sizeof($versionsFilter)) {
            unset($hashes[$h]);
        } else {
            $sum += sizeof($hashes[$h]);
        }
    }
    
    $nbHashes = sizeof($hashes);
    return ($nbHashes ? ($nbHashes / ($sum /  $nbHashes)) : false);
}

function t3scan_httpget($url, &$ret=null){
	global $_proxy;
	
	$opts = array();
	if(isset($_proxy) && !empty($_proxy)){
		$opts['proxy'] = 'tcp://'.$_proxy;
	}
    return t3scan_httpreq($url, $opts, $ret);
}

function t3scan_httpreq($url, array $ctx=array(), &$ret=null){
//var_dump($url);
    $ctx = array_merge(array(
        'user_agent' => 'Internet Explorer 8 (compatible; MSIE 8.0; Windows NT 6.1) Microsoft Internet Explorer Win32',
        'request_fulluri' => true,
    ), $ctx);
    
    $ctx = stream_context_create(array('http' => $ctx));

    $r =  @file_get_contents($url, false, $ctx);
	if(isset($http_response_header) && strpos($http_response_header[0], 'HTTP') === 0){
		list($ver, $code, $resp) = explode(' ', $http_response_header[0]);
		$ret = (int) $code;
	}
	return $r;
}