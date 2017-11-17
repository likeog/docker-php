<?php

/** 
 * Docker Web Manager 用户Model
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */ 

namespace docker\dashboard\model;

use think\Model;

class User extends Model
{

    /**
     * @var 自动写入时间
     */

    protected $autoWriteTimestamp = true;

    /**
     * @var 新增时添加
     */

    protected $insert = [ 'status' => 1, 'group_id' => 10 ];

    /**
     * @var 更新时修改
     */

    protected $update = [ 'login_ip', 'login_time' ];

    /**
     * 登录操作
     * 
     * @param string $username
     * @param string $passowrd
     * @return array
     */

    public function login( $username, $password )
    {	
    	// 查询条件
   		$condition = ['email'=>$username,'password'=>md5($password)];

   		$this->startTrans();	
   		try{
   			// 查询用户
   			$userinfo = $this->where($condition)
    					 ->field('id,username,group_id,face,status,is_admin')
    					 ->find();
    		// 判断用户是否存在
    		if( $userinfo == NULL ) return ['status'=>1,'info'=>'账号密码错误！'];
            if( $userinfo->status != 1 ) return ['status'=>1,'info'=>'账号被禁用！'];
    		// 增加登录次数，更新登录时间和IP
    		$queryOne = $this->save(['login_count'=>['exp',  'login_count+1']],$condition);
    		// 记录日志
    		$queryTwo = setLog($userinfo->id,'用户登录');
    		// 事物操作
    		if( $queryOne && $queryTwo ){
    			$this->commit();
    			// 登录操作
    			doLogin($userinfo);
    			return ['status'=>0,'info'=>url('/dashboard/index')];
    		}else{
    			$this->rollback();
    		}
   		}catch (\Exception $e) {
		   $this->rollback();
		}
    	return ['status'=>1,'info'=>'登录失败请稍后重试'];
    }

    /**
     * 修改密码自动md5
     */

    public function setPasswordAttr($value)
    {
        return md5($value);
    }

    /**
     * 登录和修改是自动ip
     */

    public function setLoginIpAttr(){
    	 return request()->ip();
    }

    /**
     * 修改登录时间
     */

    public function setLoginTimeAttr(){
    	 return time();
    }

}