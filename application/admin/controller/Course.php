<?php

namespace app\admin\controller;
require VENDOR_PATH . 'demo.php';

use think\Db;
use think\Paginator;
use think\Session;
use think\Loader;
use think\Controller;
use think\Request;
use think\Cache;

class Course extends Common
{
    public function jd()
    {
        return $this->fetch();
    }

    public function videoUpload()
    {
        try {
            set_time_limit(0);
            $files = $_FILES;
            $Video = new \Video();
            $accessKeyId = 'LTAI4FjKUiEjMJbF3kKPPMM6';
            $accessKeySecret = 'ZmeadmaSP8w25f77HDmjnofD3z92dL';
            foreach ($files as $key => $value) {
                $vodClient = $Video->init_vod_client($accessKeyId, $accessKeySecret);
                $createRes = $Video->create_upload_video($vodClient);
                // 执行成功会返回VideoId、UploadAddress和UploadAuth
                $videoId = $createRes->VideoId;
                $uploadAddress = json_decode(base64_decode($createRes->UploadAddress), true);
                $uploadAuth = json_decode(base64_decode($createRes->UploadAuth), true);
                $ossClient = $Video->init_oss_client($uploadAuth, $uploadAddress);
                // 上传文件，注意是同步上传会阻塞等待，耗时与文件大小和网络上行带宽有关
                // $localFile = 'file:///E:/php/admin/public/uploads/videos/201912/8651575509300.mp4';
                $readUrl = "http://video.mxiaotu.com" . "/" . $uploadAddress["FileName"];
                $result = $Video->upload_local_file($ossClient, $uploadAddress, $value['tmp_name']);
                $this->ajaxReturn(array('code' => '200', 'url' => $readUrl));
            }

        } catch (Exception $e) {
            $this->ajaxReturn(array('code' => '400', 'msg' => $e));
        }
    }

    public function courseAdd()
    {
        cache::set('img_number', null);
        cache::set('video_number', null);
        return $this->fetch();
    }

    public function add1()
    {
        return $this->fetch();
    }

    public function addDo()
    {
        $request = Request::instance();
        if ($request->isPost()) {
            $param = $request->param();
            $relation_id = $this->create_no();
            $base_data['relation_id'] = $relation_id;
            $base_data['title'] = !empty($param['title']) ? $param['title'] : '';
            $base_data['detail'] = !empty($param['detail']) ? $param['detail'] : '';
            $hw_title = !empty($param['hw_title']) ? $param['hw_title'] : '';
            $hw_title1 = !empty($param['hw_title1']) ? $param['hw_title1'] : '';

            $res = $this->CourseModel->allowField(true)->save($base_data);
            if (!$res) {
                $this->error("出现错误");
            }
            cache::set('sort', 0);
            $cache = cache('sort');
            if (empty($cache)) {
                cache::set('sort', 1);
            } else {
                cache::inc('sort');
            }
            if (!empty($param['course_imgs1'])) {
                $img_number = cache('img_number');
                $video_number = cache('video_number');
                $plan_data = array();
                $img_res = array();
                $video_res = array();
                for ($i = 1; $i <= $img_number; $i++) {
                    $img = array();
                    cache::inc('sort');
                    $img['course_imgs'] = $param['course_imgs' . "" . $i];
                    $img['dir_type'] = 'img';
                    $img['sort'] = cache('sort');
                    $img_res[] = $img;
                }
                for ($i = 1; $i <= $video_number; $i++) {
                    cache::inc('sort');
                    $img = array();
                    $img['course_videos'] = $param['course_videos' . "" . $i];
                    $img['dir_type'] = 'video';
                    $img['sort'] = cache('sort');
                    $video_res[] = $img;
                }
                cache::set('sort', null);
                $img_file_path = array_merge_recursive($img_res, $video_res);

                $plan_data['img_file_path'] = json_encode($img_file_path);
                $plan_data['relation_id'] = $relation_id;
                $plan_data['course_type'] = 1;
                $res = $this->CoursePlanModel->allowField(true)->save($plan_data);
                if (!$res) {
                    $this->error("出现错误");
                }
            }

            if (!empty($param['h_imgs'])) {
                $homeWork1['img_file_path'] = implode('&&', $param['h_imgs']);
                $homeWork1['relation_id'] = $relation_id;
                $homeWork1['title'] = $hw_title;
                $homeWork1['course_type'] = 1;
                if (!empty($param['h_imgs1'])) {
                    $homeWork2['img_file_path'] = implode('&&', $param['h_imgs1']);
                    $homeWork2['relation_id'] = $relation_id;
                    $homeWork2['title'] = $hw_title1;
                    $homeWork2['course_type'] = 2;
                }
                $homeWork[] = $homeWork1;
                $homeWork[] = $homeWork2;
                $res = $this->HomeWorkModel->allowField(true)->saveAll($homeWork);
                if (!$res) {
                    $this->error("出现错误");
                }
            }
            $this->redirect("/course/courseList.html");
        }
    }

