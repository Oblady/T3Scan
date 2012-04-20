#!/usr/bin/php
<?php

//CLI init
$rootDir = dirname(__FILE__);
chdir($rootDir);
set_include_path($rootDir.'/includes' .PATH_SEPARATOR. get_include_path());

include('CommandLineLibrary/cll.inc');

//Application init
include('functions.inc');
$io->tell('T3scan v1 by Adrien LUCAS - Sponsored by Oblady'."\n");

$target = $io->getUp();
if(!$target || $io->getUp('help', 'h')) {
    include('usage.inc');
} elseif(!preg_match('#[a-z]+://#i', $target)){
    $target = 'http://'.$target;
}
$target = rtrim($target, '/').'/';

$lvl = (int) $io->getUp('investigate', 'i');
$_proxy = $io->getUp('proxy', 'p');
$_forceCheck = $io->getUp('force', 'f');

//Here we go :
//Meta tags analyse
$homepage = t3scan_httpget($target);
if(($branch = t3scan_matchBranchInMetatag($homepage)) !== false){
    $io->tell('[+] Homepage meta-tag "generator" said branch is '.$branch);
} else {
	$io->tell('[-] Homepage meta-tag "generator" not found');
}

//Changelogs analyse
$ver = false;
foreach(array('typo3/ChangeLog', 'typo3_src/ChangeLog', 'ChangeLog') as $path){
    if(!$ver){
        $file = t3scan_httpget($target.$path);
        $ver = t3scan_matchVersionInChangelog($file);
    }
}

if($ver){
    $io->tell('[+] ChangeLog said release is '.$ver);
} else {
	$io->tell('[-] ChangeLog not found');
}

//Listable directories
$directories = array('fileadmin/', 'typo3temp/', 'uploads/', 'typo3conf/', 'typo3conf/ext/', 'typo3_src');
foreach(array_keys($directories) as $dirKey){
    $code = 0;
    $r = t3scan_httpget($target.$directories[$dirKey]);
    if ($code !== 200 && strpos($r, '<title>Index of') === false) {
        unset($directories[$dirKey]);
    }
}
if(!empty($directories)){    
    $io->tell('[+] Listable directories :');
    foreach($directories as $dir){
        $io->tell('    o '.$target.$dir);
    }
}

//TYPO3 version fingerprinting
if($lvl > 0){
    include('data/t3_fingerprints.php');
    $c = 0;
    $versions = t3scan_matchVersionsByFingerprints($target, $prints, $c);
    if(is_bool($versions)){
        
    } elseif(!is_array($versions)){
        $io->tell('[+] Version fingerprinted :'.$versions);
    } elseif(sizeof($versions) > 0){
        $io->tell('[+] Possible version(s) :'.implode(', ', $versions));
    } else {
        $io->tell('[-] Version detection by fingerprints failed.');
    }
    $io->tell('    Done with '.$c.' requests.');
    
}

//Plugins enumeration
if($lvl > 1) {
    foreach(file('data/t3_extensions.csv') as $ext){
        
        list($extkey, ) = str_getcsv($ext);
        
        $code = 0;
        $r = t3scan_httpget($target.'typo3conf/ext/'.$extkey.'/', $code);
        if($code != 404 || $_forceCheck){
            if($code == 200) {
                $io->tell('[+] Extension "'.$extkey.'" found :');
                $io->tell('    o URL : '.$target.'typo3conf/ext/'.$extkey);
            } else {
                $io->tell('[+] Extension "'.$extkey.'" blind check :');
                $io->tell('    o HTTP '.$code.' on '.$target.'typo3conf/ext/'.$extkey);
            }
            
            if(strpos($r, '<title>Index of') || strpos($r, 'ext_icon.gif') || strpos($r, 'ext_emconf.php')){
                $io->tell('    o Directory listing is allowed.');
            }
            
            //Plugins version fingerprinting
            if($lvl > 2) {
                static $ext_prints = null;
                if(is_null($ext_prints)){
                    $ext_prints = parse_ini_file('data/t3_extensions_fingerprints.ini', true);
                }
                if(isset($ext_prints[$extkey])) {
                    $c = 0;
                    $r = t3scan_matchVersionsByFingerprints($target.'typo3conf/ext/'.$extkey, $ext_prints[$extkey], $c);
                    if($r === 1) {
                        $io->tell('    o Seems to be up-to-date ('.$c.' fingerprint checks).');
                    } elseif(is_array($r)) {
                        $io->tell('    o Seems to be outdated ('.$c.' fingerprint checks).');
                    } elseif($r === false){
                        $io->tell('    o Seems not to be installed (100% of '.$c.' check(s) returned 404).');
                    } elseif ($_forceCheck) {
                        $io->tell('    o Seems to be installed (at least one static file found).');
                    }
                } elseif(!$_forceCheck) {
                    $io->tell('    o No fingerprints for this extension, can\'t check version.');
                }
            }
        }
    }
}



$io->tell('');