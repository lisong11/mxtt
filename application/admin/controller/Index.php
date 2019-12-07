<?php

namespace app\admin\controller;

use think\Session;

class Index extends Common
{

    public function _initialize()
    {

    }

    public function index()
    {
        //判断是否登录
        if (!$this->isLogin()) {
            $this->redirect("login");
        }
        $this->assignAdminInfo();
        //获取菜单列表
        $this->assignAdminMenu();
        $this->assignAdminMenuFirst();
        return $this->fetch();
    }

    public function login()
    {
        $user_name = "";
        if (\think\Cookie::has("user_name")) {
            $user_name = \think\Cookie::get("user_name");
        }
        $password = "";
        if (\think\Cookie::has("password")) {
            $password = \think\Cookie::get("password");
        }
        $this->assign("user_name", $user_name);
        $this->assign("password", $password);
        $this->view->engine->layout(false);
        return $this->fetch();
    }

    public function ajaxCheckPass()
    {
        $data = $this->request->param();
        if (!empty($data['user_name']) && !empty($data['password'])) {
            $password = $this->getInitializePass($data['password']);
            $where = array(
                'password' => $password['password'],
                'user_name'=>$data['user_name']
            );
            $user = $this->AdminListModel->where($where)->select();
            if (empty($user)) {
                return array(
                    "code" => 1,
                );
            }
        }
    }

    public function loginDo()
    {
        if (!$this->request->isAjax()) {
            $this->error("请求类型错误");
        }
        $data = $this->request->param();
//        $res  = $this->checkFormData("AdminList", "login");
//        if ($res["code"] != 0) {
//            $this->ajaxReturn($res);
//        }
        //检查验证码
        $res = $this->validate($data, array(
            'captcha|验证码' => 'require|captcha'
        ));
        if ($res !== true) {
            $return = array(
                "code" => 1,
                "field" => "captcha",
                "msg" => $res
            );
            $this->ajaxReturn($return);
        }


        //验证成功进行登录信息的注入session
        $adminList = $this->AdminListModel;//\think\Loader::model('AdminList');
        $where = array(
            "user_name" => $data["user_name"]
        );
        $adminInfo = $adminList->where($where)->find();
        Session::delete("admin");
        Session::delete("adminMenu");
        Session::delete("adminNode");
        Session::set("admin", $adminInfo);

        /*更新本次登录时间*/
        $adminList->where('id', $adminInfo['id'])->update(['last_login_time' => date('Y-m-d H:i:s')]);

        //登录日志
        $loginlog = $this->LoginLogModel;
        $loginlog->insert(array('admin_id' => $adminInfo['id'], 'type' => 1, 'log_time' => date('Y-m-d H:i:s')));

        //获取用户节点权限注入session中
        $adminNodeList = $this->NodeListModel->getRoleAllowNodeList($adminInfo["role_id"]);
        Session::set("adminNodeList", $adminNodeList);

        //检查是否记住密码
        if (isset($data["remember"]) && $data["remember"] == 1) {
//            \think\Cookie::set("user_name",$data["user_name"]);
//            \think\Cookie::set("password",$data["password"]);
        } else {
            //未勾选记住密码要清除cookie
            \think\Cookie::delete("user_name");
            \think\Cookie::delete("password");
        }
        $this->ajaxReturn(array(
            "code" => 0,
            "msg" => "登录成功",
            "url" => "/index/index"
        ));
    }


    public function loginOut()
    {
        $adminInfo = Session::get("admin");
        //登录日志--退出
        $loginlog = $this->LoginLogModel;
        $loginlog->insert(array('admin_id' => $adminInfo['id'], 'type' => 2, 'log_time' => date('Y-m-d H:i:s')));

        Session::delete("admin");
        Session::delete("adminMenu");
        Session::delete("adminNode");
        Session::delete("adminRoom");
        $this->redirectToTopFramework("login");
    }

    public function welcome()
    {
        return $this->fetch();
    }

    public function test()
    {
//        $this->to_download_file('/webroot/media/video/hhh.mp4');
        return $this->fetch();
    }

}

?>