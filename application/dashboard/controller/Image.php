<?php

/** 
 * Docker Web Manager 镜像模块
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */ 

namespace docker\dashboard\controller;

use docker\dashboard\model\Image as ImageModel;
use docker\dashboard\model\Job as JobModel;

class Image extends Base
{

	private $docker = null;

	public function _initialize()
	{
		parent::_initialize();
		$paramUid = intval(input('uid'));
		if( $paramUid != 0 && session('uid') != $paramUid ){
			$this->docker = Dao( 'Image', [], $paramUid );
		}else{
			$this->docker = Dao( 'Image' );
		}
	}

	/**
     * 镜像列表
     *
     * @return think\View
     */

	public function lists()
	{
		$where = getListWhere();
		$this->view->list = model('Image')->where($where)->paginate(9);
		return $this->fetch();
	}

	/**
     * 删除镜像
     *
     * @return think\View
     */

	public function remove($id)
	{
		if( $this->request->isAjax() ){
			$imageName = ImageModel::where('id',intval($id))->value('name');
			$imageTag  = ImageModel::where('id',intval($id))->value('tag');
			if( !$imageName[0] ) return json(['status'=>0,'info'=>'镜像不存在']);
			$status = $this->docker->remove($imageName.':'.$imageTag,[],true);
			if( $status == 404 || $status == 200 ){
				ImageModel::where('id',intval($id))->delete();
				return json(['status'=>1,'info'=>'删除成功']);
			}

			if( $status == 409 ){
				return json(['status'=>0,'info'=>'镜像正在使用中']);

			}
			return json(['status'=>0,'info'=>'删除失败']);
		}
		return json(['status'=>0,'info'=>'非法请求']);
	}

	/**
     * 更新镜像列表
     *
     * @return Object
     */

	public function updateImage()
	{
		$serverStatus = $this->docker->ping();
		if( $serverStatus != 200 ){
			return json(['status'=>0,'info'=>'Docker服务器连接失败，请检查配置']);
		}
		$imageList  = $this->docker->lists(['digests'=>true]);
		$ImageModel = new ImageModel();
		$result = $ImageModel->updateImage($imageList);
		if( $result ) return json(['status'=>1,'info'=>'更新列表成功']);
		return json(['status'=>0,'info'=>'更新列表失败请稍后重试']);
	}

	/**
     * 构建镜像
     *
     * @return Object
     */

	public function build()
	{
		if( $this->request->isGet() ){
			return $this->fetch();
		}

		if( $this->request->isAjax() ){
			$method    = input('param.method');
			$postParam = input('post.');
			extract( $postParam );
			// 检测镜像名称和Tag
			$config['file'] = $file = "";
			$config['registryConfig'] =  $registryConfig = [];
			if( empty($image) || empty($tag) ){
				return json(['status'=>0,'info'=>'镜像名称或者镜像Tag不能为空!']);
			}
			$dockerParams['t']   = $config['params']['t'] = $image.':'.$tag;
			$postParam['method'] = trim($method);
			// 方法函数检测
			switch ($method) {
				case 'git':
					if( empty($git_remote) ) return json(['status'=>0,'info'=>'git仓库地址不能为空！']);
					$git_remote = preg_replace("/(https:\/\/|http:\/\/|\.git)/" ,'', trim($git_remote) );
					$dockerParams['remote'] = $config['params']['remote'] = "git://".$git_remote;
					break;
				case 'dockerfile':
					if( empty(trim($dockerfile)) ) return json(['status'=>0,'info'=>'Dockerfile 内容不能为空']);
					$config['file'] = $file = getImagePath(md5($image.$tag.time()), 'tar.gz');
					$compress = new \PharData($file);
					$compress->addFromString('Dockerfile',trim($dockerfile));
					//$compress->compress(\Phar::GZ);
					break;
				default:
					# code...
					break;
			}
			$status = $this->docker->build($dockerParams,$file,$registryConfig,true);
			if( $status == 200 ){
				$model  = new JobModel();
				$config['log'] = md5($dockerParams['t']).time().'.log'; 
				$result = $model->data(['config'=> $config, 'desc'=>"构建镜像 [ {$dockerParams['t']} ]" ] )->save();
				$this->curl_post(['id'=>$model->id]);
				return json(['status'=>1,'info'=>url('/dashboard/image/build_log/'.$model->id)]);
			} 
			if( $status == 500 ) return json(['status'=>0,'info'=>'构建参数错误']);
			return json(['status'=>0,'info'=>'参数错误']);
		}
	}

	/**
     * 构建日志
     *
     * @return View
     */

	public function build_log($id)
	{
		$id = intval($id);
		$config = JobModel::get($id);
		$this->view->log = $config->config['log'];
		$this->view->status = $config->status;
		return $this->fetch();
	}

	/**
     * Ajax 获取显示日志
     *
     * @return Json Object
     */

	public function logs()
	{
		if( $this->request->isAjax() ){
			$log = input('post.log');
			$num = input('post.num');
			$content = "";
			$flag = 0;
			if( input('post.status') != 1 ){
				$logPath = getLogPath($log);
				$content = file_get_contents($logPath);
				$flag    = 1;
			}else{
				$logPath = getLogPath($log);
				if( !file_exists($logPath) ){
					return json(['status'=>0]);
				}
				$body    = file_get_contents($logPath);
				$stream  = explode("\r", $body);
				if( !isset($stream[$num]) ){
					return json(['status'=>0]);
				}
				$content = $stream[$num];
				if( trim($content) == '构建完毕' || stripos($content, 'errorDetail') ){
					$flag = 1;
				}
			}
			return json([
				'status' => 1,
				'info'   => $content."</br>",
				'num'    => $num+1,
				'flag'   => $flag,
				'id'	 => $num
			]);
		}
	}

	/**
     * 构建异步请求
     *
     * @return Object
     */

	private function curl_post($postData){
		$ch = curl_init();
		$header = [ 'X-Registry-Config:'.md5(config('build_auth_header')) ];
		curl_setopt( $ch, CURLOPT_URL, 'http://'.$_SERVER['HTTP_HOST'].'/build.html');
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt( $ch, CURLOPT_POST, 1); 
		curl_setopt( $ch, CURLOPT_POSTFIELDS,$postData);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 1);
		curl_exec($ch);
		curl_close($ch);
	}
}