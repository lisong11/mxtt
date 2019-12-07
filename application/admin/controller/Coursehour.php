<?php

namespace app\admin\controller;

use think\Db;
use think\Paginator;
use think\Session;
use think\Loader;
use think\Controller;
use think\Request;

class Coursehour extends Common
{
    public function courseAdd()
    {
        return $this->fetch();
    }

    public function addDo()
    {
        $request = Request::instance();
        if ($request->isPost()) {
            $param = $request->param();
            $param['img_file_path'] = implode('&&', $param['course_imgs']);
            $res = $this->CourseHourModel->allowField(true)->save($param);
            if (!$res) {
                $this->error("出现错误");
            }
            $this->redirect("/coursehour/courseList.html");
        }
    }

    public function courseList()
    {
        $where = array();
        $list = $this->CourseHourModel->getCourseList($where, $this->getTruePaginator());
        $page = $list->render();
        $list = $list->toArray();
        $this->assign("list", $list);
        $this->assign("page", $page);
        return $this->fetch();
    }

    public function courseDetail()
    {
        $id = $this->request->get("course_id", "");
        $where['course_hour.course_id'] = $id;
        $info = $this->CourseHourModel->getCourseDetail($where);
        $list = explode('&&', $info['img_file_path']);
        $this->assign("list", $list);
        $this->assign("info", $info);
        return $this->fetch();
    }

    public function setDelete()
    {
        $data = $this->request->param();

        $course_id = $data['course_id'];

        $res = db('course_hour')->where("course_id='$course_id'")->update(['status' => -1]);
        if ($res === false) {
            $this->ajaxReturn(
                array(
                    "code" => 1,
                    "msg" => "删除出错",
                )
            );
        }
        $this->ajaxReturn(
            array(
                "code" => 0,
                "msg" => "删除成功",
            )
        );
    }
}