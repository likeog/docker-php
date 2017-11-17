<?php

/** 
 * Docker Web Manager 基类
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */ 

namespace docker\dashboard\controller;

use util\Auth;
use think\Db;
use think\Controller;
use think\Cache;
use likeog\Docker\Docker;

class Base extends Controller
{

	/**
     * 基类检测登录 权限 服务器等方法
     *
     * @return Void
     */

	public function _initialize()
    {
    	// 登录检测
    	if( !isLogin() ){
			$this->redirect('/signin.html');
		}
		// 权限检测
        $authModel  = new Auth();
        $module 	= $this->request->module();
		$controller = $this->request->controller();
		$action     = $this->request->action();
		$verifyName = $module.'/'.$controller.'/'.$action;
		$isAdmin    = config('auth_config.admin_uid') == session('uid');
		$this->view->numberCount = Db::name('user')->count();
		if( !$authModel->check($verifyName,session('uid')) && !$isAdmin ){
		//	$this->error('您没有操作权限');
		}

		// 服务器连接检测
		$flag = getSettingUid();
		if( !Cache::has('INSTALL_'.$flag) ){
			if( !$this->_ping() && $controller != 'Setting' ){
				$this->view->install = 1;
			}else{
				Cache::set('INSTALL_'.$flag,1,3600);
			}
    	}
    	
    }

    /**
     * 检测Docker服务器连接设置
     *
     * @return BOOL
     */

   	protected function _ping($config=[])
	{
		try{
			if( count($config) > 0 ){
				$docker = new Docker($config,'Module');
			}else{
				$docker = Dao('Module');
			}
			if( $docker->ping() == 200 ){
				return true;
			}
			return false;
		}catch(\Exception $e){
			return false;
		}
	}

	/**
     * 上传文件的方法
     *
     * @return Json Object
     */

	protected function uploadFile($name,$storagePath,$customValidata = []){
		$file = $this->request->file($name);
		$path = BASE_PATH . 'storage' . DS . $storagePath;
		!is_dir($path) && @mkdir($path, 0755, true);
		$validate = array_merge($customValidata,['upload_file_size'=>config('upload_file_size')]);
		$info = $file->validate($validate)->rule('uniqid')->move($path);
		if($info){
			return ['ext'=>$info->getExtension(),'file'=>$info->getFilename()];
			}else{
			return ['status'=>false,'info'=>$file->getError()];
		}
	}
}
