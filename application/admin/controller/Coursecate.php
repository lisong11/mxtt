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

    public function cateadd()
    {
        return $this->fetch();
    }

    //课时
    public function addDo()
    {
        $data = $this->request->param();
        $data['cate_type'] = 1;
        $res = $this->CourseCateModel->allowField(true)->save($data);
        if (!$res) {
            $this->error("出现错误");
        }
        $this->redirect("/coursecate/catelist?cate_type=1");
    }

    //课程
    public function addCate()
    {
        $data = $this->request->param();
        $data['cate_type'] = 2;
        $res = $this->CourseCateModel->allowField(true)->save($data);
        if (!$res) {
            $this->error("出现错误");
        }
        $this->redirect("/coursecate/catelist?cate_type=2");
    }

    //课时列表
    public function catelist()
    {
        $param = $this->request->param();
        $where['cate_type'] = 1;
        $list = $this->CourseCateModel->getCateList($where, $this->getTruePaginator());
        $page = $list->render();
        $list = $list->toArray();
        $this->assign("list", $list);
        $this->assign("page", $page);
        return $this->fetch();
    }

    //课程列表
    public function clist()
    {
        $param = $this->request->param();
        $where['cate_type'] = 2;
        $list = $this->CourseCateModel->getCateList($where, $this->getTruePaginator());
        $page = $list->render();
        $list = $list->toArray();
        $this->assign("list", $list);
        $this->assign("page", $page);
        return $this->fetch();
    }

}