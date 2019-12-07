<?php

namespace app\admin\controller;

use think\Session;

class Sign
{
    public function test(){
        $data = array(
            "Action" => "CreateUploadVideo",
            "Title" => "exampleTitle",
            "FileName" => "example.avi",
            "FileSize" => "10485760",
        );
        $res=$this->getSignature($data);
        dump($res);
    }
    public function getSignature($data = [])
    {
        $key = 'LTAI4FjKUiEjMJbF3kKPPMM6';//这里是阿里云的accesskeyid 和accesskeysecret
        $secret = 'ZmeadmaSP8w25f77HDmjnofD3z92dL';
//这是请求api 的公共请求参数，
        $publicParams = array(
            "Format" => "JSON",
            "Version" => "2014-08-15",
            "AccessKeyId" => $key,
            "Timestamp" => date('Y-m-d\TH:i:s\Z', time() - date('Z')),
//            date("Y-m-d\TH:i:s\Z"),
            "SignatureMethod" => "HMAC-SHA1",
            "SignatureVersion" => "1.0",
            "SignatureNonce" => substr(md5(rand(1, 99999999)), rand(1, 9), 14),
        );
        $params = array_merge($publicParams, $data);
        $params['Signature'] =  $this->sign($params, $secret);
        $uri = http_build_query($params);
        $url = 'https://rds.aliyuncs.com/?'.$uri;
        return $params['Signature'];
    }

    public function sign($params, $accessSecret, $method = "GET")
    {
        ksort($params);
        $stringToSign = strtoupper($method) . '&' . $this->percentEncode('/') . '&';
        $tmp = "";
        foreach ($params as $key => $val) {
            $tmp .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($val);
        }
        $tmp = trim($tmp, '&');
        $stringToSign = $stringToSign . $this->percentEncode($tmp);
        $key = $accessSecret . '&';
        $hmac = hash_hmac("sha1", $stringToSign, $key, true);
        return base64_encode($hmac);
    }


    public function percentEncode($value = null)
    {
        $en = urlencode($value);
        $en = str_replace("+", "%20", $en);
        $en = str_replace("*", "%2A", $en);
        $en = str_replace("%7E", "~", $en);
        return $en;
    }



}

?>