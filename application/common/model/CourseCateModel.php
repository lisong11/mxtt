<?php

namespace app\common\model;


class CourseCateModel extends BaseModel
{
    protected $table = 'course_cate';
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

    public function getCateList($where = array(), $pageSize = 30)

    {
        $field = array(
            "course_cate.*",
        );
        return $this
            ->field($field)
            ->where($where)
            ->order("course_cate.id desc")
            ->paginate($pageSize);


    }


}

?>