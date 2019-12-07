<?php
namespace lib;

/**
 * Created by PhpStorm.
 * User: liupan
 * Date: 2018/3/4
 * Time: 下午2:34
 */
class wx {

    public $appid = '';
    public $appsecret = '';
    public $log_path = '';
    public $token = '';
    public $EncodingAESKey = '';
    public $weixin_platform = 1;

    /**
     * 微信jsapi的接口列表
     *
     * @var array
     */
    public $jsapilist = array(
        'onMenuShareTimeline',
        'onMenuShareAppMessage',
        'onMenuShareQQ',
        'onMenuShareWeibo',
        'onMenuShareQZone',
        'chooseImage',
        'previewImage',
        'uploadImage',
        'downloadImage',
        'openLocation',
        'getLocation',
    );

    public function __construct($appid = '', $appsecret = '', $weixin_platform = 1,$log_path='', $token='', $EncodingAESKey='') {
        $this->appid = $appid;
        $this->appsecret = $appsecret;
        $this->weixin_platform = $weixin_platform;
        $this->token = $token;
        $this->EncodingAESKey = $EncodingAESKey;
        $log_path = $log_path == '' ? __DIR__."/wxlog/" : $log_path;
        $this->log_path = $log_path;
    }

    /*
      获取微信code
     */

    public function getOpenId() {
        $code = isset($_GET['code']) ? trim($_GET['code']) : '';
        if ($code == "" || empty($code) || !$code) {
            $this->autoReAuth();
            return false; // 没有接收到code
        }
//        echo $code;die;
        // 根据code获取用户的微信的open_id
        $open_id = $this->getOpenIdByCode($code);
        if ($open_id == "" || empty($open_id) || !$open_id) {
            return false; // 没有取到open_id
        }
        return $open_id;
    }

    /**
     * 根据微信返回的code取得客户openid
     *
     * @param string $code
     */
    public function getOpenIdByCode($code) {
        $appid = $this->appid;
        $appsecret = $this->appsecret;
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?grant_type=authorization_code&appid=' . $appid . '&secret=' . $appsecret . '&code=' . $code;
        $json_string = $this->httpGet($url);
        $obj = json_decode($json_string); // 将json字符串解析为json数组
        return isset($obj->openid) ? $obj->openid : '';
    }

    /**
     * 根据用户open_id拉取用户信息
     */
    public function getCustomerWxInfo($open_id) {
        if (empty($open_id)) {
            return NULL;
        }
        $access_token = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $access_token . '&openid=' . $open_id . '&lang=zh_CN';
        $res = $this->httpGet($url);
        $arr = json_decode($res, true);
        return $arr;
    }

    /*
      获取access_token
     */

    public function getAccessToken() {
        /*
          获取数据库中的access_token
         */
        if (isset($GLOBALS['g_db'])) {
            $g_db = $GLOBALS['g_db'];
        } else {
            $g_db = new pdomysql();
        }
        $access_token = $g_db->getOne('select * from weixin_list where weixin_platform = ' . $this->weixin_platform);
        if (empty($access_token['access_token']) || $this->getIntervalFromNow($access_token['accesstoken_time']) > 7000) {
            $appid = $this->appid;
            $appsecret = $this->appsecret;
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $appsecret;
            $json_string = $this->httpGet($url);
            $obj = json_decode($json_string); // 将json字符串解析为json数组
            $access_token = isset($obj->access_token) ? $obj->access_token : '';
            //更新数据库数据
            $save = array(
                'access_token' => $access_token,
                'accesstoken_time' => date('Y-m-d H:i:s')
            );
            $g_db->update('weixin_list', $save, "weixin_platform = {$this->weixin_platform}");
        } else {
            $access_token = $access_token['access_token'];
        }
        return $access_token;
    }

    /**
     * 获取一个时间戳和现在的差值
     *
     * @param int $param
     */
    public function getIntervalFromNow($param) {
        $now = time();
        $param = strtotime($param);
        return abs($now - intval($param));
    }

    /**
     * 微信授权自动跳转
     *
     * @param int $type 1-base；2-userinfo
     * @return void
     */
    protected function autoReAuth($type = 1) {
        $scope_info = array(
            '1' => "snsapi_base",
            '2' => "snsapi_userinfo"
        );
        $scope = $scope_info[$type];
        $redirect_uri = $this->getCurrentUrl();
        $appid = $this->appid;
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . urlencode($redirect_uri) . "&response_type=code&scope=" . $scope . "&state=autoReauth";
        header("Location:" . $url);
        die;
    }

