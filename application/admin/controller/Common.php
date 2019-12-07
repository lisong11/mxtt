<?php

namespace app\admin\controller;

use think\Db;
use think\loader;
use think\Session;

header("Content-type: text/html; charset=utf-8");

class common extends \app\common\controller\Common
{
    protected $notNeedCheckController = array(
        "Index",
        "Ajax"
    );

    /**
     * 高并发下创建不重复流水号
     * @param string $prefix
     * @param string $uid
     * @return string
     */
    function create_no($prefix = '', $uid = '')
    {
        if (empty($uid)) $uid = date('His');
        $str = $prefix . session_id() . microtime(true) . uniqid(md5(microtime(true)), true);
        $str = md5($str);
        $prefix = $prefix . date('YmdH') . $uid;
        $code = $prefix . substr(uniqid($str, true), -8, 8);
        return $code;
    }

    public function Area()
    {
        $id = $this->request->post("id", "");
        $where['pid'] = $id;
        $province = $this->AreaModel->getAllList($where);
        $this->ajaxReturn(array("code" => '200', "data" => $province));
    }

    //新增消息
    protected function messageAdd($type, $from_type, $message_type, $message_content, $to_type, $to_id, $from_id)
    {
        $data['type'] = $type;
        $data['from_type'] = $from_type;
        $data['message_type'] = $message_type;
        $data['to_id'] = $to_id;
        $data['from_id'] = $from_id;
        $data['message_content'] = $message_content;
        $data['to_type'] = $to_type;
        $data['add_time'] = date('Y-m-d H:i:s', time());
        $res = $this->MessageListModel->allowField(true)->save($data);

    }

    //导入execel表格
    public function Excel()
    {
        $filepath = '/www/web/admin_tutuya/public/approve.xlsx';
        if (!file_exists($filepath)) exit('2134');
        $excel = $this->importExcel($filepath, array('0', '1', '2', '3', '4', '5', '6'));
        $result = array();

        foreach ($excel as $key => $v) {
            if ($key >= 5) {
                foreach ($excel[$key] as &$value) {
                    if (empty($value['D']) || empty($value['E'])) {
                        continue;
                    }
                    $save = array();
                    $save['name'] = $value['B'];
                    $save['to_type'] = $value['C'];
                    $save['explain'] = $value['D'];
                    $save['forms'] = $value['E'];
                    $save['forms_type'] = $value['F'];
                    $save['approva_ids'] = $value['G'];
                    $save['copy_ids'] = $value['H'];
                    $save['textarea1'] = $value['I'];
                    $save['create_time'] = date('Y-m-d');
                    $result[] = $save;


                }
            } else {
                foreach ($excel[$key] as &$value) {
                    if (empty($value['C']) || empty($value['D'])) {
                        continue;
                    }
                    $save = array();


                    $save['name'] = $value['A'];
                    $save['to_type'] = $value['B'];
                    $save['explain'] = $value['C'];
                    $save['forms'] = $value['D'];
                    $save['forms_type'] = $value['E'];
                    $save['approva_ids'] = $value['F'];
                    $save['copy_ids'] = $value['G'];
                    $save['textarea1'] = $value['H'];
                    $save['create_time'] = date('Y-m-d');
                    $result[] = $save;
                }
            }


        }

        foreach ($result as &$value) {
            $this->ApprovaModel->data(array());
            $res = $this->ApprovaModel->allowField(true)->isUpdate(false)->save($value);
        }


    }

    public function download()
    {
        $id = $this->request->get("id", 0);
        if ($id < 1) {
            $this->error("参数错误");
        }
        $map['id'] = $id;
        $path = db('file_list')->where($map)->value('path');

        ob_end_clean();
        ob_start();
        $filename = \think\Config::get('IMG_READ_PATH') . "" . $path;
        $title = substr($filename, strrpos($filename, '/') + 1);
        $size = readfile($filename);
        Header("Content-type:application/octet-stream");
        Header("Accept-Ranges:bytes");
        Header("Accept-Length:");
        header("Content-Disposition:  attachment;  filename= $title");
    }

