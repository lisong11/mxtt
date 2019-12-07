<?php
/**
 * Created
 * User: yujun
 * Date: 2018/03/15
 * Time: 下午3:15
 */

// [ 应用入口文件 ]
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
define('APP_DEBUG',true);
define("ENTRY_PATH",__DIR__);
define('BIND_MODULE','admin');
require_once "init.php";

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';

