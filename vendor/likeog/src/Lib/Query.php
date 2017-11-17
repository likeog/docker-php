<?php

namespace likeog\Docker\Lib;

class Query
{
	private $params = [];

	private $url;

	public function __construct( $url = '' )
	{
		$this->url = $url;
	}

	public function setDefault( $name, $value = "" )
	{
		if( is_array($name) ){
			foreach ($name as $k => $v) {
				$this->setDefault( $k, $v );
			}
		}else{
			$this->params[$name] = $value;
		}
	}

	public function setParams($params = [])
	{
		foreach ($params as $name => $value) {
			if( array_key_exists($name, $this->params) && (  !isset($value)||!empty($value) ) ){
				if( $value == 'true'  || $value === true  ) $value = 1;
				if( $value == 'false' || $value === false ) $value = 0;
				$this->params[$name] = $value;
			}
		}
	}

	public function setUrl($url)
	{
		$this->url = $url;
	}

	public function buildQuery($params = [])
	{
		if( count($params) > 0 ) $this->setParams($params);
		$params = http_build_query($this->params);
		if( !empty($params) ){
			return $this->url.'?'.$params;
		}
		return $this->url;
	}

}