    public function _initialize()
    {
        if (!$this->isLogin()) {
            $this->redirectToTopFramework("/index/login");
        }
        if (!$this->checkNodeAllow()) {
            if ($this->request->isAjax()) {
//                $this->ajaxReturn(array(
//                    'code' => 10000,
//                    'msg'  => '对不起，您没有相关访问权限',
//                    'data' => ''
//                ));
            }
//             $this->error("对不起，您没有相关访问权限");
        }
        if (!$this->request->isAjax()) {
            $this->assignAdminInfo();
            $this->assignCrumbsNode();
            $this->assignTopNode();
            $this->assignAdminAllowNodeList();
            $this->assignAdminMenu();
            $this->assign('nowBaseUrl', $this->getBaseUrlAsNode());
            //$this->assignAdminMenuFirst();
            //$this->assignCurrentPage();
        }
    }

    protected function checkNodeAllow()
    {
        $baseUrl = $this->getBaseUrlAsNode();
        $module = $this->request->module();
        $controller = $this->request->controller();
        $action = $this->request->action();
        $adminNode = $this->getAdminAllowNodeList();
        if (!in_array($controller, $this->notNeedCheckController) && !in_array($baseUrl, $adminNode)) {
            return false;
        }
        return true;
    }

    protected function assignCrumbsNode()
    {
        $baseUrl = $this->getBaseUrlAsNode();
        $nowNodeId = $this->NodeListModel->where(array("node_value" => $baseUrl))->column("id");
        $nowNodeId = empty($nowNodeId) ? 0 : $nowNodeId[0];
        $CrumbsNode = $this->NodeListModel->getCrumbsByNodeId($nowNodeId);
        $this->assign("CrumbsNode", $CrumbsNode);
    }

    protected function assignTopNode()
    {
        $baseUrl = $this->getBaseUrlAsNode();
        $nowNodeId = $this->NodeListModel->where(array("node_value" => $baseUrl))->value("id");
        $nowTopNode = $this->NodeListModel->getTopParentId($nowNodeId);
        $this->assign("nowTopNode", $nowTopNode);

    }

    protected function assignAdminMenuFirst()
    {
        $adminMenuFirst = $this->getAdminMenuFirst();
        $this->assign("adminMenuFirst", $adminMenuFirst);
    }

    protected function getAdminMenuFirst()
    {
        if (!Session::has("adminMenuFirst")) {
            $adminMenu = $this->getAdminMenu();
            $adminMenuFirst = "/index/index";
            foreach ($adminMenu as $admin) {
                if (isset($admin["son"]) && !empty($admin["son"])) {
                    foreach ($admin["son"] as $adminSon) {
                        if ($adminSon["is_allow"] == 1) {
                            $adminMenuFirst = $adminSon["node_value"];
                            break 2;
                        }
                    }
                }
            }
            Session::set("adminMenuFirst", $adminMenuFirst);
        } else {
            $adminMenuFirst = Session::get("adminMenuFirst");
        }
        return $adminMenuFirst;
    }

    protected function assignAdminMenu()
    {
        $adminMenu = $this->getAdminMenu();
        $this->assign("adminMenu", $adminMenu);
    }

    protected function getAdminMenu()
    {
        if (!Session::has("adminMenu")) {
            $admin = $this->getAdminInfo();
            $node = $this->NodeListModel;//\think\Loader::model("NodeList");
            $adminMenu = $node->getRoleAllowMenuNodeList($admin["role_id"]);
            Session::set("adminMenu", $adminMenu);
        } else {
            $adminMenu = Session::get("adminMenu");
        }
        return $adminMenu;
    }

