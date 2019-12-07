<?php

namespace app\admin\controller;

use think\Db;
use think\Paginator;
use think\Session;
use think\Loader;
use think\Controller;
use think\Request;

class Mechanism extends Common
{
    public function add()
    {
        return $this->fetch();
    }

    public function addDo()
    {
        $request = Request::instance();
        if ($request->isPost()) {
            $param = $request->param();
            $param['img_file_path'] = implode("&&", $param['course_imgs']);
            $userInfo = $this->getInitializePass($param['password']);
            $userInfo['user_name'] = $param['user_name'];
            $userInfo['role_id'] = 3;
            $user_id = $this->AdminListModel->insertGetId($userInfo);
            $param['user_id'] = $user_id;
            $res = $this->MechanismModel->allowField(true)->save($param);
            if (!$res) {
                $this->error("出现错误");
            }

            $this->redirect("/mechanism/mechanismList.html");
        }
    }

    public function mechanismList()
    {
        $where = array();
        $list = $this->MechanismModel->getMechanismList($where, $this->getTruePaginator());
        $page = $list->render();
        $list = $list->toArray();
        $this->assign("list", $list);
        $this->assign("page", $page);
        return $this->fetch();
    }

    public function mechanismDetail()
    {
        $id = $this->request->get("id", "");
        $where['mechanism_list.id'] = $id;
        $info = $this->MechanismModel->getDetail($where);
        $img = explode("&&",$info['img_file_path']);
        foreach($img as &$value){
            $img_res = array();
            $img_res['img'] = $value;
            $list[] =  $img_res;
        }
//        dump($list);die;
        $this->assign("list", $list);
        $this->assign("info", $info);
        return $this->fetch();
    }

    public function setDelete()
    {
        $data = $this->request->param();

        $id = $data['id'];

        $res = db('mechanism_list')->where("id='$id'")->update(['status' => -1]);
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