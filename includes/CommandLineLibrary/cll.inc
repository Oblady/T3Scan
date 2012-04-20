<?php

if(!defined('AMOD_CLL_PATH')){
    define('AMOD_CLL_PATH', dirname(__FILE__).'/');
}

if(strtolower(PHP_SAPI) != 'cli'){
	echo 'This is a command line tool...';
	exit(121071820);
}

require_once(AMOD_CLL_PATH.'commandlineio.class.php');
require_once(AMOD_CLL_PATH.'outputstring.class.php');
$io = commandLineIO::getInstance();

