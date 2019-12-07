<?php
namespace app\admin\controller;

use think\Session;

class Loginlog extends Common
{

    public function _initialize()
    {
        parent::_initialize();
    }
    //日志列表
    public function logList()
    {
        $where = array();
        $user_name = $this->request->get("user_name", "");
        if (!empty($user_name)) {
            $where["admin_list.user_name"] = array(
                "like", "%" . $user_name . "%"
            );
        }
        $this->assign("user_name",$user_name);

        $type = $this->request->get("type", "-1");
        if ($type >= 0) {
            $where["admin_login_log.type"] = $type;
        }
        $this->assign("type",$type);

        $start_time = $this->request->get("start_time", "");
        $end_time = $this->request->get("end_time", "");
        if (!empty($start_time) && empty($end_time)) {
            $where["admin_login_log.log_time"] = array("> time", $start_time);
        }
        if (empty($start_time) && !empty($end_time)) {
            $where["admin_login_log.log_time"] = array("< time", $end_time);
        }
        if (!empty($start_time) && !empty($end_time)) {
            $where["admin_login_log.log_time"] = array("between time", [$start_time,$end_time]);
        }
        $this->assign("start_time",$start_time);
        $this->assign("end_time",$end_time);

        $list = $this->LoginLogModel->getAllList($where);

        $page = $list->render();
        $list = $list->toArray();
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
    }



}

?>