    /**
     * 获取当前页面完整URL地址
     */
    protected function getCurrentUrl() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
    }

    /**
     * 判断是否为微信浏览器
     */
    public function isWeixin() {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }

    /**
     * 获取当前访问域名
     *
     * @return string
     */
    protected function getCurrentHost() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
    }

    /**
     * 从腾讯服务器重新获取jsapi ticket
     *
     * @return boolean
     * @return string
     */
    protected function getWxJsApiTicket() {
        if (!$this->isWeixin()) {
            return null;
        }
        //先获取数据库中的数据
        if (isset($GLOBALS['g_db'])) {
            $g_db = $GLOBALS['g_db'];
        } else {
            $g_db = new pdomysql();
        }
        $ticket = $g_db->getOne('select * from weixin_list where weixin_platform = ' . $this->weixin_platform);
        if (empty($ticket) || $this->getIntervalFromNow($ticket['ticket_time']) < 7000) {
            $access_token = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $access_token . "&type=jsapi";
            $json_string = $this->httpGet($url);
            $obj = json_decode($json_string); // 将json字符串解析为json数组
            $ticket = isset($obj->ticket) ? $obj->ticket : '';
            //更新数据库数据
            $save = array(
                'jsapi_ticket' => $ticket,
                'ticket_time' => date('Y-m-d H:i:s')
            );
            $g_db->update('weixin_list', $save, "weixin_platform = {$this->weixin_platform}");
            return $ticket;
        } else {
            return $ticket['ticket'];
        }
    }

    /**
     * 获取微信jsapi 签名
     *
     * @return array
     */
    protected function getJsSignPackage($url = '') {
        $ticket = $this->getWxJsApiTicket();
        if (empty($url)) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        $nonceStr = $this->getRandomCode(16, 4);
        $timestamp = time();
        $string = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = array(
            "appId" => $this->getAppid(),
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    /**
     * 只有调用了该函数，网页内才会集成jsapi功能
     */
    public function getWxJsApi($url = '') {
        return array(
            'signPackage' => $this->getJsSignPackage($url),
            'wx_jsapi_list' => $this->wx_jsapi_list,
        );
    }

    /**
     * 执行http get请求
     *
     * @param unknown $url
     * @return mixed
     */
    public function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    /**
     * 发起htt post 请求
     *
     * @param string $url
     * @param mixed $data
     * @return boolean|mixed
     */
    public function httpPost($url, $data, $need_urlencode = false) {
        if (is_array($data) && true === $need_urlencode) {
            foreach ($data as $key => $val) {
                $data[$key] = urlencode($val);
            }
        }
        // 模拟提交数据函数
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 发起异步http请求
     * @param string $url
     * @param string|array $post_string
     */
    function post_async($url, $post_string) {
        if (is_array($post_string)) {
            foreach ($post_string as $key => &$val) {
                if (is_array($val))
                    $val = implode(',', $val);
                $post_params[] = $key . '=' . urlencode($val);
            }
            $post_string = implode('&', $post_params);
        }
        $parts = parse_url($url);
        $fp = @fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);

        $out = "POST " . $parts['path'] . "?" . $parts['query'] . " HTTP/1.1\r\n";
        $out .= "Host: " . $parts['host'] . "\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        if (isset($post_string))
            $out .= $post_string;

        @fwrite($fp, $out);
        @fclose($fp);
    }

    //发送微信文本消息
    public function send_weixin_text_msg($open_id, $msg) {
        $access_token = $this->getAccessToken();
        $post_data = array(
            'touser' => $open_id,
            'msgtype' => 'text',
            'text' => array(
                'content' => $msg
            )
        );
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $access_token;
        $res = $this->send_content_httprequest($post_data, $url);
        $tmp = json_decode($res);
        return $tmp;
    }

    //模拟HTTP请求，将数组数据转json串后，作为报体发送,用file_get_contents("php://input")获取包报体内容;
    public function send_content_httprequest($postdata, $send_url) {
        $postdata = urldecode(json_encode($postdata, JSON_UNESCAPED_UNICODE));
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60
            ) // 超时时间（单位:s）
        );
        $context = stream_context_create($options);
        $result = file_get_contents($send_url, false, $context);
        return json_decode($result);
    }

    //发送微信模板消息1
    public function pushWXPLMsg($open_id, $text1, $text2) {
        if (strlen($open_id) == 28) {
            $tpl_id = "zL7q4vwQOYgEUXEC2E70VeDWnTSBwJdoj1wVBK3_y08"; //模板id
            //点击连接地址
            $url = "http://m.doctorgroup.com.cn/wallet/withdraw";

            $data = array(
                'first' => array(
                    'value' => "您好\n",
                    'color' => "#494949"
                ),
                'keyword1' => array(
                    'value' => $text1,
                    'color' => "#1c92fd"
                ),
                'keyword2' => array(
                    'value' => $text2,
                    'color' => "#e34b07"
                ),
                'remark' => array(
                    'value' => "\n" . "如有问题，请致电联系我们。",
                    'color' => "#F65941"
                )
            );

            $res = $this->send_wx_tpl_msg($open_id, $tpl_id, $data, $url);
            return $res;
        }
    }

    //发送微信模板消息2
    public function send_wx_tpl_msg($open_id, $tpl_id, $data, $url, $topcolor = "#26b8c1",$is_mini=0,$pagepath='') {
        $access_token = $this->getAccessToken();
        $send_url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;

        // 准备数据
        $send_data = array(
            'touser' => $open_id,
            'template_id' => $tpl_id,
            'url' => $url,
            'topcolor' => $topcolor,
            'data' => $data
        );
        if($is_mini>0){
            $send_data['miniprogram'] = array(
                "appid"=>"wxa01500c7172f82b6",
                "pagepath"=>$pagepath
            );
        }
        $res = $this->send_content_httprequest($send_data, $send_url);
        return $res;
    }

    /**
     * 写发送消息接口日志
     * @param type $prefix 模块名
     * @param type $msg 内容
     *
     */
    public function writeFileLog($msg)
    {
        $path = $this->log_path.date("Ym")."/";
        if (!is_dir($path)) {
            $res =$this->makedir($path);
        }
        $url = $_SERVER['REQUEST_URI'];
        $message = "[" . date("Y-m-d H:i:s") . "]:" . $url . " | ".$msg . "\n";
        $filename = $path . date("Y-m-d") . ".log";
        file_put_contents($filename, $message, FILE_APPEND);
    }

    public function go($response_list=[])
    {
        if(empty($this->weixin_platform)){
            exit('');
        }
        $welcome = $response_list['welcome'];
        $text_response = $response_list['text_response'];

        $this->isValid();
        /*
         * 获取传输过来的数据
         */
        $postStr = file_get_contents('php://input');
        if(!empty($postStr) && !empty($this->weixin_platform))
        {
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->writeFileLog("weixin_platform:$this->weixin_platform;content:".json_encode($postObj));
            $user_open_id = $postObj->FromUserName;
            $our_open_id = $postObj->ToUserName;
            $msgType = strtolower($postObj->MsgType);
            $keyword = trim($postObj->Content);
            /*
             * 用户发送文本消息
             */
            if($msgType == "text"){
                $data = array(
                    'FromUserName'=>$our_open_id,
                    'ToUserName'=>$user_open_id,
                    'Content' => $text_response
                );
                $response = $this->buildMsgString($msgType, $data);
                echo $response;
            }
            /*
             * 用户发送事件消息
             */
            else if($msgType == "event")
            {
                //获取事件类型
                $event_type = strtolower($postObj->Event);
                switch ($event_type)
                {
                    /*
                     * 用户未关注时，进行关注后的事件推送
                     */
                    case "subscribe":

                        $data = array(
                            'FromUserName'=>$our_open_id,
                            'ToUserName'=>$user_open_id,
                            'Content' => $welcome
                        );
                        $response = $this->buildMsgString($msgType, $data);
                        //进行其他的处理
                        echo $response;
                        break;

                    /*
                     * 用户已关注时，进行关注后的事件推送
                     */
                    case "scan":
                        $data = array(
                            'FromUserName'=>$our_open_id,
                            'ToUserName'=>$user_open_id,
                            'Content' => $welcome
                        );
                        $response = $this->buildMsgString($msgType, $data);
                        //进行其他的处理
                        echo $response;
                        break;

                    /*
                     * 上报地理位置事件
                     */
                    case "location":
                        break;

                    /*
                     * 自定义菜单事件
                     */
                    case "click":
                        break;

                    /*
                     * 点击菜单跳转链接时的事件推送
                     */
                    case "view":
                        break;

                }
            }
            /*
             * 用户发送图片消息
             */
            else if($msgType == "image")
            {

                $data = array(
                    'FromUserName'=>$our_open_id,
                    'ToUserName'=>$user_open_id,
                    'Content'=>$text_response
                );
                $response = $this->buildMsgString("text", $data);
                echo $response;
            }
            /*
             * 用户发送语音消息
             */
            else if($msgType == "voice")
            {

            }
            /*
             * 用户发送视频消息
             */
            else if($msgType == "video")
            {

            }
            /*
             * 用户发送小视频消息
             */
            else if($msgType == "shortvideo")
            {

            }
            /*
             * 用户发送地理位置消息
             */
            else if($msgType == "location")
            {

            }
            /*
             * 用户发送链接消息
             */
            else if($msgType == "link")
            {

            }
            /*
             * 其他消息
             */
            else{

            }

            exit();
        }
    }

    /**
     * 检测接入是否有效
     */
    private function isValid()
    {
        $echoStr = isset($_GET['echostr']) ? trim($_GET['echostr']) : '';
        if(!empty($echoStr)){
            if($this->checkSignature()){
                exit($echoStr);
            }else{
                exit('');
            }
        }

    }

    /**
     * 接入签名算法
     * @throws Exception
     * @return boolean
     */
    private function checkSignature()
    {

        $signature = isset($_GET['signature']) ? trim($_GET['signature']) : '';
        $timestamp = isset($_GET['timestamp']) ? trim($_GET['timestamp']) : '';
        $nonce = isset($_GET['nonce']) ? trim($_GET['nonce']) : '';

        $token = $this->token;
        $tmpArr = array($token,$timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 构建被动回复消息回复内容模板
     * @param string $msgType 消息类型
     * ---text  文本消息
     * ---image 图片消息
     * ---voice 语音消息
     * ---video 视频消息
     * ---music 音乐消息
     * ---news  图文消息
     * @param array $data 传递要发送的参数，以数组形式传递。根据消息类型不同，传递的参数也各不相同。
     * 注意，数据里面的key值必须和发送模板里面的一致，大小写不可出错
     * 发送图文消息是特别要注意格式
     * ---参考链接：http://mp.weixin.qq.com/wiki/14/89b871b5466b19b3efa4ada8e577d45e.html
     */
    private function buildMsgString($msgType,Array $data){
        if(empty($msgType))
        {
            return "";
        }
        $time = time();
        switch ($msgType)
        {
            /*
             * 发送文本消息
             */
            case "text":
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
                $resultStr = sprintf($textTpl, $data['ToUserName'], $data['FromUserName'], $time, $data['Content']);
                break;
            /*
             * 发送图片消息
             */
            case "image":
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[image]]></MsgType>
                            <Image>
                            <MediaId><![CDATA[%s]]></MediaId>
                            </Image>
                            </xml>";
                $resultStr = sprintf($textTpl, $data['ToUserName'], $data['FromUserName'], $time, $data['MediaId']);
                break;

            /*
             * 发送语音消息
             */
            case "voice":
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[voice]]></MsgType>
                            <Voice>
                            <MediaId><![CDATA[%s]]></MediaId>
                            </Voice>
                            </xml>";
                $resultStr = sprintf($textTpl, $data['ToUserName'], $data['FromUserName'], $time, $data['MediaId']);
                break;

            /*
             * 发送视频消息
             */
            case "video":
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[video]]></MsgType>
                            <Video>
                            <MediaId><![CDATA[%s]]></MediaId>
                            <Title><![CDATA[%s]]></Title>
                            <Description><![CDATA[%s]]></Description>
                            </Video> 
                            </xml>";
                $resultStr = sprintf($textTpl, $data['ToUserName'], $data['FromUserName'], $time, $data['MediaId'],$data['Title'],$data['Description']);
                break;

            /*
             * 发送音乐消息
             */
            case "music":
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[music]]></MsgType>
                            <Music>
                            <Title><![CDATA[%s]]></Title>
                            <Description><![CDATA[%s]]></Description>
                            <MusicUrl><![CDATA[%s]]></MusicUrl>
                            <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                            <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
                            </Music>
                            </xml>";
                $resultStr = sprintf($textTpl, $data['ToUserName'], $data['FromUserName'], $time, $data['Title'],$data['Description'],$data['MusicUrl'],$data['HQMusicUrl'],$data['ThumbMediaId']);

                break;
            /*
             * 发送图文消息
             */
            case "news":
                if(count($data['Articles']) == 0)
                {
                    $resultStr = "";
                }else
                {
                    $textTpl_1 = "<xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[news]]></MsgType>
                                <ArticleCount>%s</ArticleCount>
                                <Articles>";
                    $resultStr = sprintf($textTpl_1, $data['ToUserName'], $data['FromUserName'],$time, count($data['Articles']));
                    $textTpl_2 = "<item>
                                <Title><![CDATA[%s]]></Title> 
                                <Description><![CDATA[%s]]></Description>
                                <PicUrl><![CDATA[%s]]></PicUrl>
                                <Url><![CDATA[%s]]></Url>
                                </item>";
                    foreach ($data['Articles'] as $key => $a)
                    {
                        $resultStr .= sprintf($textTpl_2, $a['Title'], $a['Description'], $a['PicUrl'],$a['Url']);
                        echo $resultStr."<br>";
                    }
                    $textTpl_3 = "</Articles>
                                </xml>";

                    echo $resultStr."<br>";
                    $resultStr .= $textTpl_3;
                    echo $resultStr."<br>";
                }
                break;
            default:
                $resultStr = "";
                break;

        }
        return $resultStr;
    }

}

?>