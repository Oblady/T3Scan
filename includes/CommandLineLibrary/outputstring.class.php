<?php

//namespace \amod\commandLineIO;

class outputString{
	
	protected $stream = null;
	protected $content = '';
	
	protected static $cr = true;
	
	public function __construct($stream,$str){
		$this->stream = $stream;
		$this->content = $str;
	}
	
	public function __destruct(){
	    self::$cr = (substr($this->content, -1) == "\n");
		fwrite($this->stream,$this->content);
	}
	
	public function colored($c){
	
		if(empty($c)){
			return $this;
		}
			
		$colors = array(
			'grey' => 30,
			'red' => 31,
			'green' => 32,
			'yellow' => 33,
			'blue' => 34,
			'purple' => 35,
			'cyan' => 36,
			'white' => 37,
			'bold' => 38,
			'black' => 39
		);
		
		if(isset($colors[$c])) {
			$code = $colors[$c];
		} else {
			$code = intval($c);
		}
			
		$this->content = "\033[1;".$code.'m'. $this->content ."\033[1;00m";
	
		return $this;
	}
	
	public function indented($i=1){
		//$this->content = str_repeat($i,"\t").str_replace("\n",str_repeat($i,"\t")."\n",$this->content);
		$this->content = str_repeat($i,'   ').str_replace("\n",str_repeat($i,'   ')."\n",$this->content);
		return $this;
	}
	
	public function inline(){
	    if(!self::$cr) {
	        $this->content = "\n".$this->content;
	    }
	    $this->content .= "\n";
	    return $this;
	}
}