    protected function getBaseUrlAsNode()
    {
        $module = $this->request->module();
        $controller = $this->request->controller();
        $action = $this->request->action();
        $baseUrl = "/" . strtolower($controller) . "/" . strtolower($action);
        $urlHtmlSuffix = "." . (\think\Config::get("url_html_suffix"));
        $baseUrl = str_replace($urlHtmlSuffix, "", $baseUrl);
        return $baseUrl;
    }

    protected function assignAdminInfo()
    {
        $this->assign("adminInfo", $this->getAdminInfo());
    }

    protected function assignAdminAllowNodeList()
    {
        $this->assign("adminAllowNodeList", $this->getAdminAllowNodeList());
    }

    protected function getAdminAllowNodeList()
    {
        if (!Session::has("adminNode")) {
            $admin = $this->getAdminInfo();
            $nodeList = $this->NodeListModel->getRoleAllowNodeList($admin["role_id"]);
            $adminNode = $this->getAdminNodeArray($nodeList);
            Session::set("adminNode", $adminNode);
        } else {
            $adminNode = Session::get("adminNode");
        }
        return $adminNode;
    }

    protected function getAdminNodeArray($nodeList)
    {
        $adminNode = array();
        foreach ($nodeList as $node) {
            $adminNode[] = strtolower($node["node_value"]);
        }
        return $adminNode;
    }

    protected function getAdminInfo()
    {
        return Session::get("admin");
    }

    /**
     * 是否登录
     * @return type
     */
    protected function isLogin()
    {
        return Session::has("admin");
    }

    protected function isAdmin()
    {
        $admin = $this->getAdminInfo();
        if ($admin["role_id"] == 1) {
            return true;
        }
        return false;
    }

    protected function getInitializePass($iniPass = "")
    {
        $salt = $this->generate_random_string(64);
//        if (empty($iniPass)) {
//            $iniPass = \think\Config::get("INIPASS");
//        }
//        $password = md5(md5($iniPass . $salt));
        $password = md5(serialize($iniPass));
        $data = array(
            "salt" => $salt,
            "password" => $password
        );
        return $data;
    }

    /**
     * 保存管理员操作记录
     */
    protected function saveOperationRecord($type = 0, $content = '')
    {
        $adminInfo = $this->getAdminInfo();
        $add = array(
            'admin_id' => $adminInfo['id'],
            'admin_user_name' => $adminInfo['user_name'],
            'admin_real_name' => $adminInfo['real_name'],
            'type' => $type,
            'description' => $content
        );
        $this->AdminOperationRecordModel->save($add);
    }

    //下拉搜合伙人
    public function GetCityParter()
    {
        $name = $this->request->post("name", '');

        $page = $this->request->post("page", '1');
        $page_size = $this->request->post("page_size", '10');

        $limit = ($page - 1) * $page_size . ",{$page_size}";

        $sql_count = "select count(1) as count from (select corporation from city_partner where `company_name` like '%{$name}%' ) as m";

        $total_count = Db::query($sql_count);

        $total_count = $total_count[0]['count'];

        $sql = "select name from (select corporation as name from city_partner where `corporation` like '%{$name}%' ) as m limit $limit";
        $data = Db::query($sql);

        foreach ($data as &$value) {
            $map['corporation'] = $value['name'];
            $company_name = db('city_partner')->where($map)->value('company_name');
            $value['name'] = $company_name . "  " . $value['name'];
        }
        if (empty($data)) {
            $sql = "select name from (select company_name as name from city_partner where `company_name` like '%{$name}%' ) as m limit $limit";
            $data = Db::query($sql);
            foreach ($data as &$value) {
                $where['company_name'] = $value['name'];
                $corporation = db('city_partner')->where($where)->value('corporation');
                $value['name'] = $value['name'] . "  " . $corporation;
            }
        }
        $this->_return('0', '成功', array(
            'total_count' => $total_count,
            'page' => $page,
            'page_size' => $page_size,
            'list' => $data
        ));

    }

