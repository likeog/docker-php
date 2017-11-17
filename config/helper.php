<?php

/** 
 * Docker Web Manager 助手函数
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */ 

use think\Db;
use think\Cache;
use likeog\Docker\Docker;
use docker\dashboard\model\Container as ContainerModel;

/**
 * 检测是否登录
 * @return  boolean
 */

function isLogin()
{
	$islogin = session("?username") && session("?islogin") || cookie("?username") && cookie("?islogin");
	return $islogin;
} 

/**
 * 设置日志
 * @param   integer   $uid  	用户id
 * @param   string    $explain  描述信息
 * @param   integer   $type  	类型id
 * @return  boolean
 */

function setLog($uid, $explain, $type = 1 )
{
	$result = DB::name('logs')->insert([
					 'ip' => request()->ip(),
 			        'uid' => $uid,
			       'type' => $type,
			    'explain' => $explain,
			'create_time' => time()
		]);
	return $result;
}

/**
 * 容器端口
 * @param   array   $config  容器信息array
 * @return  string
 */

function containerPort($config)
{
	if( isset($config['Ports']) ){
		$ports = $config['Ports'];
		if( count($ports) > 0 ){
			$ports_string = '';
			foreach ($ports as  $value) {
				$ports_string .= '<span class="label label-inverse">'.$value['PrivatePort'].'</span> : <span class="label label-inverse">'.$value['PublicPort'].'</span>';
				$ports_string .= "<br>"; 
			}
			return $ports_string;
		}
	}
	return '<span class="label label-inverse">未指定</span>';
}

/**
 * 容器状态
 * @param   integer   $type  容器状态
 * @return  string
 */

function containerStatus($type){
	$status = [ 
		'<span class="label label-danger">已停止</span>',
		'<span class="label label-success">运行中</span>',
		'<span class="label label-warning">已暂停</span>'
	];
	return $status[$type];
}

/**
 * 获取容器id 12位
 * @param   integer   $id  容器在数据库中的id
 * @return  string
 */

function getContainerSha($id){
	$dockerSha  = ContainerModel::where('id',$id)->value('sha');
	if( !$dockerSha ) return false;
	return substr($dockerSha, 0, 12);
}

/**
 * 获取用户组名称
 * @param   integer   $uid  用户id
 * @return  string
 */

function getGroup($uid)
{
	$userGroup = Cache::get('USER_GROUP');
	if( !$userGroup || !isset($userGroup[$uid]) ){
		if( !$userGroup ){
			$userGroup = [];
		}
		$groupName  = DB::name('authGroupAccess a')
						->where("a.uid='$uid'")
						->field('g.title as title')
						->join(config('database.prefix')."auth_group g "," a.group_id=g.id")
						->find();
		$userGroup[$uid] = $groupName['title'];
		Cache::set('USER_GROUP',$userGroup);
	}
	return $userGroup[$uid];
}

/**
 * 实例化 Docker SDK
 * @param   string    $model   实例化的Model名称 继承于\likeog\Docker\Module
 * @param   array     $config  数组话配置文件[
 *											'stream_type'   => 'unix',
 *											'remote_socket' => '/var/run/docker.sock',
 *									        'timeout' 		=> null,
 *									        'stream_context_options' => [
 *									        	'cafile'      => null,
 *									            'local_cert'  => null,
 *									            'local_pk'    => null,
 *									        ],
 *									        'ssl' => null,
 *									        'write_buffer_size' => 8192,
 *										];
 * @param   integer    $uid    用户id，当config为空的时候，根据用户id获取配置信息
 * @return  \likeog\Docker\Docker 
 */

function Dao(  $module = '', $config = [], $uid = 0 )
{
	if( count($config) == 0 ){
		if( $uid == 0 ) $uid = getSettingUid();
		$modelConfig = Db::name('Config')->where(['uid'=>$uid])->find();
		$type = $modelConfig['stream_type'];
		$config = ['remote_socket'=>$modelConfig['remote_socket']];
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
	}
	return new Docker($config,$module);
}

/**
 * 多用户和单用户模式判断并返回以哪个用户的配置文件为主
 * @return  integer
 */

