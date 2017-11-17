<?php

/** 
 * Docker Web Manager 镜像Model
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */ 


namespace docker\dashboard\model;

use think\Model;

class Image extends Model
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
     * 更新镜像列表
     *
     * @return bool
     */
	
	public function updateImage($imageList)
	{
		$imageCache  = getModelCache(2);
		$compareList = array_column($imageList,'Id');
		$imageSame   = array_intersect($imageCache,$compareList);
		// 更新数据库
        $condition = ['uid'=>session('uid')];
		if( count($imageSame) != 0 ){
			$imageIds = array_keys($imageSame);
			if( count($imageIds) == 1 ){
				$condition['id'] = ['neq',$imageIds[0]];
			}else{
				$condition['id'] = ['not in',trim(implode(',', $imageIds),',')];
			}
			$this->where($condition)->delete();
		}else{
			$this->where($condition)->delete();
		}

		$insterData = [];

		foreach ($compareList as $id => $value ) {
			if( !in_array($value, $imageSame) ){
				foreach ($imageList[$id]['RepoTags'] as $n => $v) {
                    list($name,$tag) = explode(':', $v);
                    $insterData[] = [
                    	'config' => $imageList[$id],
                        'name'   => $name,
                        'sha'    => $value,
                        'tag'    => $tag,
                        'size'   => $imageList[$id]['Size']
                    ];
                }
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