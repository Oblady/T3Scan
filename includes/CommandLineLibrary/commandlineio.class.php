<?php

//namespace \amod;

class commandLineIO{

	private static $instance = null;
	
	protected $in = null;
	protected $out = null;
	protected $err = null;
	
	protected $options = array();
	protected $argv = array();
	
	protected $scriptPath = '';
	
	/**
	 * Class constructor
	 *
	 */
	protected function __construct(){
		global $argv;
		
		$this->scriptPath = array_shift($argv);
		
		$argName = '';
		while($arg = array_shift($argv)){
			if(strpos($arg,'-')===0){
			
				if(!empty($argName)) {
					$this->options[$argName] = true;
				}
				
				if(strpos($arg,'--')===0) {
					if(!empty($argName)){
						$this->options[$argName] = true;
					}
					$argName = substr($arg,2);
					
					if(strpos($argName, '=')){
					    list($argName, $addArgVal) = explode('=', $argName, 2);
					    array_unshift($argv, $addArgVal);
					}
				} else {
					$argName = substr($arg,1,1);
				}
				
			}else{
				if(!empty($argName)) {
					$this->options[$argName] = $arg;
					$argName = '';
				} else {
					$this->argv[] = $arg;
				}
			}
		}
		
		if(!empty($argName)){
			$this->options[$argName] = true;
		}
		
		$this->in = STDIN;
		$this->out = STDOUT;
		$this->err = STDERR;
		
		
	}
	
	public static function getInstance(){
		if(!isset(self::$instance))
			self::$instance = new commandLineIO();
		
		return self::$instance;
	}
	
	public function getScriptPath(){
		return $this->scriptPath;
	}
	
	public function getUp($optName='',$alt=''){
		if(empty($optName)){
			$v = each($this->argv);
			return $v[1];
		}if(isset($this->options[$optName])){
			return $this->options[$optName];
		}elseif(isset($this->options[$alt])){
			return $this->options[$alt];
		}else{
			return false;
		}
	}
	
	public function getOptions(array &$arr=null){
	    /*if(is_null($arr)){
	        $arr = &$GLOBALS;
	    }
	    $arr = array_merge($arr, $this->options);*/
		if(!is_null($arr)){
	        $arr = array_merge($arr, $this->options);
	    } else {
			$arr = $this->options;
		}
		return $arr;
	}
	
	public function listen($endOfInput = "\n"){
		$in = '';
		while(1){
			$c = fread($this->in,1);
			if($c == $endOfInput){
				break;
			}else
				$in .= $c;
		}
		return $in;
	}
	
	public function tell($str, $addNL = true){
		$o = new outputString($this->out,$str);
		if($addNL){
			$o->inline();
		}
		return  $o;
	}
	
	public function whisper($str){
		if($this->getUp('verbose','v'))
			return $this->tell($str);
	}
	
	public function cry($str){
		return new outputString($this->err,$str."\n");
	}
	
}
