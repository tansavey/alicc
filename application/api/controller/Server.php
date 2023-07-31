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
     * @return void
     */
    public static function subsystem($server_token,$data)
    {
        $url = Env::get('server.ip') . '/evo-apigw/evo-brm/1.2.0/device/subsystem/page';
        $result = self::formatPost($server_token,$url, $data);
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

}
