<?php

namespace app\common\validate;

use think\validate;

class BaseValidate extends Validate
{

    /**
     * 检查 以多个字段为条件 检查是否同时存在
     * @param type $value 提交的值
     * @param type $rule 规则
     * @param type $data 提交的数据数组
     * @param type $modelName 检查用的表
     * @param type $field 检查的字段
     * @param type $errorMsg 报错消息
     * @param type $otherField 其他需要协助的字段 string | array
     * @param type $otherType 其他协助字段判断类型 为 1:相等；2：不相等，即排除。
     * @return type
     */
    protected function checkValueWithOtherFields($value, $rule, $data, $modelName, $field, $errorMsg, $otherField, $otherType = 1,$otherWhere=array())
    {
        $model = strpos($modelName,'Model')!==false ? $this->{$modelName}:$this->{$modelName.'Model'};//\think\Loader::model($modelName);
        $method = $otherType == 1 ? "eq" : "neq";
        $where  = array(
            $field => $value
        );
        if (is_array($otherField)) {
            foreach ($otherField as $other) {
                $where[$other] = array($method, $data[$other]);
            }
        }
        else {
            $where[$otherField] = array($method, $data[$otherField]);
        }
        $where = array_merge($where,$otherWhere);
        $count = $model->where($where)->count();
        return $count > 0 ? $errorMsg : true;
    }
    
    /**
     * 检查值是否重复 携带其他条件，如 status is_delete 等状态相关的
     * @param type $value
     * @param type $rule
     * @param type $data
     * @param type $modelName
     * @param type $field
     * @param type $errorMsg
     * @param type $otherWhere
     * @return type
     */
    protected function checkValueNotEffectiveRepeat($value, $rule, $data, $modelName, $field, $errorMsg,$otherWhere=array())
    {
        $model = strpos($modelName,'Model')!==false ? $this->{$modelName}:$this->{$modelName.'Model'};//\think\Loader::model($modelName);
        $where  = array(
            $field => $value
        );
        $where = array_merge($where,$otherWhere);
        $count = $model->where($where)->count();
        return $count > 0 ? $errorMsg : true;
    }

    /**
     *  检查手机号格式是否正确
     * @param type $value
     * @param type $errorMsg
     * @return type
     */
    protected function checkPhoneBase($value, $errorMsg)
    {
        return checkPhoneFunc($value) ? true : $errorMsg;
    }
    
    /**
     * 
     * @param type $value 验证值
     * @param type $type 验证类型： max; min; between
     * @param type $typeValue 对应的 限制值： between 时候，范围用 ,隔开或者是数组
     */
    public function checkStrTrueLength($value,$type,$typeValue,$errorMsg){
        $trueLen = getStrTrueLen($value);
        $res = true;
        switch ($type){
            case "max":
                $res = $trueLen <= $typeValue ? true : false;
                break;
            case "min":
                $res = $trueLen >= $typeValue ? true : false;
                break;
            case "between":
                $typeValue = is_array($typeValue) ? $typeValue : explode(",", $typeValue);
                $res = $trueLen >= min($typeValue) && $trueLen <= max($typeValue) ? true : false;
                break;
            default : 
                $res = true;
                break;
        }
        return $res ? $res : $errorMsg;
    }
    
     /**
     * 重写get魔术方法,方便获取model实例
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return false;
    }

    /**
     * 重写魔术方法isset
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->loadModel($name);
    }

    /**
     * 重写魔术方法set
     *
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    /**
     * 实例化加载model
     *
     * @param $model
     * @return bool
     */
    public function loadModel($model)
    {
        if(strpos($model,'Validate')!==false){
            $this->{$model} = \think\Loader::validate($model);
        }else{
            $this->{$model} = \think\Loader::model($model);
        }
        
        if (!$this->{$model}) {
            return false;
        }
        return true;
    }
}
