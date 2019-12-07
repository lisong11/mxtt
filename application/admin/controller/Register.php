<?php

namespace app\admin\controller;

use think\Session;
use think\Db;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class Register extends Common
{

    public function _initialize()
    {

    }

    public function index()
    {

        return $this->fetch();
    }

    public function ajaxCheckPhone()
    {
        $data = $this->request->param();
        if (!empty($data['user_name'])) {
            $phone = db('admin_list')->where('user_name', $data['user_name'])->select();
            if (!empty($phone)) {
                return array(
                    "code" => 1,
                );
            }
        }
    }

    public function ajaxCheckCode()
    {
        $data = $this->request->param();
        if (!empty($data['reset']) && !empty($data['code'])) {
            $newCode = $data['code'];
            $code = Session::get('reset_code');
            if ($newCode != $code) {
                return array(
                    "code" => 1,
                    "data" => $code
                );
            }
        }
        if (!empty($data['code']) && empty($data['reset'])) {
            $newCode = $data['code'];
            $code = Session::get('register_code');
            if ($newCode != $code) {
                return array(
                    "code" => 1,
                );
            }
        }

    }

    public function registerDo()
    {
        $data = $this->request->param();
        $iniPass = $this->getInitializePass($data['password']);
        $data = array_merge($data, $iniPass);
        $data['role_id'] = 2;
        $res = $this->AdminListModel->allowField(true)->save($data);
        if (!$res) {
            $this->error("注册出现错误");
        }
        $this->redirect("/index/login.html");
    }

    public function resetDo()
    {
        $data = $this->request->param();
        $iniPass = $this->getInitializePass($data['password']);
        $data = array_merge($data, $iniPass);
        db('admin_list')->where(['user_name' => $data['user_name']])->update(['password' => $data['password']]);
        $this->redirect("/index/login.html");
    }

    public function sendSms()
    {
        $data = $this->request->param();
        $phone = $data['user_name'];
        if ($data['reset'] == 1) {
            $TemplateCode = 'SMS_174880529';
            $code_key = 'reset_code';
        } else {
            $TemplateCode = 'SMS_174880530';
            $code_key = 'register_code';
        }
        $newCode = $this->randCode();
        $randCode = json_encode(array('code' => $newCode));
        Session::set($code_key, $newCode);
        $code_res = $this->sms($phone, $randCode, $TemplateCode);
        if ($code_res['code'] = 'ok') {
            return array(
                "code" => 0,
            );
        } else {
            return array(
                "code" => 1,
            );
        }
    }

    //生成手机验证码
    public function randCode()
    {
        $key = '';
        $pattern = '1234567890';
        for ($i = 0; $i < 6; $i++) {
            $key .= $pattern[mt_rand(0, 9)];
        }
        return $key;
    }

    public function sms($phone = '', $randCode = array(), $TemplateCode = '')
    {
        AlibabaCloud::accessKeyClient('LTAI4FjKUiEjMJbF3kKPPMM6', 'ZmeadmaSP8w25f77HDmjnofD3z92dL')
            ->regionId('cn-hangzhou')
            ->asDefaultClient();

        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'PhoneNumbers' => "$phone",
                        'SignName' => "芈小兔pc端",
                        'TemplateCode' => "$TemplateCode",
                        'TemplateParam' => "$randCode",
                    ],
                ])
                ->request();
            $res = $result->toArray();
            return $res;
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
    }

    public function resetPass()
    {
        return $this->fetch();
    }

}

?>