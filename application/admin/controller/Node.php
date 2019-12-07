<?php

namespace app\admin\controller;

use think\Session;
use think\Loader;

class Node extends Common
{

    public function _initialize()
    {
        parent::_initialize();
    }

    public function nodeList()
    {
        $data = $this->request->param();
        if (isset($data["parentId"]) && is_numeric($data["parentId"])) {
            $parentId = $data["parentId"];
        } else {
            $parentId = 0;
        }
        //鑾峰彇姝よ妭鐐逛笅鐨勫瓙鑺傜偣鍒楄〃
        $where = array(
            "parent_node_id" => $parentId
        );
        $list = $this->NodeListModel->where($where)->paginate($this->getTruePaginator());
        $page = $list->render();
        $list = $list->toArray();

        $this->assign("parentId", $parentId);
        $this->assign("list", $list);
        $this->assign("page", $page);
        return $this->fetch();
    }

    public function add()
    {
        $parentId = $this->request->get("parentId", 0);
        if ($parentId > 0) {
            $info = $this->NodeListModel->get($parentId);
            $node_deep = $info->getData("node_deep");
        } else {
            $node_deep = 0;
        }
        $nowDeep = intval($node_deep) + 1;
        $this->assign("parentId", $parentId);
        $this->assign("nowDeep", $nowDeep);
        $this->assign("nodeDeep", \think\Config::get('NODE_DEEP'));

        return $this->fetch();
    }

    public function addDo()
    {
        $data = $this->request->param();
        $this->checkFormDataReturn("NodeList", "add");

        $res = $this->NodeListModel->allowField(true)->save($data);
        if (!$res) {
            $this->error("娣诲姞鑺傜偣淇濆瓨鍑虹幇閿欒");
        }
        $this->redirect(url("nodeList", array("parentId" => $data["parentId"])));
    }

    public function edit()
    {
        $nodeId = $this->request->get("nodeId", 0);
        if ($nodeId < 1) {
            $this->error("鍙傛暟閿欒");
        }
        $info = $this->NodeListModel->get($nodeId);
        $info = $info->getData();
        $this->assign("info", $info);
        $this->assign("nodeDeep", \think\Config::get('NODE_DEEP'));

        return $this->fetch();
    }

    public function editDo()
    {
        $data = $this->request->param();
        $this->checkFormDataReturn("NodeList", "edit");
        $res = $this->NodeListModel->allowField(true)->save($data, array("id" => $data["id"]));
        if ($res === false) {
            $this->error("缂栬緫鑺傜偣淇濆瓨鍑虹幇閿欒");
        }
        $this->redirect(url("nodeList", array("parentId" => $data["parentId"])));
    }

    public function ajaxGetParentNodeInfo()
    {
        if (!$this->request->isAjax()) {
            $this->error("璇锋眰绫诲瀷閿欒");
        }
        $nodeDeep = $this->request->post("nodeDeep", 0);
        $nodeDeep = intval($nodeDeep) - 1;
        if ($nodeDeep == 0) {
            $data = array(
                array(
                    "id" => 0,
                    "node_name" => "已是最大节点",
                    "node_value" => ""
                )
            );
        } else {
            $where = array(
                "node_deep" => $nodeDeep
            );
            $field = array(
                "id",
                "node_name",
                "node_value"
            );
            $data = $this->NodeListModel->field($field)->where($where)->select();
            foreach ($data as &$value) {
                $value = $value->toArray();
            }
        }

        $return = array(
            "code" => 0,
            "msg" => "鎴愬姛",
            "data" => $data
        );
        $this->ajaxReturn($return);
    }

}

?>