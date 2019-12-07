<?php

namespace app\common\validate;

class AdminListValidate extends BaseValidate
{

    protected $needCityRoleId = 0;
    protected $rule           = array(
        'id'=>'',
        'user_name' => 'require|checkUserNameMax',
        'password' => 'require',
        'real_name' => 'require',
        'role_id'  => 'require|number|gt:0|checkAdmin',
    );
    protected $message        = array(
        'user_name.require' => '账号不能为空',
        'user_name.max'     => '账号长度不能超过20个字符',
        'password.require' => '密码不能为空',
//        'password.require' => '请填写密码',
        'real_name.require' => '请填写真实姓名',
//        'real_name.unique' => '真实姓名已经存在',
        'role_id.require'  => '请选择角色',
        'role_id.number'  => '请选择角色',
        'role_id.gt'  => '请选择角色',
    );
    protected $scene          = array(
        "login" => array(
            "user_name" => "require|loginCheckName",
            "password" => "require|loginCheckPassword"
        ),
        "add"   => array(
            'user_name' => 'require|checkUserNameMax|checkNameNotEffectiveRepeat',
            'real_name' => 'require|checkRealNameMax',//|checkRealNameNotEffectiveRepeat
            'role_id',
        ),
        "edit"  => array(
            'user_name' => 'require|checkUserNameMax|checkNameNotSelfNotRepeat',
            'password',
            'real_name'=> 'require|checkRealNameMax',//|checkRealNameNotSelfNotRepeat
            'role_id',
        ),
        "changepassword" => array(
            "password" => "require|changePasswordCheck"
        ),
        'delete' => array(
            'id' => "require|checkEffectiveSelf",
        ),
    );
    
    protected function checkEffectiveSelf($value, $rule, $data)
    {
        $where = array(
            "is_delete" => 0,
            'id' => $data['id'],
        );
        return $this->AdminListModel->where($where)->count() > 0 ? true : '人员不存在';
    }
    
    protected function checkRealNameMax($value, $rule, $data){
        return $this->checkStrTrueLength($value,"max",10,"姓名长度不能超过10个字符");
    }

    protected function checkUserNameMax($value, $rule, $data){
        return $this->checkStrTrueLength($value,"max",20,"账号长度不能超过20个字符");
    }

    protected function checkAdmin($value, $rule, $data){
//        $id = $this->AdminListModel->where('role_id',1)->value('id');
//        if($value == 1&&$id!=$data['id']){
//            return "超级管理员只能有admin一个";
//        }
        return true;
    }
   

    protected function checkRealNameNotEffectiveRepeat($value, $rule, $data)
    {
        $baseWhere = array(
            "is_delete" => 0
        );
        return $this->checkValueNotEffectiveRepeat($value, $rule, $data, "AdminListModel", "real_name", "姓名已经存在", $baseWhere);
    }
    protected function checkNameNotEffectiveRepeat($value, $rule, $data)
    {
        $baseWhere = array(
            "is_delete" => 0
        );
        return $this->checkValueNotEffectiveRepeat($value, $rule, $data, "AdminListModel", "user_name", "账号已经存在", $baseWhere);
    }

    protected function checkRealNameNotSelfNotRepeat($value, $rule, $data)
    {
        $baseWhere = array(
            "is_delete" => 0
        );
        return $this->checkValueWithOtherFields($value, $rule, $data, "AdminListModel", "real_name", "姓名已经存在", "id", 2, $baseWhere);
    }
    protected function checkNameNotSelfNotRepeat($value, $rule, $data)
    {
        $baseWhere = array(
            "is_delete" => 0
        );
        return $this->checkValueWithOtherFields($value, $rule, $data, "AdminListModel", "user_name", "账号已经存在", "id", 2, $baseWhere);
    }

//    protected function checkName($value, $rule, $data)
//    {
//        return $this->checkPhoneBase($value, "账号错误");
//    }

    /**
     * 登录检查用户名
     * @param type $value
     * @param type $rule
     * @param type $data
     * @return string|boolean
     */
    protected function loginCheckName($value, $rule, $data)
    {
        $obj   = $this->AdminListModel;//\think\Loader::model("AdminList");
        $where = array(
            "user_name" => $value
        );
        $field = array(
            "admin_list.*",
            "admin_role_list.is_delete role_is_delete",
            "admin_role_list.status role_status",
        );
        $res   = $obj
                ->field($field)
                ->where($where)
                ->join("admin_role_list", "admin_list.role_id = admin_role_list.id")
                ->find();
        if (empty($res)) {
            return "账号不存在";
        }
        elseif ($res["is_delete"] == 1) {
            return "该账号已不存在";
        }
        elseif ($res["status"] == 0) {
            return "该账号已被禁用";
        }
        elseif ($res["role_is_delete"] == 1) {
            return "该角色已被删除";
        }
        elseif ($res["role_status"] == 0) {
            return "该角色已被禁用";
        }
        else {
            return true;
        }
    }

    /**
     * 登录检查密码
     * @param type $value
     * @param type $rule
     * @param type $data
     * @return type
     */
    protected function loginCheckPassword($value, $rule, $data)
    {
        $obj = $this->AdminListModel;//\think\Loader::model("AdminList");
        $res = $obj->where(array("user_name" => $data["user_name"]))->find();
        return md5(md5($value . $res["salt"])) === $res["password"] ? true : "密码不正确";
    }

    protected function changePasswordCheck($value, $rule, $data)
    {
        if (empty($value)) {
            return "原始密码不能为空";
        }
        elseif (empty($data["new_password"])) {
            return "新密码不能为空";
        }elseif(empty($data["new_password_two"])){
            return "确认密码不能为空";
        }elseif(!preg_match('#^[a-zA-Z\d]{6,20}$#', $data["new_password"])){
//        }elseif(!preg_match('#^[a-zA-Z\d]{6,20}$#', $data["new_password"])){
            return "密码由6-20数字或字母组成";
        }
        elseif (md5(md5($value . $data["salt"])) != $data["now_password"]) {
            return "原密码填写错误";
        }
        elseif ($data["new_password"] != $data["new_password_two"]) {
            return "两次密码不一致";
        }elseif(md5(md5($data["new_password"] . $data["salt"])) == $data["now_password"]){
            return "新密码和老密码不能一致";
        }else{
            return true;
        }
    }

}
