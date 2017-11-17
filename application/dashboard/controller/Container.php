<?php

/** 
 * Docker Web Manager 容器模块
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */ 

namespace docker\dashboard\controller;

use docker\dashboard\model\Container as ContainerModel;

class Container extends Base
{

	private $docker = null;

	public function _initialize()
	{
		parent::_initialize();
		$paramUid = intval(input('uid'));
		if( $paramUid != 0 && session('uid') != $paramUid ){
			$this->docker = Dao( 'Container',[],$paramUid );
		}else{
			$this->docker = Dao('Container');
		}
		
	}

    /**
     * 容器列表
     *
     * @return think\View
     */

	public function lists()
	{
		$where = getListWhere();
		$this->view->list = model('Container')->where($where)->paginate(9);
		return $this->fetch();
	}

	/**
     * 容器操作管理[ 停止 启动 暂停 取消暂停]
     *
     * @return object json
     */

	public function manager($id, $method)
	{
		if( $this->request->isAjax() ){
			$type = [ 
				'stop'    => ['status'=>0,'info'=>'停止'], 
				'start'   => ['status'=>1,'info'=>'启动'], 
				'pause'   => ['status'=>2,'info'=>'暂停'], 
				'unpause' => ['status'=>1,'info'=>'取消暂停'] 
				];
			$container = input('post.id');
			$method    = input('post.method');
			if( array_key_exists($method, $type) ){
				$dockerSha = getContainerSha($container);
				if( !$dockerSha ) return json(['status'=>0,'info'=>'容器不存在']);
				$statusCode = $this->docker->{$method}($dockerSha);
				if( $statusCode == 204 ){
					ContainerModel::where('id',$container)->setField('status',$type[$method]['status']);
					return json(['status'=>1,'info'=>$type[$method]['info'].'成功']);
				}
				return json(['status'=>0,'info'=>$type[$method]['info'].'失败']);
			}
		}
		return json(['status'=>$id,'info'=>$method]);
	}

	/**
     * 容器重命名
     *
     * @return object json
     */

	public function rename()
	{
		if( $this->request->isAjax() ){
			$container  = input('post.pk');
			$rename 	= input('post.value');
			$dockerSha  = getContainerSha($container);
			if( !$dockerSha ) return json(['status'=>0,'info'=>'容器不存在']);
			$statusCode = $this->docker->rename($dockerSha,$rename);
			if( $statusCode == 204 ){
				ContainerModel::where('id',$container)->setField('name',$rename);
				return json(['status'=>1]);
			}
			return json(['status'=>0,'info'=>'修改失败']);
		}else{
			return json(['status'=>0,'info'=>'非法获取']);
		}
	}

	/**
     * 导出容器
     *
     * @return stream
     */

	public function export($id)
	{
		$tarName   = ContainerModel::where('id',intval($id))->value('name');
		$dockerSha = getContainerSha(intval($id));
		if( !$tarName || !$dockerSha ) $this->error('容器不存在');
		$tarName   = trim($tarName,'/').'.tar.gz';
		$tarStream = $this->docker->export($dockerSha); 
		Header( "Content-type  :  application/octet-stream "); 
		Header( "Accept-Ranges :  ".strlen($tarStream)."bytes ");
		Header( "Content-Disposition:attachment;filename={$tarName}"); 
		echo $tarStream;
	}


	/**
     * 更新容器列表
     *
     * @return stream
     */

	public function updateContainer()
	{
		$serverStatus = $this->docker->ping();
		if( $serverStatus != 200 ){
			return json(['status'=>0,'info'=>'Docker服务器连接失败，请检查配置']);
		}
		$containerList  = $this->docker->lists(['all'=>true]);
		$containerModel = new ContainerModel();
		$result = $containerModel->updateContainer($containerList);
		if( $result ) return json(['status'=>1,'info'=>'更新列表成功']);
		return json(['status'=>0,'info'=>'更新列表失败请稍后重试']);
	}
}
