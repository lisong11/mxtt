 <?php

return array_merge(array(
    "NODE_DEEP" => array(
        "1" => "一级菜单",
        "2" => "二级菜单",
        "3" => "三级菜单",
        "4" => "四级菜单"
    ),
    "URL_SPE"=>array(
        'changepassword',
    ),
    'INIPASS'=>'123456',
    'upload_path'=>'http://test.res.tutuyaedu.com',
    'BILL_TYPE' => array(
        '1' => '教企联盟课程',
    ),
    "NODE_DEEP" => array(
        "1" => "一级菜单",
        "2" => "二级菜单",
        "3" => "三级菜单",
        "4" => "四级菜单"
    ),
    //客户类别
    'CUSTOMER_TYPE' => array(
        ['title'=>'新例子','value'=>'1'],
        ['title'=>'正在跟进中的','value'=>'2'],
        ['title'=>'无效的','value'=>'3'],
        ['title'=>'意向的','value'=>'4'],
        ['title'=>'不感兴趣的','value'=>'5'],
        ['title'=>'已成交的','value'=>'6'],
    ),
    //沟通情况
    'COMM_TYPE' => array(
        ['title'=>'未接电话的','value'=>'1'],
        ['title'=>'挂断的','value'=>'2'],
        ['title'=>'停机的','value'=>'3'],
        ['title'=>'关机的','value'=>'4'],
        ['title'=>'占线的','value'=>'5'],
        ['title'=>'接电话了有效的','value'=>'6'],
        ['title'=>'错号的','value'=>'7'],
    ),

    //虚拟礼物类别
    'VC_GIFT_CATEGORY' => array(
        '1' => '初级',
        '2' => '中级',
        '3' => '高级',
        '4' => '豪华',
        '5' => '特殊',
    ),
),  require_once 'config.'.FILE_CONFIG.'.php');
