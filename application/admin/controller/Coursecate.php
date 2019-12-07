<?php

namespace app\admin\controller;


use think\Db;
use think\Paginator;
use think\Session;
use think\Loader;
use think\Controller;
use think\Request;

class Coursecate extends Common
{

    public function add()
    {
        return $this->fetch();
    }

    public function addDo()
    {
        $data = $this->request->param();
        dump($data);die;
    }


}