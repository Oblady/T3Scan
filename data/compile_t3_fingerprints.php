#!/usr/bin/php
<?php
chdir(dirname(__FILE__));

//Configuration
$input = 't3_fingerprints.ini';
$output = 't3_fingerprints.php';

//Index files > fingerprints > versions
$printsByVersions = parse_ini_file($input, true);
$files = array();
foreach($printsByVersions as $ver=>$prints) {
    foreach($prints as $h=>$f){
        if(!isset($files[$f])){
            $files[$f] = array();
        }
        if(!isset($files[$f][$h])){
            $files[$f][$h] = array();
        }
        $files[$f][$h][] = $ver;
    }
}

file_put_contents($output, '<?php'."\n".'$prints = unserialize(base64_decode("'.base64_encode(serialize($files)).'"));');