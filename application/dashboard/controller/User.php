<?php

/** 
 * Docker Web Manager 用户模块
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */ 

namespace docker\dashboard\controller;

class User extends Base
{

	public function list()
	{
		$this->view->list = model('User')->paginate(2);
		return $this->fetch();
	}
}
