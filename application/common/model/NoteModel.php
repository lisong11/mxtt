<?php

namespace app\common\model;


class NoteModel extends BaseModel
{
    protected $table  = 'note';
    protected $insert = array('create_time');
    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }
    
    //添加数据时候自动添加 创建时间字段
    protected function setCreateTmAttr($value)
    {
        return date("Y-m-d H:i:s");
    }

 
    
    public function getnoteList($where=array(),$pageSize=30){
        $field = array(
            "note.*",
        
        );
     $where['status'] = array('gt',-1);
       
        return $this
                ->field($field)
                ->where($where)
                ->order("note.id desc")
                ->paginate($pageSize);
    }

   

    
   
}

?>