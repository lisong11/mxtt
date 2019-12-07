<?php

namespace app\admin\controller;

use think\Db;
use think\Session;
use think\Loader;
use think\Controller;

class User extends Common
{

    public function _initialize()
    {
        parent::_initialize();
    }

    public function userList()
    {
        //搜索
        $where = array();
        $searchField = $this->request->get("searchField", "");
        $searchValue = $this->request->get("searchValue", "");
        if (!empty($searchField) && !empty($searchValue)) {

            if ($searchField == 'realname') {
                $where["user.realname|user.nickname"] = array(
                    "like", "%" . $searchValue . "%"
                );
            } elseif ($searchField == 'phone') {
                $where["user.phone"] = array(
                    "like", "%" . $searchValue . "%"
                );
            }
        }
        $this->assign("searchField", $searchField);
        $this->assign("searchValue", $searchValue);
        $type = $this->request->get("status", "-1");
        if ($type >= 0) {
            $where["status"] = $type;
        }
        $this->assign("status", $type);
        $start_time = $this->request->get("start_time", "");
        $end_time = $this->request->get("end_time", "");
        if (!empty($start_time) && !empty($end_time)) {
            $where["create_time"] = array("between time", [$start_time, $end_time]);
        }
        $this->assign("start_time", $start_time);
        $this->assign("end_time", $end_time);
        $list = $this->userListModel->getuserList($where, $this->getTruePaginator());

        $page = $list->render();
        $list = $list->toArray();
        $this->assign("list", $list);
        $this->assign("page", $page);
        return $this->fetch();
    }

    public function edit()
    {
        $id = $this->request->get("id", 0);
        if ($id < 1) {
            $this->error("参数错误");
        }
        $user_id["user.user_id"] = $id;
        $info = $this->userListModel->getOneUser($user_id);
        $area = explode(',', $info['area']);
        $info['province'] = $area['0'];
        $info['city'] = $area['1'];
        $info['county'] = $area['2'];
        $basewhere['pid'] = 0;
        $Area = $this->AreaModel->getAllList($basewhere, $this->getTruePaginator());
        $this->assign("Area", $Area);
        $this->assign("info", $info);
        return $this->fetch();
    }

    public function editdo()
    {
        $data = $this->request->param();
        $result = $this->UserListModel->allowField(true)->save($data, ["user_id" => $data["user_id"]]);
        if ($result === false) {
            $this->error("编辑保存出现错误");
        }
        return $this->redirect('userList');

    }

    public function ajaxCheckRepeat()
    {
        $data = $this->request->param();

        if (!empty($data['phone'])) {

            $where['phone'] = $data['phone'];
            $where['user_id'] = $data['user_id'];
            $phone = db('user')->where('phone', $data['phone'])->select();
            $userphone = db('user')->where($where)->select();
            if (!empty($phone) && empty($userphone)) {
                return array(
                    "code" => 1,
                );
            }
        }
        if (!empty($data['email'])) {
            $where['email'] = $data['email'];
            $where['user_id'] = $data['user_id'];
            $email = db('user')->where('email', $data['email'])->select();
            $useremail = db('user')->where($where)->select();
            if (!empty($email) && empty($useremail)) {
                return array(
                    "code" => 1,
                );
            }
        }

    }

    public function setDelete()
    {
        $data = $this->request->param();

        $id = $data['user_id'];

        $res = db('user')->where("user_id='$id'")->update(['status' => -1]);
        if ($res === false) {
            $this->ajaxReturn(
                array(
                    "code" => 1,
                    "msg" => "删除人员出现错误",
                    "data" => $res)
            );
        }
        $this->ajaxReturn(
            array(
                "code" => 0,
                "msg" => "删除成功",
                "data" => $data)
        );
    }

    public function userDetail()
    {
        $id = $this->request->get("id", 0);

        if ($id < 1) {
            $this->error("参数错误");
        }
        $user_id["user.user_id"] = $id;
        $info = $this->userListModel->getOneUser($user_id);
        $area = explode(',', $info['area']);
        $info['province'] = $area['0'];
        $info['city'] = $area['1'];
        $info['county'] = $area['2'];
        $this->assign("info", $info);
        return $this->fetch();
    }

    public function addmes()
    {
        $this->sendmessage(user);
    }
}

?>