    public function courseList()
    {
        $where = array();
        $list = $this->CourseModel->getCourseList($where, $this->getTruePaginator());
        $page = $list->render();
        $list = $list->toArray();
        $this->assign("list", $list);
        $this->assign("page", $page);
        return $this->fetch();
    }

    public function courseDetail()
    {
        $id = $this->request->get("course_id", "");
        $where['course_list.course_id'] = $id;
        $info = $this->CourseModel->getCourseDetail($where);
        $img = json_decode($info['img'], true);
        $sortDetail = array_column($img, 'sort');
        array_multisort($sortDetail, SORT_ASC, $img);
        //作业
        $homeWork = $this->CourseModel->homeWorkDetail($where);

        foreach ($homeWork['data'] as &$value) {

            if ($value['course_type'] == 1) {
                $info['homework_img'] = explode('&&', $value['img']);
                $info['b_title'] = $value['h_title'];
            }
            if ($value['course_type'] == 2) {
                $info['homework_video'] = explode('&&', $value['img']);
                $info['a_title'] = $value['h_title'];
            }

        }
        $this->assign("img", $img);
        $this->assign("info", $info);
        return $this->fetch();
    }

    public function ajaxSort()
    {
        $data = $this->request->param();

        $plan_id = $data['plan_id'];
        $sort = $data['sort'];
        if ($data['direct'] == 1) {
            $new_sort = $sort - 1;
            $img_file_path = db('course_plan')->where("plan_id='$plan_id'")->value('img_file_path');
            $img = json_decode($img_file_path, true);
            foreach ($img as &$value) {
                switch ($value['sort']) {
                    case "$sort":
                        $value['sort'] = $new_sort;
                        break;
                    case "$new_sort":
                        $value['sort'] = $new_sort + 1;
                        break;
                }
            }
        } elseif ($data['direct'] == 2) {
            $new_sort = $sort + 1;
            $img_file_path = db('course_plan')->where("plan_id='$plan_id'")->value('img_file_path');
            $img = json_decode($img_file_path, true);
            foreach ($img as &$value) {
                switch ($value['sort']) {
                    case "$sort":
                        $value['sort'] = $new_sort;
                        break;
                    case "$new_sort":
                        $value['sort'] = $new_sort - 1;
                        break;
                }
            }
        }

        $update = json_encode($img);
        db('course_plan')->where("plan_id='$plan_id'")->update(['img_file_path' => $update]);
        return array(
            "code" => 200
        );
    }

    public function setDelete()
    {
        $data = $this->request->param();

        $course_id = $data['course_id'];

        $res = db('course_list')->where("course_id='$course_id'")->update(['status' => -1]);
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

    public function ajaxGetImg()
    {
        $cache = cache('img_number');
        if (empty($cache)) {
            cache::set('img_number', 1);
        } else {
            cache::inc('img_number');
        }
        $cache_key = cache('img_number');
        $data = 'img' . "" . $cache_key;
        $ul = 'priview' . "" . $cache_key;
        $img = 'course_imgs' . "" . $cache_key;
        return array(
            "code" => 1,
            "data" => $data,
            "ul" => $ul,
            "img" => $img
        );
    }

    public function ajaxGetVideo()
    {
        $cache = cache('video_number');
        if (empty($cache)) {
            cache::set('video_number', 1);
        } else {
            cache::inc('video_number');
        }
        $cache_key = cache('video_number');
        $data = 'video' . "" . $cache_key;
        $ul = 'privideo' . "" . $cache_key;
        $video = 'course_videos' . "" . $cache_key;
        return array(
            "code" => 1,
            "data" => $data,
            "ul" => $ul,
            "video" => $video
        );
    }

    public function permission()
    {
        $cateId = $this->request->get("id", 0);
        $this->assign("cateId", $cateId);
        $course_ids = $this->CourseCateModel->where('id', $cateId)->value('course_ids');
        $ids = explode(',',$course_ids);
        $course_list = db('course_list')->field('course_id,title')->select();
        foreach($course_list as &$value){
            if(in_array($value['course_id'], $ids)){
                $value['is_allow'] = 1;
            }
        }
        $nodeList['0']['name'] = '课时名称';
        $nodeList['0']['son'] = $course_list;

        $this->assign('nodeList', $nodeList);
        return $this->fetch();
    }

    public function editPermissionDo()
    {
        $data = $this->request->param();
        $save['course_ids'] = ltrim(implode(',', $data['nodeId']), ",");;
        if (false === $this->CourseCateModel->isUpdate(true)->save($save, ['id' => $data['cateId']])) {
            $this->_return('1003', '数据处理失败');
        }

        $this->redirect("/coursecate/catelist");
    }
}