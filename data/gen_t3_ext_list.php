#!/usr/bin/php
<?php
chdir(dirname(__FILE__));

//Configuration
$output = 't3_extensions.csv';

$repo_host = 'http://typo3.org';
$repo_maxPages = 269;

//Init
file_get_contents($repo_host) or die('No network...');
file_put_contents($output, '')!==false or die('Can\'t write output...');
echo "\n".$repo_host."\n\n";

//Pages parsing
for($i = 1; $i<=$repo_maxPages; $i++){
    echo $i.': ';
    $r = file_get_contents(sprintf($repo_host.'/extensions/repository////page/%d/ter_search/downloads/?tx_terfe2_pi1[restoreSearch]=1', $i));
    $m = array();
    if(preg_match_all('#href="/extensions/repository/download/([^/]+)/([^/]+)/zip/#', $r, $m)){
        foreach($m[1] as $k=>$x){
            file_put_contents($output, '"'.$x.'","'.$m[2][$k].'"'."\n", FILE_APPEND);
            echo '.';
        }
    }
    echo "\n";
}