    //上传附件
    public function upload()
    {
        set_time_limit(0);
        $files = $_FILES['file'];

        $data = $this->request->param();
        if (empty($files) || $files['error'] > 0) {
            $this->ajaxReturn(array('code' => '400', 'errormsg' => '文件数据异常'));
        }
        if ($files['size'] > 500 * 1024 * 1024) {
            $this->ajaxReturn(array('code' => '400', 'errormsg' => '文件大小超出限制'));
        }

        $str = '';
        if (!empty($data['partner'])) {
            $str = 'partner';
        }
        if (!empty($data['school'])) {
            $str = 'school';
        }
        if (!empty($data['teacher'])) {
            $str = 'teacher';
        }
        if (empty($str)) $str = 'partner';

        $root = \think\Config::get('IMG_UPLOAD_PATH');
        $http = \think\Config::get('IMG_READ_PATH');

        $dir = $str . '/' . date("Ym") . '/';
        if (!is_dir($root . $dir)) {
            mkdir($root . $dir, 0777, true);
        }

        $suffix = substr(strrchr($files['name'], '.'), 1);

        $fileName = rand(10000, 99999) . "." . $suffix;
        $path = $root . $dir . $fileName;
        $url = '/' . $dir . $fileName;

        if (in_array($suffix, array('jpg', 'jpeg', 'png'))) {
            $is_pic = 1;
            $show_url = $http . $dir . $fileName;;
        } else {
            $is_pic = 0;
            $show_url = '';
        }
        if (move_uploaded_file($files['tmp_name'], $path)) {
            $this->ajaxReturn(array('code' => '200', 'url' => $url, 'size' => $files['size'], 'is_pic' => $is_pic, 'show_url' => $show_url, 'name' => $files['name']));
        } else {
            $this->ajaxReturn(array('code' => '400', 'errormsg' => '上传文件出现错误'));
        }
    }

    //上传文件
    public function uploadFile()
    {
        set_time_limit(0);
        $files = $_FILES;
        $data = $this->request->param();
        $type = $data['type'];

        $root = \think\Config::get('IMG_UPLOAD_PATH');
        $http = \think\Config::get('IMG_READ_PATH');

        $dir = '';
        if ($type == 1) {
            $dir = 'images/' . date("Ym") . '/';
        } else {
            $dir = 'videos/' . date("Ym") . '/';
        }
        if (!is_dir($root . $dir)) {
            mkdir($root . $dir, 0777, true);
        }
        foreach ($files as $key => $value) {
            $suffix = substr(strrchr($value['name'], '.'), 1);
            $fileName = rand(100, 999) . time() . "." . $suffix;
            $path = $root . $dir . $fileName;
            $url = $http . $dir . $fileName;
            if (move_uploaded_file($value['tmp_name'], $path)) {
                $this->ajaxReturn(array('code' => '200', 'url' => $url));
            } else {
                $this->ajaxReturn(array('code' => '400', 'url' => $url));
            }
        }
    }

    //编辑器上传
    public function ckeditorUploadImage()
    {
        $file = request()->file('upload');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $root = \think\Config::get("IMG_UPLOAD_PATH");
        $dir = 'editor/';
        if (!is_dir($root . $dir)) {
            mkdir($root . $dir, 0777, true);
        }
        $path = $root . $dir;

        $callback = $_REQUEST["CKEditorFuncNum"];
        $info = $file->validate(['size' => 1024 * 1024 * 10, 'ext' => 'jpg,jpeg,png,gif'])->move($path);
        if ($info) {
            // 成功上传后 获取上传信息
            $url = \think\Config::get("IMG_READ_PATH") . $dir . $info->getSaveName();
            echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($callback,'" . $url . "','');</script>";
            die;
        } else {
            // 上传失败获取错误信息
            echo "<font color=\"red\"size=\"2\">" . $file->getError() . "</font>";
            die;
        }
    }
}

?>