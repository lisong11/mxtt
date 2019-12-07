<?php
namespace app\common\validate;
class ttyRoleListModelValidate extends BaseValidate
{
	protected $rule = array(
            'id'=>'',
            'tty_tty_role_name' => 'require|unique:admin_role_list|checkRoleNameMax',
            'status' => "require|number",
            "is_delete" => "require|number",
        );
	protected $message = array(
            'tty_role_name.unique' => '该角色已经存在',
            'tty_role_name.require' => '角色名称不能为空',
        );
	protected $scene = array(
            "add" => array(
                'tty_role_name' => 'require|checkRoleNameMax|checkNameNotEffectiveRepeat|checkMaxNum' ,
                                    "status",
            ),
            "edit" => array(
                    'tty_role_name'  => 'require|checkRoleNameMax|checkNameNotSelfNotRepeat',
                    "status",
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
        return $this->ttyRoleListModel->where($where)->count() > 0 ? true : '角色不存在';
    }
        
        protected function checkRoleNameMax($value, $rule, $data){
	        return $this->checkStrTrueLength($value,"max",10,"角色名称不超过10个字符");
	    }

		protected function checkMaxNum($value, $rule, $data){
			$model = $this->ttyRoleListModel;//\think\Loader::model("RoleList");
			$where = array(
	            "is_delete" => 0
	        );
	        $count = $model->where($where)->count();
	        return $count >= 10 ? "角色不能超过10个" : true;
		}

        protected function checkNameNotEffectiveRepeat($value, $rule, $data)
    {
        $baseWhere = array(
            "is_delete" => 0
        );
        return $this->checkValueNotEffectiveRepeat($value, $rule, $data, "ttyRoleListModel", "tty_role_name", "角色名称已经存在", $baseWhere);
    }
        
	protected function checkNameNotSelfNotRepeat($value,$rule,$data){
            $baseWhere = array(
            "is_delete" => 0
        );
            return $this->checkValueWithOtherFields($value, $rule, $data, "ttyRoleListModel", "tty_role_name", "角色名称已经存在","id",2,$baseWhere);
	}
        
        
}