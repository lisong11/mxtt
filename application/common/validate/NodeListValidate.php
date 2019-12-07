<?php
namespace app\common\validate;
class NodeListValidate extends BaseValidate
{
	protected $rule = array(
            'node_name' => 'require',
            'node_value'  => 'require|unique:adm_node_list',
            'node_deep' => "require|number",
            "parent_node_id" => "require|number",
            'is_display' => "require|number",
            "status" => "require|number",
		);
	protected $message = array(
            'node_name.require'=>'节点名称不能为空',
            'node_value.require'=>'节点URL不能为空',
            'node_value.unique' => '该节点已经存在',
        );
	protected $scene = array(
            "add" => array(
                'node_name',
                'node_value'  => 'require|checkNameNotEffectiveRepeat',
                'node_deep' ,
                "parent_node_id" ,
                'is_display' ,
                "status",
            ),
            "edit" => array(
                'node_name',
                'node_value'  => 'require|checkNameNotSelfNotRepeat',
                'node_deep' ,
                "parent_node_id" ,
                'is_display' ,
                "status",
            ),
	);

    protected function checkNameNotEffectiveRepeat($value, $rule, $data)
    {
        return $this->checkValueNotEffectiveRepeat($value, $rule, $data, "AdminListModel", "real_name", "节点已经存在");
    }

	protected function checkNameNotSelfNotRepeat($value,$rule,$data){
            return $this->checkValueWithOtherFields($value, $rule, $data, "NodeListModel", "node_value", "节点已经存在","id",2);
	}
        
        
}