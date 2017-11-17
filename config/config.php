<?php

/** 
 * Docker Web Manager 配置文件
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */ 

return [
    // APP命名空间
    'app_namespace' => 'docker',
    // 视图输出字符串内容替换
    'view_replace_str'       => [
        '__PUBLIC__' => '/public/',
        '__STATIC__' => '/public/static/',
        '__CSS__'    => '/public/static/css/',
        '__IMG__'    => '/public/static/img/',
        '__JS__'     => '/public/static/js/',
    ],
    // 模板参数
    'template' => [
        'view_path'    => BASE_PATH.'templates/',
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{@',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '@}',
        // 标签库标签开始标记
        'taglib_begin' => '{@',
        // 标签库标签结束标记
        'taglib_end'   => '@}',
    ],
    // Session 后缀
    //'session' => [ 'prefix' => 'docker'],
    // 验证码长度
    'captcha' => [ 'length' => 4 ],
    // 加载自定义助手函数
    'extra_file_list' => [ THINK_PATH . 'helper' . EXT , CONF_PATH . 'helper' . EXT ],
    // Auth配置
    'auth_config' => [
        'admin_uid'  => 1,
        'auth_on'    => true,  
        'auth_type'  => 1,  
        'auth_group' => 'docker_auth_group',  
        'auth_rule'  => 'docker_auth_rule',  
        'auth_user'  => 'docker_user',
        'auth_group_access' => 'docker_auth_group_access'
    ],
    // 上传文件大小限制
    'upload_file_size' => 20480, //默认上传20MB
    // 多用户配置
    'mutil_user' => true,
    // build
    'build_auth_header' => '1cd5a059b3978',
];
