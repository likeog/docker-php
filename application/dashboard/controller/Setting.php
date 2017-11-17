<?php

/** 
 * Docker Web Manager 设置模块
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */ 

namespace docker\dashboard\controller;

use think\Db;
use think\Config;
use docker\dashboard\model\Config as AdminConfig;

class Setting extends Base
{

	/**
     * 设置页面
     *
     * @return mix
     */

	public function index()
	{
    	if( $this->request->isGet() ){
    		$configId = getSettingUid();
    		$this->view->uid    = $configId;
			$this->view->config = Db::name('Config')->where(['uid'=>$configId])->find();
    		return $this->fetch();
    	}else{
    		extract( input('post.') );
			if( !$this->pingParse($uid) ) return json(['status'=>0,'info'=>'Socket连接服务器失败']);
			if( intval( $id ) == 0 ){
				$insterData = input('post.');
				unset($insterData['id']);
				$result = Db::name('Config')->insert($insterData);
			}else{
				$result = Db::name('Config')->update(input('post.'));
			}
    		if( !$result ) return json(['status'=>0,'info'=>'更改失败']);
    		return json(['status'=>1,'info'=>'更改成功']);  
    	}
	}

	/**
     * Ajax 检测配置
     *
     * @return Bool
     */

	public function ping($id){
		if( $this->request->isAjax() ){
			if( intval($id) == 0 ) return json(['status'=>0,'info'=>'参数错误']);
			$result = $this->pingParse($id);
			if( $result ) return json(['status'=>0,'info'=>'连接成功']);
			return json(['status'=>0,'info'=>'服务器连接失败']);
		}
	}

	/**
     * 解析证书文件并ping
     *
     * @return Bool
     */

	public function pingParse($uid)
	{
		$config = ['remote_socket'=>input('post.remote_socket')];
		$type   = intval(input('post.stream_type'));
		$socket_type = [ 1 => 'unix',2 => 'tcp',3 => 'ssl' ];
		$config['stream_type'] = $socket_type[$type];
		// ssl
		if( $type == 3 ){
			$certsPath = getCertsPath($uid);
			if( !file_exists($certsPath.'client_cert.pem') ){
			 	return json(['status'=>0,'info'=>'证书文件不存在']);
			}
			$config['stream_context_options'] = [
				'cafile'      => $certsPath.'client_ca.pem',
        		'local_cert'  => $certsPath.'client_cert.pem',
        		'local_pk'    => $certsPath.'client_key.pem'
			];
			$config['ssl'] = true; 
		}
		return $this->_ping($config);
	}

	/**
     * 上传证书并解压
     *
     * @return Object json
     */

	public function upload($id){
		if( intval($id) == 0 ) return json(['status'=>0,'info'=>'参数错误']);
		// 文件上传类型
		$uploadValidata = ['ext'=>'gz,tar,zip'];
		// 上传证书
		$uploadFile = $this->uploadFile('certs','certs'.DS.$id,$uploadValidata);
		if( isset($uploadFile['status']) && !$uploadFile['status'] ){
			return json(['status'=>0,'info'=>$uploadFile['info']]);
		} 
		$filePath = getCertsPath($id);
		$fileInfo = ['ext'=>$uploadFile['ext'],'file'=>$filePath.$uploadFile['file']];
		// 证书解压
		$extractResult = customExtract($fileInfo,$filePath,'pem',true);
		if( !$extractResult ) return json(['status'=>0,'info'=>'解压失败，请检查文件正确性']);
		// 检测证书是否完整
		$certs = ['client_ca.pem','client_cert.pem','client_key.pem'];
		$result = ['status'=>1,'info'=>'上传并解压成功'];
		array_walk_recursive($certs, function($file) use(&$result,$filePath){
			if( !file_exists($filePath.$file)){
				$result = ['status'=>0,'info'=>'压缩包中'.$file.'不存在！！'];
			}
		});
		return json($result);
	}		
}
