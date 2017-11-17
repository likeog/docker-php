<?php

/** 
 * Docker Web Manager 公共模块未登陆使用
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */ 

namespace docker\dashboard\controller;

use think\Controller;
use think\Db;
use docker\dashboard\model\User;
use docker\dashboard\model\Job as JobModel;

class Common extends Controller
{

    /**
     * 登录操作
     *
     * @return object json
     */

    public function login()
    {
    	if( isLogin() ){
			$this->redirect('/dashboard/');
		}

    	if( $this->request->isGet() ){
        	return $this->fetch();
    	}else{
    		$postData = input('post.');
    		$checkCaptcha = $this->validate($postData,[
			    'captcha|验证码'  =>  'require|captcha',
			    '__token__'  	 =>  'require|token',

			]);
			if( !is_bool($checkCaptcha) ){
				return json(['status'=>2,'info'=>$checkCaptcha]);
			}
			$userModel = new User;
			return json($userModel->login($postData['username'], $postData['password']));
    	}
    }

    /**
     * 退出登录
     *
     * @return void
     */

    public function logout()
    {
    	session(NULL) && cookie(NULL);
    	$this->redirect('/');
    }

    /**
     * 获取用户是否存在
     *
     * @return object json
     */

    public function getUser($username)
    {
    	$count = Db::name('user')->where('email',$username)->count()+0;
    	return json(['status'=>$count]);
    }

    /**
     * 镜像构建方法
     *
     * @return void
     */

    public function buildImage()
    {
        if( $this->request->isPost() ){
            ignore_user_abort(true);
            set_time_limit(0) ;
            // 头部验证
            $buildAuth = $this->request->header('X-Registry-Config');
            if( $buildAuth != md5(config('build_auth_header')) ) exit();
            // id检测
            $id = intval( input('post.id') ); 
            if( $id == 0 ) exit();
            // 获取config信息
            $config = JobModel::get($id);
            // config 检测
            if( count($config->config) == 0 ) exit();
            $dockerConfig = $config->config;
            // 返回Stream
            $docker = Dao( 'Image',[],$config->uid );
            $stream = $docker->build( $dockerConfig['params'], $dockerConfig['file'],$dockerConfig['registryConfig'] );
            $logPath = getLogPath($dockerConfig['log']);
            file_put_contents($logPath, "准备开始构建\r\n\r\n");
            while( !feof($stream) ){
                $logStream = fgets($stream);
                $log = $this->parseStream(json_decode($logStream,true));
                file_put_contents($logPath,$log ,FILE_APPEND);
            }
            file_put_contents($logPath, "\r\n构建完毕", FILE_APPEND);
            fclose($stream);
            JobModel::where('id', $id)->update(['status' => 2]);
        }
    }

    public function parseStream($array)
    {
        $string = '';
        if( isset($array['stream']) ){
            $string .= $array['stream']."\r";
        }

        if( isset($array['status']) ){
            $string .= $array['status']."\t:\t";
            if( isset($array['progress']) ){
                $string .= $array['progress']?$array['progress']:'';
                $string .= "\r";
            }
            if( isset($array['id']) ){
                $string .= $array['id']?$array['id']:'';
                $string .= "\r";
            }
        }

        if( isset($array['errorDetail']) ){
            $string .= $array['errorDetail']['message'];
        }
        return $string;
    }
}
