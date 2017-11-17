<?php
namespace docker\dashboard\controller;

class Index extends Base
{
    public function index()
    {
        return $this->fetch();
    }
}
