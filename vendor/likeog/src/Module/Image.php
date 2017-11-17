<?php

namespace likeog\Docker\Module;

use likeog\Docker\Lib\Query;
use likeog\Docker\Lib\Request;

class Image extends Module
{
	public function lists( $params=[] )
	{
		$query = new Query('/images/json');
		$query->setDefault('all', false);
		$query->setDefault('digests', false);
		$query->setDefault('filters', null);
		$query->setDefault('filter', null);
		$request  = new Request('GET',$query->buildQuery($params));
		$response = $request->sendRequest($this->socket,$this->response);
		return json_decode($response->getBody(),true);
	}

	public function remove($imageName, $params=[], $code=false)
	{
		$query = new Query('/images/'.$imageName);
		$query->setDefault('force', false);
		$query->setDefault('noprune', false);
		$request  = new Request('DELETE',$query->buildQuery($params));
		$response = $request->sendRequest($this->socket,$this->response);
		if( $code ) return $response->getStatusCode(true);
		return json_decode($response->getBody(),true);
	}

	public function build($params = [], $tarfile = "" , $registryConfig = [], $flag = false )
	{
		$query = new Query('/build');
		$query->setDefault([ 
			'dockerfile' => null,
			't'			 => null,
			'q'			 => null,
			'remote'	 => null,
			'nocache'	 => null,
			'pull'		 => null,
			'rm'		 => null,
			'forcerm'	 => null,
			'memory'	 => null,
			'memswap'	 => null,
			'cpushares'	 => null,
			'cpusetcpus' => null,
			'cpuperiod'	 => null,
			'cpuquota'	 => null,
			'buildargs'	 => null,
			'shmsize'	 => null,
			'labels'	 => null
		]);

		$header = [ 'Content-Type'   => 'application/tar' ];
		// 检测文件方法
		$body = '';
		if( !empty($tarfile) ){
			// 检测文件是否存在
			if( !file_exists($tarfile) ) throw new \Exception("Tar File Not Exists", 404);
			// 读取文件内容
			$fp = fopen($tarfile, 'rb');
			while( !feof($fp) ){
			    $body .= fread($fp,1024);
			}
			fclose($fp);
			// 设置文件头部
			$header[ 'Content-Length' ] = strlen($body);
			$header[ 'Connection' ] = 'close';
		}

		// 检测用户仓库信息
		if( count($registryConfig) > 0 ){
			$header[ 'X-Registry-Config'] = base64_encode(json_encode($registryConfig));
		}
		// 注册Request
		$request  = new Request('POST', $query->buildQuery($params), $header, $body);
		$response = $request->sendRequest($this->socket,$this->response);
		// 根据判断是只返回头部 OR Body
		if( $flag ) return $response->getStatusCode(true);
		return $response->getStream();
	}
}