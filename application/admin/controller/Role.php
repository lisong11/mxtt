<?php

namespace app\admin\controller;

use think\Session;
use think\Loader;

class Role extends Common
{

    public function _initialize()
    {
        parent::_initialize();
    }

    public function roleList()
    {
        $where = array(
            "is_delete" => 0);
        $list = $this->RoleListModel->where($where)->order("create_tm desc")->paginate($this->getTruePaginator());
        $page = $list->render();
        $list = $list->toArray();
        //获取当前角色 是否有管理员
        foreach ($list["data"] as &$l){
            $l["adminCount"] = $this->AdminListModel->getAdminCount($l["id"]);
        }
        $this->assign("list", $list);
        $this->assign("page", $page);
        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }

    public function addDo()
    {
        $data   = $this->request->param();
        $this->checkFormDataReturn("RoleList","add");
        $res = $this->RoleListModel->allowField(true)->save($data);


        if (!$res) {
            $this->error("添加角色出现错误");
        }
        $this->redirect(url("roleList"));
    }

    public function edit()
    {
        $id = $this->request->get("id", 0);
        if ($id < 1) {
            $this->error("参数错误");
        }
        $info = $this->RoleListModel->get($id);
        $info = $info->getData();
        // dump($info);die;
        $this->assign("info", $info);
        return $this->fetch();
    }

    public function editDo()
    {
        $data   = $this->request->param();
        $this->checkFormDataReturn("RoleList","edit");
        $res = $this->RoleListModel->allowField(true)->save($data, array("id" => $data["id"]));
        if ($res === false) {
            $this->error("编辑角色保存出现错误");
        }
        $this->redirect(url("roleList"));
    }

    public function editPermission()
    {
        $roleId = $this->request->get("id", 0);
        if ($roleId < 1) {
            $this->error("参数错误");
        }

        $this->assign("roleId", $roleId);
        $nodeList = $this->NodeListModel->getAllRoleTreeNodeList($roleId);
        $this->assign("nodeList", $nodeList);
        $roleInfo = $this->RoleListModel->get($roleId);

        $this->assign("roleInfo",$roleInfo);
        return $this->fetch();
    }

    public function editPermissionDo()
    {
        $data     = $this->request->param();
        $roleId   = $data["roleId"];
        $nodeId   = $data["nodeId"];
        $roleNode =$this->AdminRoleNodeListModel;// Loader::model("AdminRoleNodeList");
        //先删除原有的节点关联，再添加现在的节点关联
        //先获取已经关联的节点
        $hasNode = $roleNode->getNodeByRole($roleId);
        $delNode = array_diff($hasNode,$nodeId);
        $addNode = array_diff($nodeId,$hasNode);
        foreach ($delNode as $node){
            $delWhere = array(
                "role_id" => $roleId,
                "node_id" => $node
            );
            $del      = $roleNode->where($delWhere)->delete();
            if ($del === false) {
                $this->error("删除原有关联失败");
            }
        }

        foreach ($addNode as $node) {
            $data = array(
                "role_id" => $roleId,
                "node_id" => $node
            );
            $add  = $roleNode->data($data, true)->isUpdate(false)->save();
            if (!$add) {
                $this->error("添加权限关联失败");
            }
        }
        $this->redirect(url("roleList"));
    }

    public function setDelete()
    {
        $data = $this->request->param();
        $res = $this->checkFormData("RoleList", "delete");
        if ($res['code'] != 0) {
            $this->ajaxReturn($res);
        }
        $save = array(
            'id' => $data['id'],
            'is_delete' => 1
        );
        $res = $this->RoleListModel->allowField(true)->isUpdate(true)->save($save);
        if ($res === false) {
            $this->ajaxReturn(
                array(
                    "code" => 1,
                    "msg" => "删除角色出现错误",
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

}

?>