function getSettingUid(){
	$configId = 1;
	if( !config('mutil_user') ){
		$adminuid = Cache::get('ADMIN_UID');
		if( !$adminuid ){
			$adminuid = Db::name('User')->where(['is_admin'=>1])->value('id');
			Cache::set('ADMIN_UID',$adminuid);
		}
		$configId = $adminuid;
	}else{
		$configId = session('uid');
	}
	return $configId;
}

/**
 * 获取镜像或者容器数据库列表并缓存，缓存时间300
 * @param   integer   $type   1 容器列表 2 镜像列表
 * @return  array
 */

function getModelCache($type = 1)
{
	$uid = getSettingUid();
	$cacheName = 'CONTAINER_'.$uid;
	if( $type != 1 ) $cacheName = 'IMAGE_'.$uid;

	$containerCache = Cache::get($cacheName);
	if( !$containerCache ){
		$model = DB::name('Container');
		if( $type != 1 ) $model = DB::name('Image');
		$where = [];
		// 如果多用户文件取用户的配置文件
		if( config('mutil_user') ) $where['uid'] = $uid;
		
		$containerCache  = $model->where($where)->column('sha','id');
		Cache::set($cacheName,$containerCache,300);
	}
	return $containerCache;
}

/**
 * 用户登录存储session方法
 * @return  void
 */

function doLogin($userinfo)
{
	session('uid',$userinfo['id']);
	session('islogin',1);
	session('group_id',$userinfo['group_id']);
    session('username',$userinfo['username']);
	session('face',$userinfo['face']);
	session('is_admin',$userinfo['is_admin']);
}

/**
 * 判断where条件，如果多用户模式，用户只能看自己容器或者镜像的条件
 * @return  array
 */

function getListWhere()
{
	$where = [];
	// 多用户和不是管理员的情况
	if( config('mutil_user') && !session('is_admin') ) $where['uid'] = session('uid');
	return $where;
}

/**
 * 解析文件大小
 * @param 	integer  $size  文件大小字节
 * @return  string
 */

function formatBytes($size) 
{ 
	$units = array(' B', ' KB', ' MB', ' GB', ' TB'); 
  	for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024; 
  	return round($size, 2).$units[$i]; 
}

/**
 * 获取证书文件地址
 * @param 	integer  $uid  用户id
 * @return  string
 */

function getCertsPath($uid)
{
	return BASE_PATH . 'storage' . DS . 'certs' . DS . $uid . DS;
}

/**
 * 获取日志文件地址
 * @param 	integer  $uid  用户id
 * @return  string
 */

function getLogPath($file)
{
	return BASE_PATH . 'storage' . DS . 'logs' . DS . $file;
}

/**
 * 解压程序，支持.zip .tar 和 tar.gz
 * @param 	array             $fileInfo      ['ext'=>'扩展名','file'=>'文件地址'] 
 * @param 	string            $storagePath   解压的目录
 * @param 	string    		  $ext 			 文件后缀，如果填写，就删除目录下此文件后缀的所有文件
 * @param 	boolean    		  $flag 		 true，解压成功后删除此压缩包
 * @return  boolean
 */

function customExtract($fileInfo, $storagePath, $ext = '', $flag = false)
{
	$extension = [
		'gz'=>\Phar::GZ,
		'tar'=>\Phar::GZ,
		'zip'=>\Phar::ZIP
	];

	if( !isset( $extension[$fileInfo['ext']] ) ) return false;

	try{
		if( !empty($ext) ) array_map("unlink", glob($storagePath . '*.'.$ext));
		$pharType = $extension[$fileInfo['ext']];
		$phar 	  = new \PharData($fileInfo['file'], 0, 'phartest', $pharType);
		$result   = $phar->extractTo($storagePath);
		if($flag) @unlink($fileInfo['file']);
		return $result; 
	}catch(\Exception $e){
		return false;
	}
	return true;
}

function getImagePath($filename,$ext)
{
	return BASE_PATH . 'storage' . DS . 'image' . DS . $filename. '.' .$ext;
}

/**
 * 检测是否是windows系统
 * @return  string
 */
function isWindows()
{
	return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? true : false;
}

/**
 * git clone 仓库暂时不支持私有库
 * @return  string
 */

function gitClone()
{
	$isWindows = isWindows();
	
}