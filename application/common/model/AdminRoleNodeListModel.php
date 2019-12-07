<?php
namespace app\common\model;

class AdminRoleNodeListModel extends BaseModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table  = 'admin_role_node_list';
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

    public function getNodeByRole($roleId=0){
        $where = array(
            'role_id' => $roleId
        );
        $list = $this->field('node_id')->where($where)->select();
        $res = array();
        foreach ($list as $l){
            $res[] = $l->node_id;
        }
        return $res;
    }

}

?>