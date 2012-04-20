#!/usr/bin/php
<?php
chdir(dirname(__FILE__));

//Configuration
$input = 't3_extensions.csv';
$output = 't3_extensions_fingerprints.ini';
$logs = 'extensions-no-prints.log';

$repo_host = 'http://typo3.org';

//Init
file_get_contents($repo_host) or die('No network...');
($exts = file_get_contents($input)) or die('Can\'t read input...');
file_put_contents($output, '')!==false or die('Can\'t write output...');
file_put_contents($logs, '')!==false or die('Can\'t write logs...');
echo "\n".$repo_host."\n\n";

foreach(explode("\n", $exts) as $x){
    list($name, $ver) = str_getcsv($x);
    echo $name."\n";
    
    $downloadUrl = 'http://typo3.org/extensions/repository/download/'.$name.'/'.$ver.'/zip/';
    $downloadOut = '/tmp/'.$name.'-'.$ver.'.zip';
    $tmpDir = '/tmp/'.$name;
    exec('wget -O "'.$downloadOut.'"  "'.$downloadUrl.'"');
    if(file_exists($downloadOut) && mkdir($tmpDir)){
        exec('unzip -o '.$downloadOut.' -d '.$tmpDir);
        $prints = staticFilesFingerprints($tmpDir);
        if(!empty($prints)){
            $ini = '['.$name.']'."\n";
            foreach($prints as $p){
                $ini .= $p[1].' = "'.str_replace($tmpDir, '', $p[0]).'"'."\n";
            }
            $ini .= "\n";
            file_put_contents($output, $ini, FILE_APPEND);
        } else {
            trigger_error('No fingerprints found for "'.$name.'"');
            file_put_contents($logs, $name."\n", FILE_APPEND);
        }
        exec('rm '.$downloadOut.' && rm -r '.$tmpDir);
    } else {
        trigger_error('Can\'t download "'.$downloadUrl.'"');
    }
}


function staticFilesFingerprints($scanDir, $maxDepth = 3){
    $ret = array();
    foreach(glob($scanDir . '/*') as $file) {
        if(is_dir($file)){
            if($maxDepth > 0){
                $ret = array_merge($ret, staticFilesFingerprints($file, $maxDepth--));
            }
        } else {
            //avoid hidden files
            if(strpos(basename($file), '.') === 0){
                continue;   
            }
            
            $fileType = strtolower(substr(strrchr($file,'.'),1));
            if(empty($fileType) || in_array($fileType, array('txt', 'css', 'js', 'html', 'htm', 'sql'))) {
                $ret[] = array($file, md5_file($file));
            }
        }
    }
    return $ret;
}