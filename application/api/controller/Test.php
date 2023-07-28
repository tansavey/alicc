<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Env;

/**
 * 首页接口
 */
class Test extends Api
{

    protected $noNeedLogin = ['dataRsaEncode'];
    protected $noNeedRight = ['test2'];

    public function dataRsaEncode(){
        $public_key = Env::get('important.public_key');
        $client_secret = 'admin123';
        $pass =$this->rsaEncode($client_secret,$public_key);
        $this->success(__('success'), $pass);

    }

    function rsaEncode($password, $rsa_public_key)
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
