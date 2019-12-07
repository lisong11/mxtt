<?php

namespace app\admin\controller;

use think\Db;
use think\Session;
use think\Loader;
use think\Controller;
class Admin extends Common
{

    public function _initialize()
    {
        parent::_initialize();
    }

    public function adminList()
    {
        //搜索
        $where = array();
        $searchField = $this->request->get("searchField", "");
        $searchValue = $this->request->get("searchValue", "");
        if (!empty($searchField) && !empty($searchValue)) {
            $where[$searchField] = array(
                "like", "%" . $searchValue . "%"
            );
        }
        $this->assign("searchField", $searchField);
        $this->assign("searchValue", $searchValue);
        $list = $this->AdminListModel->getAdminRoleInfo($where, $this->getTruePaginator());
        $page = $list->render();
        $list = $list->toArray();
        $this->assign("list", $list);
        $this->assign("page", $page);


        return $this->fetch();
    }

    public function setStatus()
    {
        $id = $this->request->get("id", 0);
        $set = $this->request->get("set", -1);
        if (!is_numeric($id) || !in_array($set, array("0", "1"))) {
            $this->error("参数错误");
        }
        $where = array(
            "id" => $id
        );
        $save = array(
            "status" => $set
        );
        if (false === $this->AdminListModel->save($save, $where)) {
            $this->error("更新状态失败");
        }
        $this->redirect(url("adminList", array("page" => $this->getCurrentPage())));
    }

    public function setDelete()
    {
        $data = $this->request->param();
        $res = $this->checkFormData("AdminList", "delete");
        if ($res['code'] != 0) {
            $this->ajaxReturn($res);
        }
        $save = array(
            'id' => $data['id'],
            'is_delete' => 1
        );
        $res = $this->AdminListModel->allowField(true)->isUpdate(true)->save($save);
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

    public function resetPassword()
    {
        $id = $this->request->get("id", 0);
        if (!is_numeric($id)) {
            $this->error("参数错误");
        }
        $info = $this->AdminListModel->get($id);
        if (empty($info)) {
            $this->error("未获取到信息");
        }
        $salt = $this->generate_random_string(64);
        $where = array(
            "id" => $id
        );
        $save = $this->getInitializePass();
        if (false === $this->AdminListModel->save($save, $where)) {
            $this->error("重置密码失败");
        }
        $this->redirect(url("adminList", array("page" => $this->getCurrentPage())));
    }

    public function add()
    {
        //获取所有有效的角色列表
        $role = $this->RoleListModel;//Loader::model("RoleList");
        $roleList = $role->getAllEffectiveList();

        $this->assign("roleList", $roleList);

        return $this->fetch();
    }

    public function Getdirector()
    {
        $name = $this->request->post("name", '');

        $page = $this->request->post("page", '1');
        $page_size = $this->request->post("page_size", '10');

        $limit = ($page - 1) * $page_size . ",{$page_size}";

        $sql_count = "select count(1) as count from (select name from school_user where `name` like '%{$name}%' ) as m";

        $total_count = Db::query($sql_count);

        $total_count = $total_count[0]['count'];

        $sql = "select name from (select name as name from school_user where `name` like '%{$name}%' ) as m limit $limit";
        $data = Db::query($sql);


        foreach ($data as &$value) {
            $map['name'] = $value['name'];
            $phone = db('school_user')->where($map)->value('phone');
            $value['name'] = $value['name'] . " " . $phone;
        }

        $this->_return('0', '成功', array(
            'total_count' => $total_count,
            'page' => $page,
            'page_size' => $page_size,
            'list' => $data
        ));

    }

    public function addDo()
    {
        $data = $this->request->param();

        $this->checkFormDataReturn("AdminList", "add");
        //获取初始密码get
        $iniPass = $this->getInitializePass($data['password']);
        $data = array_merge($data, $iniPass);

        $res = $this->AdminListModel->allowField(true)->save($data);
        if (!$res) {
            $this->error("添加管理员出现错误");
        }
        $this->redirect("adminList");
    }

    public function edit()
    {
        $id = $this->request->get("id", 0);
        if ($id < 1) {
            $this->error("参数错误");
        }
        $info = $this->AdminListModel->get($id);
        $info = $info->getData();

        $this->assign("info", $info);
        $roleList = $this->RoleListModel->getAllEffectiveList();

        $this->assign("roleList", $roleList);
        //dump($info);die;
        //客服列表
        $customer_service_list = $this->CustomerServiceModel->getList();
        $this->assign("customer_service_list", $customer_service_list);

        return $this->fetch();
    }

    public function editDo()
    {
        $data = $this->request->param();
        if ($data['role_id'] == 5) {

            $city_parter = $data['customer_service_id'];
            $city_parter1 = explode(' ', $city_parter);
            $map['name'] = $city_parter1['0'];


            $data['customer_service_id'] = db('school_user')->where($map)->value('id');
            if (empty($data['customer_service_id'])) {
                $map2['id'] = $data['id'];

                $data['customer_service_id'] = db('admin_list')->where($map2)->value('customer_service_id');

            }
        }
        if (empty($data['password'])) {
            $map1['id'] = $data['id'];
            $data['password'] = db('admin_list')->where($map1)->value('password');
        } else {
            $this->checkFormDataReturn("AdminList", "edit");
            $iniPass = $this->getInitializePass($data['password']);
            $data = array_merge($data, $iniPass);
        }


        $res = $this->AdminListModel->allowField(true)->save($data, array("id" => $data["id"]));
        if ($res === false) {
            $this->error("编辑保存出现错误");
        }
        $this->redirect("adminList");
    }

    public function changePassword()
    {
        $adminInfo = $this->getAdminInfo();
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $data = array_merge($data, array(
                "id" => $adminInfo["id"],
                "now_password" => $adminInfo["password"],
                "salt" => $adminInfo["salt"]
            ));
            $result = $this->AdminListValidate->scene("changepassword")->check($data);
            if (!$result) {
                $return = array(
                    "code" => 1,
                    "field" => $this->AdminListValidate->getErrorField(),
                    "msg" => $this->AdminListValidate->getError()
                );
            } else {
                $return = array(
                    "code" => 0,
                    "msg" => "验证成功"
                );
            }
            if ($this->request->isAjax()) {
                $this->ajaxReturn($return);
            } else {
                if (!$result) {
                    $this->error($return["msg"]);
                }
            }
            $save = $this->getInitializePass($data["new_password"]);
            $res = $this->AdminListModel->allowField(true)->save($save, array("id" => $data["id"]));
            if ($res === false) {
                $this->error("更改密码出现错误");
            }
            $this->redirect("/index/loginOut");
        } else {
            $this->assign("adminInfo", $adminInfo);
            return $this->fetch();
        }
    }

}

?>