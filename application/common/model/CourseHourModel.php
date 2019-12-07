<?php

namespace app\common\model;


class CourseHourModel extends BaseModel
{
    protected $table  = 'course_hour';
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

    public function getCourseList($where=array(),$pageSize=30){
        $where["course_hour.status"] = array('gt',-1);
        $field = array(
            "course_hour.*",
        );
        return $this
            ->field($field)
            ->where($where)
            ->order("course_hour.course_id desc")
            ->paginate($pageSize);



    }

    public function getCourseDetail($where=array(),$pageSize=30){

        $field = array(
            "course_hour.course_id",
            "course_hour.title",
            "course_hour.detail",
            "course_hour.create_tm",
            "course_hour.img_file_path",
        );
        return $this
            ->field($field)
            ->where($where)
            ->order("course_hour.course_id desc")
            ->find()
            ->toArray();



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