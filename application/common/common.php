<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用公共文件
/**
 * 检查电话格式是否正确
 * @param type $phone
 * @return type
 */
function checkPhoneFunc($phone)
{
    $reg = '#^1[34578]{1}[0-9]{9}$#';
    return preg_match($reg, $phone) ? true : false;
}

function getStrTrueLen($str)
{
    $str = mb_convert_encoding($str, "utf8", "AUTO");
    return mb_strlen($str,"utf8");
}

function checkFee($fee=0){
    $reg = '#^[1-9]\d(\.\d)?$#';
    return preg_match($reg, $fee) ? true : false;
}

function checkInt($str){
    if(!empty($str) && is_numeric($str) && intval($str) == $str){
        return true;
    }
    return false;
}