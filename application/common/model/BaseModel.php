<?php
namespace app\common\model;
use think\Model;

class BaseModel extends Model
{

     /**
     * 重写get魔术方法,方便获取model实例
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if(strpos($name, 'Model') !== false){
            return isset($this->{$name}) ? $this->{$name} : FALSE;
        }else{
            return parent::__get($name);
        }
    }

    /**
     * 重写魔术方法isset
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
         if(strpos($name, 'Model') !== false){
             return $this->loadModel($name);
         }else{
            return parent::__isset($name);
         }   
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
        if(strpos($name, 'Model') !== false){
             $this->{$name} = $value;
         }else{
            parent::__set($name,$value);
         }   
        
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

    /**
     * 重组数据为数组
     */
    protected function regroupToArray($list=array()){
        foreach ($list as &$value) {
            $value = $value->toArray();
        }
        return $list;
    }
    

}