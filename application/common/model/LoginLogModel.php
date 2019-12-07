<?php

namespace app\common\model;


class LoginLogModel extends BaseModel
{
    protected $table  = 'admin_login_log';
    protected $insert = array('create_time');
    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }
    
    //添加数据时候自动添加 创建时间字段
    protected function setCreateTimeAttr($value)
    {
        return date("Y-m-d H:i:s");
    }

    public function getAllList($basewhere=[],$pageinate=30){
        $where=array();
        $field=array(
            'admin_login_log.*',
            'admin_list.user_name',
        );
        $basewhere=array_merge($where,$basewhere);
        $list=$this->field($field)
            ->join('admin_list','admin_login_log.admin_id=admin_list.id','left')
            ->where($basewhere)
            ->order('admin_login_log.id desc')
            ->paginate($pageinate,false,['query'=>request()->param()]);
        return $this->regroupToArray($list);
    }

}

?>