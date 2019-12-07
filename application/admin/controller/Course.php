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
            dump($param);
            die;
            $relation_id = $this->create_no();
            $base_data['relation_id'] = $relation_id;
            $base_data['title'] = !empty($param['title']) ? $param['title'] : '';
            $base_data['detail'] = !empty($param['detail']) ? $param['detail'] : '';
            $hw_title = !empty($param['hw_title']) ? $param['hw_title'] : '';
            $hw_title1 = !empty($param['hw_title1']) ? $param['hw_title1'] : '';
            $imgs_length = count($param)-5;

//            $res = $this->CourseModel->allowField(true)->save($base_data);
//            if (!$res) {
//                $this->error("出现错误");
//            }
            if (!empty($param['course_imgs1'])) {
                $img_data = array();
                for($i=1;$i<=$imgs_length;$i++){
                    $img = array();
                    $img['img_file_path'] = implode('&&', $param['course_imgs'."".$i]);
                    $img['relation_id'] = $relation_id;
                    $img['course_type'] = 1;
                    $img['dir_level'] = $i;
                    $img_data[$i] = $img;
                }
                $res = $this->CoursePlanModel->allowField(true)->saveAll($img_data);
                dump($img_data);
                die;
//                $video_data = array();
//                if (!empty($param['videos'])) {
//                    $video_data['img_file_path'] = implode('&&', $param['videos']);
//                    $video_data['relation_id'] = $relation_id;
//                    $video_data['course_type'] = 2;
//                }
//                $plan_data[] = $img_data;
//                $plan_data[] = $video_data;
                $res = $this->CoursePlanModel->allowField(true)->saveAll($img_data);
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
        $CourseDetail = $this->CourseModel->getCourseDetail($where);
        $info = array();
        foreach ($CourseDetail['data'] as &$value) {

            if ($value['course_type'] == 1) {
                $info['plan_img'] = explode('&&', $value['img']);
            }
            if ($value['course_type'] == 2) {
                $info['plan_video'] = explode('&&', $value['img']);
            }
            $info['course_id'] = $value['course_id'];
            $info['title'] = $value['title'];
            $info['detail'] = $value['detail'];
            $info['create_tm'] = $value['create_tm'];
        }

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
        $this->assign("info", $info);
        return $this->fetch();
    }

    public function sortDetail()
    {
        $id = $this->request->get("course_id", "");
        $where['course_list.course_id'] = $id;
        $CourseDetail = $this->CourseModel->getCourseDetail($where);
        $info = array();
        foreach ($CourseDetail['data'] as &$value) {

            if ($value['course_type'] == 1) {
                $info['plan_img'] = explode('&&', $value['img']);
            }
            if ($value['course_type'] == 2) {
                $info['plan_video'] = explode('&&', $value['img']);
            }
            $info['title'] = $value['title'];
            $info['detail'] = $value['detail'];
            $info['create_tm'] = $value['create_tm'];
            $info['course_id'] = $value['course_id'];
        }

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
        $this->assign("info", $info);
        return $this->fetch();
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
//        $cache = cache('video_number');
//        if (empty($cache)) {
//            cache::set('video_number', 1);
//        } else {
//            cache::inc('video_number');
//        }
//        $cache_key = cache('video_number');
//        $data = 'video' . "" . $cache_key;
//        $ul = 'privideo' . "" . $cache_key;
//        $img = 'course_videos' . "" . $cache_key;
        return array(
            "code" => 1,
            "data" => 'video1',
            "ul" => 'privideo1',
            "img" => 'course_videos1'
        );
    }

    public function coursete()
    {
        return $this->fetch();
    }
}