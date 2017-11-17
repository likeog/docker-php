<?php

/** 
 * Docker Web Manager 容器Model
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */ 


namespace docker\dashboard\model;

use think\Model;

class Container extends Model
{	

	/**
     * @var 自动写入时间
     */

    protected $autoWriteTimestamp = true;

    /**
     * @var 新增时自动完成
     */

    protected $insert = [ 'uid' , 'username', 'config' ];

 	/**
     * 更新容器列表
     *
     * @return bool
     */
	
	public function updateContainer($containerList)
	{
		$containerCache = getModelCache();
		$compareList    = array_column($containerList,'Id');
		$ContainerSame  = array_intersect($containerCache,$compareList);
		// 更新数据库
		$condition = ['uid'=>session('uid')];
		if( count($ContainerSame) != 0 ){
			$ContainerIds   = array_keys($ContainerSame);
			if( count($ContainerIds) == 1 ){
				$condition['id'] = ['neq',$ContainerIds[0]];
			}else{
				$condition['id'] = ['not in',trim(implode(',', $ContainerIds),',')];
			}
			$this->where($condition)->delete();
		}else{
			$this->where($condition)->delete();
		}

		$insterData = [];
		foreach ($compareList as $id => $value ) {
			if( !in_array($value, $ContainerSame) ){
				$status = 0;
            	if( $containerList[$id]['State'] == 'running' ) $status = 1;
				$insterData[] = [
					'config' => $containerList[$id],
   					'sha'    => $value,
	                'name'   => ltrim($containerList[$id]['Names'][0],'/'),
	                'image'  => $containerList[$id]['Image'],
	                'status' => $status
				];
			}
		}

		if( count($insterData) > 0 ){
			$result = $this->saveAll($insterData,true);
			return $result;
		}
		return true;
	}

	/**
     * 获取Config字段的时候，自动unserialize
     */

	public function getConfigAttr($value)
    {
        return unserialize($value);
    }


	/**
     * 新增时自动修改Config
     */

	public function setConfigAttr($value)
    {
        return serialize($value);
    }

    /**
     * 新增时自动设置uid
     */

    public function setUidAttr()
    {
        return session('uid');
    }

    /**
     * 新增时自动设置username
     */

    public function setUsernameAttr()
    {
        return session('username');
    }
}