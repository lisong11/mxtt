<?php

namespace app\common\model;


class CourseModel extends BaseModel
{
    protected $table = 'course_list';
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

    public function getCourseList($where = array(), $pageSize = 30)
    {
        $where["course_list.status"] = array('gt', -1);
        $field = array(
            "course_list.*",
        );
        return $this
            ->field($field)
            ->where($where)
            ->order("course_list.course_id desc")
            ->paginate($pageSize);


    }

    public function getCourseDetail($where = array(), $pageSize = 30)
    {

        $field = array(
            "course_list.course_id",
            "course_list.title",
            "course_list.detail",
            "course_list.create_tm",
            "course_plan.img_file_path as img",
            "course_plan.course_type",
        );
        return $this
            ->field($field)
            ->where($where)
            ->join('course_plan', 'course_plan.relation_id=course_list.relation_id', 'left')
            ->order("course_list.course_id desc")
            ->paginate($pageSize)
            ->toArray();


    }

    public function homeWorkDetail($where = array(), $pageSize = 30)
    {

        $field = array(
            "course_homework.title as h_title",
            "course_homework.img_file_path as img",
            "course_homework.course_type",
        );
        return $this
            ->field($field)
            ->where($where)
            ->join('course_homework', 'course_homework.relation_id=course_list.relation_id', 'left')
            ->paginate($pageSize)
            ->toArray();


    }


}

?>