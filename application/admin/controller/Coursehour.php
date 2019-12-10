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

    public function permission()
    {
        $cateId = $this->request->get("id", 0);
        $this->assign("cateId", $cateId);
        $course_ids = $this->CourseCateModel->where('id', $cateId)->value('course_ids');
        $ids = explode(',',$course_ids);
        $course_list = db('course_hour')->field('course_id,title')->select();
        foreach($course_list as &$value){
            if(in_array($value['course_id'], $ids)){
                $value['is_allow'] = 1;
            }
        }
        $nodeList['0']['name'] = '课程名称';
        $nodeList['0']['son'] = $course_list;

        $this->assign('nodeList', $nodeList);
        return $this->fetch();
    }

    public function editPermissionDo()
    {
        $data = $this->request->param();
        $save['course_ids'] = ltrim(implode(',', $data['nodeId']), ",");
        if (false === $this->CourseCateModel->isUpdate(true)->save($save, ['id' => $data['cateId']])) {
            $this->_return('1003', '数据处理失败');
        }

        $this->redirect("/coursecate/clist");
    }

}