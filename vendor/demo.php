<?php
require_once 'aliyun-php-sdk/aliyun-php-sdk-core/Config.php';    // 假定您的源码文件和aliyun-php-sdk处于同一目录。
require_once 'aliyun-php-sdk/aliyun-oss-php-sdk-2.2.4/autoload.php';

use vod\Request\V20170321 as vod;
use OSS\OssClient;
use OSS\Core\OssException;

class Video
{
    // 使用账号AK初始化VOD客户端
    public function init_vod_client($accessKeyId, $accessKeySecret)
    {
        $regionId = 'cn-shanghai';  // 点播服务所在的Region，国内请填cn-shanghai，不要填写别的区域
        $profile = DefaultProfile::getProfile($regionId, $accessKeyId, $accessKeySecret);
        return new DefaultAcsClient($profile);
    }

    // 获取视频上传地址和凭证
    public function create_upload_video($vodClient)
    {
        $request = new vod\CreateUploadVideoRequest();

        $request->setTitle("视频标题");        // 视频标题(必填参数)
        $request->setFileName("文件名称.mov"); // 视频源文件名称，必须包含扩展名(必填参数)

        $request->setDescription("视频描述");  // 视频源文件描述(可选)
        $request->setCoverURL(""); // 自定义视频封面(可选)
        $request->setTags("标签1,标签2"); // 视频标签，多个用逗号分隔(可选)

        return $vodClient->getAcsResponse($request);
    }

    // 使用上传凭证和地址信息初始化OSS客户端（注意需要先Base64解码并Json Decode再传入）
    public function init_oss_client($uploadAuth, $uploadAddress)
    {
        $ossClient = new OssClient($uploadAuth['AccessKeyId'], $uploadAuth['AccessKeySecret'], $uploadAddress['Endpoint'],
            false, $uploadAuth['SecurityToken']);
        $ossClient->setTimeout(86400 * 7);    // 设置请求超时时间，单位秒，默认是5184000秒, 建议不要设置太小，如果上传文件很大，消耗的时间会比较长
        $ossClient->setConnectTimeout(10);  // 设置连接超时时间，单位秒，默认是10秒
        return $ossClient;
    }

    // 使用简单方式上传本地文件：适用于小文件上传；最大支持5GB的单个文件
    public function upload_local_file($ossClient, $uploadAddress, $localFile)
    {
        return $ossClient->uploadFile($uploadAddress['Bucket'], $uploadAddress['FileName'], $localFile);
    }

// 大文件分片上传，支持断点续传；最大支持48.8TB
    public function multipart_upload_file($ossClient, $uploadAddress, $localFile)
    {
        return $ossClient->multiuploadFile($uploadAddress['Bucket'], $uploadAddress['FileName'], $localFile);
    }
}
// 使用账号AK初始化VOD客户端
//function init_vod_client($accessKeyId, $accessKeySecret) {
//    $regionId = 'cn-shanghai';  // 点播服务所在的Region，国内请填cn-shanghai，不要填写别的区域
//    $profile = DefaultProfile::getProfile($regionId, $accessKeyId, $accessKeySecret);
//    return new DefaultAcsClient($profile);
//}
//

//
//// 刷新上传凭证
//function refresh_upload_video($vodClient, $videoId) {
//    $request = new vod\RefreshUploadVideoRequest();
//    $request->setVideoId($videoId);
//    return $vodClient->getAcsResponse($request);
//}
//
//// 使用上传凭证和地址信息初始化OSS客户端（注意需要先Base64解码并Json Decode再传入）
//function init_oss_client($uploadAuth, $uploadAddress) {
//    $ossClient = new OssClient($uploadAuth['AccessKeyId'], $uploadAuth['AccessKeySecret'], $uploadAddress['Endpoint'],
//        false, $uploadAuth['SecurityToken']);
//    $ossClient->setTimeout(86400*7);    // 设置请求超时时间，单位秒，默认是5184000秒, 建议不要设置太小，如果上传文件很大，消耗的时间会比较长
//    $ossClient->setConnectTimeout(10);  // 设置连接超时时间，单位秒，默认是10秒
//    return $ossClient;
//}
//
//// 使用简单方式上传本地文件：适用于小文件上传；最大支持5GB的单个文件
//// 更多上传方式参考：https://help.aliyun.com/document_detail/32103.html
//function upload_local_file($ossClient, $uploadAddress, $localFile) {
//    return $ossClient->uploadFile($uploadAddress['Bucket'], $uploadAddress['FileName'], $localFile);
//}
//
//// 大文件分片上传，支持断点续传；最大支持48.8TB
//function multipart_upload_file($ossClient, $uploadAddress, $localFile) {
//    return $ossClient->multiuploadFile($uploadAddress['Bucket'], $uploadAddress['FileName'], $localFile);
//}
//
//$accessKeyId = '<AccessKeyId>';                    // 您的AccessKeyId
//$accessKeySecret = '<AccessKeySecret>';            // 您的AccessKeySecret
//$localFile = '/Users/yours/Video/testVideo.flv';   // 需要上传到VOD的本地视频文件的完整路径
//
//try {
//    // 初始化VOD客户端并获取上传地址和凭证
//    $vodClient = init_vod_client($accessKeyId, $accessKeySecret);
//    $createRes = create_upload_video($vodClient);
//
//    // 执行成功会返回VideoId、UploadAddress和UploadAuth
//    $videoId = $createRes->VideoId;
//    $uploadAddress = json_decode(base64_decode($createRes->UploadAddress), true);
//    $uploadAuth = json_decode(base64_decode($createRes->UploadAuth), true);
//
//    // 使用UploadAuth和UploadAddress初始化OSS客户端
//    $ossClient = init_oss_client($uploadAuth, $uploadAddress);
//
//    // 上传文件，注意是同步上传会阻塞等待，耗时与文件大小和网络上行带宽有关
//    //$result = upload_local_file($ossClient, $uploadAddress, $localFile);
//    $result = multipart_upload_file($ossClient, $uploadAddress, $localFile);
//    printf("Succeed, VideoId: %s", $videoId);
//
//} catch (Exception $e) {
//    // var_dump($e);
//    printf("Failed, ErrorMessage: %s", $e->getMessage());
//}



