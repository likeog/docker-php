<?php

namespace likeog\Docker\Module;

use likeog\Docker\Lib\Query;
use likeog\Docker\Lib\Request;
use likeog\Docker\Factory\SocketInterface;
use likeog\Docker\Factory\ResponseInterface;

class Module
{
	protected $response = null;

	protected $socket   = null;

	protected $query    = null;

	public function __construct(SocketInterface $socket, ResponseInterface $response )
	{
		$this->socket   = $socket;
		$this->response = $response;
	}


	public function ping()
	{
		$query = new Query('/_ping');
		$request  = new Request('GET',$query->buildQuery());
		$response = $request->sendRequest($this->socket,$this->response);
		return $response->getStatusCode(true);
	}

}