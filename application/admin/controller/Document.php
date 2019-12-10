<?php

namespace app\admin\controller;


use think\Db;
use think\Paginator;
use think\Session;
use think\Loader;
use think\Controller;
use think\Request;

class Document extends Common
{

    public function add()
    {
        return $this->fetch();
    }

    public function docAdd()
    {
        $request = Request::instance();
        if ($request->isPost()) {
            $param = $request->param();
            $params = explode('||', $param['param']);
            $result = array();
            $arr = array();
            foreach ($params as &$value) {
                $str = explode('&&', $value);
                $arr['param'] = $str['0'];
                if (!empty($str['1'])) {
                    $arr['type'] = $str['1'];
                }
                $result[] = $arr;
            }
            $param['params'] = json_encode($result);
            $res = $this->DocModel->allowField(true)->save($param);
            if (!$res) {
                $this->error("出现错误");
            }
            $this->redirect("/document/doclist");
        }
    }


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

    public function doclist()
    {
        $where = array();
        $list = $this->DocModel->getDocList($where, $this->getTruePaginator());
        $page = $list->render();
        $list = $list->toArray();
        $this->assign("list", $list);
        $this->assign("page", $page);
        return $this->fetch();
    }

    public function docDetail()
    {
        $id = $this->request->get("id", "");
        $where['doc_list.id'] = $id;
        $info = $this->DocModel->getDocDetail($where);
        $params = json_decode($info['params'], true);
        $this->assign("info", $info);
        $this->assign("params", $params);
        return $this->fetch();
    }

    public function edit()
    {
        $id = $this->request->get("id", "");
        $where['doc_list.id'] = $id;
        $info = $this->DocModel->getDocDetail($where);
        $params = json_decode($info['params'], true);
        foreach ($params as &$value) {
            $value = $value['param'] . "&&" . $value['type'];
        }
        $info['param'] = implode('||', $params);
        $this->assign("info", $info);
        return $this->fetch();
    }

    public function docEdit()
    {
        $request = Request::instance();
        if ($request->isPost()) {
            $param = $request->param();
            $params = explode('||', $param['param']);
            $result = array();
            $arr = array();
            foreach ($params as &$value) {
                $str = explode('&&', $value);
                $arr['param'] = $str['0'];
                if (!empty($str['1'])) {
                    $arr['type'] = $str['1'];
                }
                $result[] = $arr;
            }
            $param['params'] = json_encode($result);
            if (false === $this->DocModel->allowField(true)->isUpdate(true)->save($param, ['id' => $param['id']])) {
                $this->_return('1003', '数据处理失败');
            }

            $this->redirect("/document/doclist");
        }
    }

    public function setDelete()
    {
        $data = $this->request->param();

        $id = $data['id'];

        $res = db('doc_list')->where("id='$id'")->update(['status' => -1]);
        if ($res === false) {
            $this->ajaxReturn(
                array(
                    "code" => 1,
                    "msg" => "删除出错",
                )
            );
        }
        $this->ajaxReturn(
            array(
                "code" => 0,
                "msg" => "删除成功",
            )
        );
    }
}