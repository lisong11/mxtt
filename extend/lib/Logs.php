<?php
namespace lib;

class Logs{

    /**
     * @abstract 初始化
     * @param String $dir 文件路径
     * @param String $filename 文件名
     * @return
     */
    public $baseDir = '';
    public $prefix = 'log';

    function __construct($dir,$prefix='')
    {
        $this->baseDir = rtrim($dir,"/");
        $this->prefix = $prefix;
    }

    /**
     * @abstract 写入日志
     * @param String $log 内容
     */

    function setLog($log)
    {
        $this->Log(Logs::NOTICE, $log);
    }
    function LogDebug($log)
    {
        $this->Log(Logs::DEBUG, $log);
    }
    function LogError($log)
    {
        $this->Log(Logs::ERROR, $log);
    }
    function LogNotice($log)
    {
        $this->Log(Logs::NOTICE, $log);
    }
    function Log($priority, $log)
    {
        $file_path = $this->baseDir."/".date("Ymd");
        $file_name = $this->prefix."_".date("H").".log";
        if(!is_dir($file_path)){
            $res = mkdir($file_path,0777,true);
        }
        file_put_contents($file_path."/".$file_name,"[status:{$priority}]".$log,FILE_APPEND);
    }

    const EMERG  = 0;
    const FATAL  = 0;
    const ALERT  = 100;
    const CRIT   = 200;
    const ERROR  = 300;
    const WARN   = 400;
    const NOTICE = 500;
    const INFO   = 600;
    const DEBUG  = 700;
}
?>
