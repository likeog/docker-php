<?php


class A{

	public function __construct(){}

	public function c(){
		$a = yield $this->d();
		if( $a == 'body' ){
			$this->a();
		}

	}

	function a(){
		echo '11111111111';
	}

	function d(){
		return 'xiaowei';
	}
}
$a = new A();
$c = $a->c();
var_dump($c->current());
$c->send('body');
$descriptorspec = array(
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);
		$pipes = array();
		$resource = proc_open('git', $descriptorspec, $pipes);
		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		foreach ($pipes as $pipe) {
			fclose($pipe);
		}
		$status = trim(proc_close($resource));
		//var_dump($stdout);


$descriptorspec = array(
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);
		$pipes = array();
		$resource = proc_open('/usr/bin/git clone  -b v1.3.0  https://github.com/widuu/chinese_docker1.git', $descriptorspec, $pipes,__DIR__.'/test/');
	
		print stream_get_contents($pipes[1]);
		print stream_get_contents($pipes[2]);
		
		foreach ($pipes as $pipe) {
			fclose($pipe);
		}
		

		//}
		$status = trim(proc_close($resource));
