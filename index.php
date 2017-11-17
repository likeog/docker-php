<?php

/** 
 * Docker Web Manager
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */  


// 检测PHP环境
if(version_compare(PHP_VERSION,'5.4.0','<'))  die('require PHP > 5.4.0 !');
// 定义根目录
define("BASE_PATH",  realpath(dirname(__FILE__)).'/');
// 定义配置
define('CONF_PATH', BASE_PATH.'config/');
//define('BIND_MODULE','index');
// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
// 加载框架引导文件
require __DIR__ . '/framework/start.php';
