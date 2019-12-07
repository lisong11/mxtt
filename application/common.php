<?php

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
    return mb_strlen($str, "utf8");
}

function checkFee($fee = 0)
{
    $reg = '#^[1-9](\d+)?(\.\d{1,2})?$#';
    return preg_match($reg, $fee) ? true : false;
}

function checkInt($str)
{
    if (!empty($str) && is_numeric($str) && intval($str) == $str) {
        return true;
    }
    return false;
}

/**
 * 二维数组排列组合
 * @param array $arr
 * @return bool|mixed
 */
function two_array_combination(array $arr)
{
    $num = count($arr);
    if ($num === 0) return false;
    if ($num === 1) return $arr[0];

    while(count($arr) > 1) {
        $arr_first = array_shift($arr);
        $arr_second = array_shift($arr);
        $c = array();
        foreach ($arr_first as $v) {
            $v = (array) $v;
            foreach ($arr_second as $val) {
                $c[] = array_merge($v, (array) $val);
            }
        }
        array_unshift($arr, $c);
        unset($c);
    }
    return $arr[0];
}

/**
 * 后台菜单判断是否选中
 * @author ZhiyuanLi < 956889120@qq.com >
 * @param $url
 * @return bool
 */
function adminMenuUrl($url){

    $request = \think\Request::instance();
    $controller       = $request->controller();
    $action       = $request->action();
    $baseUrl       = "/" . strtolower($controller)."/" . strtolower($action);
    $urlHtmlSuffix = "." . (\think\Config::get("url_html_suffix"));
    $baseUrl       = str_replace($urlHtmlSuffix, "", $baseUrl);

    return $baseUrl == $url ? true : false;

}



