<?php

/** 
 * Docker Web Manager 任务Model
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */ 


namespace docker\dashboard\model;

use think\Model;

class Job extends Model
{	

	/**
     * @var 自动写入时间
     */

    protected $autoWriteTimestamp = true;

    /**
     * @var 新增时自动完成
     */

    protected $insert = [ 'uid' , 'status', 'config' ];


    /**
     * 获取配置文件
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

    public function setStatusAttr()
    {
        return 1;
    }

}