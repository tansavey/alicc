<?php

namespace app\api\controller;

use think\Controller;
use think\Env;

/**
 * 首页接口
 */
class Server extends Controller
{

    public static function public_key()
    {
        $url = Env::get('server.ip') . '/evo-apigw/evo-oauth/1.0.0/oauth/public-key';
        $result = curlGet($url);
        $result = json_decode($result, true);
        return $result['data']['publicKey'];
    }

    /**
     *获取Token
     * 2023/7/31
     * @param $public_key
     * @return mixed
     */

    public static function token($public_key)
    {
        $data = [
            "grant_type" => "password",
            "username" => "apiadmin",
            "password" => self::rsaEncode('ts123456', $public_key),
            "client_id" => "tengsentest123",
            "client_secret" => "eccb3a8f-89f4-496b-99a6-c510f4ced947",
            "public_key" => $public_key,

        ];
        $url = Env::get('server.ip') . '/evo-apigw/evo-oauth/1.0.0/oauth/extend/token';
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $result = curlPost($url, $data);
        $result = json_decode($result, true);
        return $result['data'];
    }

    /**
     *设备分页查询
     * 2023/7/31
     * @param $server_token
     * @param $data
     * @return mixed
     */

    public static function subsystem($server_token,$data)
    {
        $url = Env::get('server.ip') . '/evo-apigw/evo-brm/1.2.0/device/subsystem/page';
        $result = self::formatPost($server_token,$url, $data);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     *设备通道分页查询
     * @param $server_token
     * @param $data
     * @return mixed
     */
    public static function channel($server_token,$data)
    {
        $url = Env::get('server.ip') . '/evo-apigw/evo-brm/1.2.0/device/channel/subsystem/page';
        $result = self::formatPost($server_token,$url, $data);
        $result = json_decode($result, true);
        return $result;
    }


    /**
     *球机控制
     * 2023/8/2
     * @param $server_token
     * @param $data
     * @return mixed
     */

    public static function OperateDirect($server_token,$data)
    {
        $url = Env::get('server.ip') . '/evo-apigw/admin/API/DMS/Ptz/OperateDirect';
        $result = self::formatPost($server_token,$url, $data);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     *alarm事件分页查询
     * 2023/8/2
     * @param $server_token
     * @param $data
     * @return mixed
     */
    public static function alarmRecordPage($server_token,$data)
    {
        $url = Env::get('server.ip') . '/evo-apigw/evo-event/1.2.0/alarm-record/page';
        $result = self::formatPost($server_token,$url, $data);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     *alarm事件分页查询
     * 2023/8/2
     * @param $server_token
     * @param $data
     * @return mixed
     */
    public static function count($server_token,$data)
    {
        $url = Env::get('server.ip') . '/evo-apigw/evo-event/1.0.0/alarm-record/count-num';
        $result = self::formatPost($server_token,$url, $data);
        $result = json_decode($result, true);
        return $result;
    }

    public static function hls($server_token,$data)
    {
        $url = Env::get('server.ip') . '/evo-apigw/admin/API/video/stream/realtime';
        $result = self::formatPost($server_token,$url, $data);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 查询单个通道录像存在状态
     */
    public static function GetChannelMonthRecordStatus($server_token,$data){
        $url = Env::get('server.ip') . '/evo-apigw/admin/API/SS/Record/GetChannelMonthRecordStatus';
        $result = self::formatPost($server_token,$url, $data);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     *查询普通录像信息列表
     * 2023/8/1
     * @param $server_token
     * @param $data
     * @return mixed
     */
    public static function QueryRecords($server_token,$data){
        $url = Env::get('server.ip') . '/evo-apigw/admin/API/SS/Record/QueryRecords';
        $result = self::formatPost($server_token,$url, $data);
        $result = json_decode($result, true);
        return $result;
    }


    /**
     *录像回放
     * 2023/8/1
     * @param $server_token
     * @param $data
     * @return mixed
     */
    public static function record($server_token,$data)
    {
        $url = Env::get('server.ip')  . '/evo-apigw/admin/API/video/stream/record';
        $result = self::formatPost($server_token,$url, $data);
        $result = json_decode($result, true);
        return $result;
    }


    /**
     *事件订阅
     * 2023/8/1
     * @param $server_token
     * @param $data
     * @return mixed
     */
    public static function mqinfo($server_token,$data)
    {
        $url = Env::get('server.ip')  . '/evo-apigw/evo-event/1.0.0/subscribe/mqinfo';
        $result = self::formatPost($server_token,$url, $data);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     *刷新token
     * 2023/8/1
     * @param $refresh_token
     * @return mixed
     */

    public static function refreshToken($refresh_token)
    {
        $data = [
            "grant_type" => "refresh_token",
            "client_id" => Env::get('server.client_id'),
            "client_secret" => Env::get('server.client_secret'),
            "refresh_token" => $refresh_token,
        ];
        $url = Env::get('server.ip') . '/evo-apigw/evo-oauth/1.0.0/oauth/extend/refresh/token';
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $result = curlPost($url, $data);
        $result = json_decode($result,true);
        return $result;
    }


    /**
     *封装POST请求
     * 2023/7/31
     * @param $user_token
     * @param $url
     * @param $data
     * @return bool|string|null
     */
    public static function formatPost($server_token,$url,$data){
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        /*发送*/
        $token_type = $server_token['token_type'];
        $access_token = $server_token['access_token'];
        $header = [
            "Content-Type:application/json",
            "Authorization: $token_type $access_token"
        ];
        $result = curlPost($url, $data, $header);
        return $result;

    }

    /**
     *RSA加密
     * 2023/7/31
     * @param $password
     * @param $rsa_public_key
     * @return string
     */
    public static function rsaEncode($password, $rsa_public_key)
    {
        // 要执行的代码
        $rsa_public = "-----BEGIN PUBLIC KEY-----\n";
        $rsa_public = $rsa_public . $rsa_public_key;
        $rsa_public = $rsa_public . "\n-----END PUBLIC KEY-----";
        $key = openssl_pkey_get_public($rsa_public);
        if (!$key) {
            echo "公钥不可用\n";
            echo $rsa_public;
        }
        //openssl_public_encrypt 第一个参数只能是string
        //openssl_public_encrypt 第二个参数是处理后的数据
        //openssl_public_encrypt 第三个参数是openssl_pkey_get_public返回的资源类型
        $return_en = openssl_public_encrypt($password, $crypted, $key);
        if (!$return_en) {
            echo "加密失败,请检查RSA秘钥";
        }
        return base64_encode($crypted);
    }


    /*事件订阅查询*/
    public static function subscribeList($data)
    {
        $url = Env::get('server.ip')  . '/evo-apigw/evo-event/1.0.0/subscribe/subscribe-list'.$data;
//        ht($url);
        $result = curlGet($url);
        $result = json_decode($result, true);
        return $result;
    }
    /*取消订阅查询*/
    public static function subscribe($server_token,$data)
    {
        $url = Env::get('server.ip')  . '/evo-apigw/evo-event/1.0.0/subscribe/mqinfo?name=10.33.75.97_8918';
        $result = self::formatPost($server_token,$url, $data);
        $result = json_decode($result, true);
        return $result;
    }

}
