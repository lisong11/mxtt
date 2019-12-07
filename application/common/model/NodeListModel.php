<?php

namespace app\common\model;



class NodeListModel extends BaseModel
{

    // 设置当前模型对应的完整数据表名称
    protected $table  = 'admin_node_list';
    protected $insert = array('create_tm');

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

    //添加数据时候自动添加 创建时间字段
    protected function setCreateTmAttr($value)
    {
        return date("Y-m-d H:i:s");
    }

    public function getNodeDeepAttr($value)
    {
        $deep = \think\Config::get("NODE_DEEP");
        return $deep[$value];
    }

    public function getChildrenNodeList($parentId)
    {
        $where = array(
            "parent_node_id" => $parentId
        );
        return $this->where($where)->select();
    }

    //获取某一节点的最顶级节点
    public function getTopParentId($nodeId, $status = false)
    {
        //获取当前节点的信息。
        if ($status !== false) {
            $where = array(
                "status" => $status,
                "id"     => $nodeId
            );
            $info  = $this->where($where)->find();
        }
        else {
            $info = $this->get($nodeId);
        }
        $info = $info->toArray();
        $res  = $info;
        if ($info["parent_node_id"] != 0) {
            $res = $this->getTopParentId($info["parent_node_id"]);
        }
        return $res;
    }

    public function getRoleAllowNodeList($roleId)
    {
        $where = array(
            "role_id" => $roleId
        );
        $field = array(
            "admin_node_list.id",
            "node_name",
            "node_value",
            "node_icon",
            "parent_node_id",
            "node_deep");
        $list  = $this
                ->field($field)
                ->join("admin_role_node_list", "admin_role_node_list.node_id = admin_node_list.id")
                ->where($where)
                ->select();
        foreach ($list as &$value) {
            $value = $value->toArray();
        }
        return $list;
    }

    public function getRoleAllowMenuNodeList($roleId)
    {
        $where = array(
            "status"    => 1,
            "node_deep" => array("in", "1,2"),
            "is_display" => 1
        );
        return $this->getAllRoleTreeNodeList($roleId, $where);
    }

    public function getMenuNodeList($field)
    {
        $where = array(
            "status"    => 1,
            "node_deep" => array("in", "1,2"),
            "is_display" => 1
        );
        $list  = $this->field($field)->where($where)->order("order_by desc")->select();
        $list  = $this->resetArrayKey($list);
        return $this->generateTree($list);
    }

    public function getAllRoleTreeNodeList($roleId, $where = array())
    {
        $where = array_merge($where, array(
            "status" => 1   
        ));
        $field = array(
            "admin_node_list.id",
            "node_name",
            "node_value",
            "node_icon",
            "parent_node_id",
            "node_deep",
            "if(admin_role_node_list.id is null,0,1) AS is_allow");
        $list  = $this
                ->field($field)
                ->join("admin_role_node_list", "admin_role_node_list.node_id = admin_node_list.id and admin_role_node_list.role_id = ".$roleId,"left")
                ->where($where)
                ->order("order_by desc")
                ->select();
        $list  = $this->resetArrayKey($list);
        return $this->generateTree($list);
    }

    //获取全部的节点数组，递归形式展示
    public function getAllTreeNodeList($status = false, $field = array("id", "node_name", "node_value", "node_icon", "parent_node_id", "node_deep"))
    {
        if ($status !== false) {
            $where = array(
                "status" => $status
            );
            $list  = $this->field($field)->where($where)->select();
        }
        else {
            $list = $this->field($field)->select();
        }
        $list = $this->resetArrayKey($list);
        return $this->generateTree($list);
    }

    /**
     * 重组节点数组， 用id作为key值
     * @param type $list
     * @return type
     */
    protected function resetArrayKey($list)
    {
        $res = array();;
        foreach ($list as $k => $v) {
            $res[$v['id']] = $v->toArray();
        }
        return $res;
    }

    //递归获取无限级分类，引用传值写法，针对已有数组进行处理
    protected function generateTree($items)
    {
        $tree = array();
        foreach ($items as $k => $item) {
            if (isset($items[$item["parent_node_id"]])) {
                $items[$item['parent_node_id']]['son'][] = &$items[$k];
            }
            else {
                $tree[] = &$items[$k];
            }
        }
        return $tree;
    }

    //根据分类id获取面包屑数组
    public function getCrumbsByNodeId($nodeId=0, $status = false, $field = array("id", "node_name", "node_value", "node_icon", "parent_node_id", "node_deep"), &$res = array())
    {
        if($nodeId == 0){
            return array();
        }
        //查库，获取该node_id的名称和父id
        $where = array(
            "id" => $nodeId
        );
        if ($status !== false) {
            $where["status"] = $status;
        }
        $info  = $this->field($field)->where($where)->find();
        $info = $info->toArray();
        $res[] = array(
            "node_id"    => $info["id"],
            "node_name"  => $info["node_name"],
            "node_value" => $info["node_value"],
            "node_icon"  => $info["node_icon"]
        );
        krsort($res); 
        if ($info["parent_node_id"] != 0) {
            $this->getCrumbsByNodeId($info["parent_node_id"], $status, $field, $res);
        }
        return $res;
    }

}

?>