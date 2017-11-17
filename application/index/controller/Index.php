<?php

/** 
 * Docker Web Manager 首页显示
 * 
 * @author     shao 
 * @version    0.1
 * @copyright  http://www.likeog.com All rights reserved. 
 */ 

namespace docker\index\controller;

use think\View;

class Index
{
    public function index(View $view)
    {
        return $view->fetch('index',[],\think\Config::get('view_replace_str'));
    }
}
