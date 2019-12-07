<?php

namespace app\common\model;


class AdminListModel extends BaseModel
{
    protected $table  = 'admin_list';
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

    public function getAdminListByRoleId($roleId){
        $where = array(
            "role_id" => $roleId
            );
        $list = $this->where($where)->select();
        foreach ($list as &$value) {
            $value = $value->toArray();
        }
        return $list;
    }
    
    public function getAdminRoleInfo($where=array(),$pageSize=15){
        $baseWhere = array(
            "admin_list.is_delete" => 0
        );
        $where = array_merge($where,$baseWhere);
        $field = array(
            "admin_list.*",
            "admin_role_list.id role_id",
            "admin_role_list.role_name",
        );
        return $this
                ->field($field)
                ->where($where)
                ->join("admin_role_list","admin_list.role_id = admin_role_list.id")
                ->order("admin_list.create_tm desc")
                ->paginate($pageSize);
    }
    
    public function getAdminCount($roleId){
        $where = array(
            "role_id" => $roleId,
            "is_delete" => 0
        );
        return $this->where($where)->count();
    }
    
    /*获取所有管理员*/
    public function getAll(){
        $where=array(
            'is_delete'=>0
        );
        $field = array(
            'id',
            "real_name"
        );
        $list = $this->field($field)->where($where)->select();
        foreach ($list as &$value) {
            $value = $value->toArray();
        }
        return $list;
        
    }
}

?>