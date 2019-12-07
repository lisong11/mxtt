<?php

namespace app\common\model;


class MechanismModel extends BaseModel
{
    protected $table  = 'mechanism_list';
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

    public function getMechanismList($where=array(),$pageSize=30){
        $where["mechanism_list.status"] = array('gt',-1);
        $field = array(
            "mechanism_list.*",
        );
        return $this
            ->field($field)
            ->where($where)
            ->order("mechanism_list.id desc")
            ->paginate($pageSize);



    }

    public function getDetail($where=array(),$pageSize=30){

        $field = array(
            "mechanism_list.*",
            "admin_list.user_name"
        );
        return $this
            ->field($field)
            ->where($where)
            ->join('admin_list', 'admin_list.id=mechanism_list.user_id', 'left')
            ->find()
            ->toArray();;



    }

    public function homeWorkDetail($where=array(),$pageSize=30){

        $field = array(
            "course_homework.title as h_title",
            "course_homework.img_file_path as h_img"
        );
        return $this
            ->field($field)
            ->where($where)
            ->join('course_homework', 'course_homework.relation_id=course_list.relation_id', 'left')
            ->order("course_list.course_id desc")
            ->paginate($pageSize);



    }







}

?>