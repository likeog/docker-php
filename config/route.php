<?php

/** 
 * Docker Web Manager 路由文件
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */ 

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    // 登录
    '/signin'   => [ 'Dashboard/Common/login'  ,['method'=>'get|post']],
    '/signout'  => [ 'Dashboard/Common/logout' ,['method'=>'get']],
    '/getUser'  => [ 'Dashboard/Common/getUser' ,['method'=>'get']],
    '/build'    => [ 'Dashboard/Common/buildImage' ,['method'=>'post']],
    '/dashboard/image/build_log/:id' => [ 'Dashboard/image/build_log' ,['method'=>'get'], ['id' => '\d+']],
];
