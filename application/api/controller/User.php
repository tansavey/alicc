<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Env;

/**
 * 首页接口
 */
class User extends Api
{
    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['token','publicKey','publicKey2'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['test2'];
    public function dataRsaEncode(){
        $public_key = Env::get('important.public_key');
        $client_secret = 'admin123';
        $pass =$this->rsaEncode($client_secret,$public_key);
        $this->success(__('success'), $pass);

    }
    public function publicKey(){
        $public_key = Env::get('important.public_key');
        $this->success(__('success'), $public_key);

    }

    /**
     * 会员登录
     *
     * @ApiMethod (POST)
     * @param string $account  账号
     * @param string $password 密码
     */
    public function token()
    {
        $data = $this->request->post();
        $client_id = $data['client_id'];
        $private_key = Env::get('important.private_key');
        $client_secret =$this->rsaDecode($data['client_secret'],$private_key);

        if (!$client_id || !$client_secret) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($client_id, $client_secret);
        if ($ret) {


            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
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

    function rsaDecode($password, $rsa_public_key)
    {
        // 要执行的代码
        $rsa_private = "-----BEGIN PRIVATE KEY-----\n";
        $rsa_private = $rsa_private . $rsa_public_key;
        $rsa_private = $rsa_private . "\n-----END PRIVATE KEY-----";
        $private_key = openssl_pkey_get_private($rsa_private);
        if (!$private_key) {
            return('私钥不可用');
        }
        $return_de = openssl_private_decrypt(base64_decode($password), $decrypted, $private_key);
        if (!$return_de) {
            return('解密失败,请检查RSA秘钥');
        }
        return $decrypted;

    }
}
