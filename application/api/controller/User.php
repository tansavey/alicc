<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Env;

/**
 * 首页接口
 */
class User extends Api
{

    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['publicKey','token'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = [''];

    public function publicKey(){
        $public_key = Env::get('important.public_key');
        $this->success(__('success'), $public_key);
    }

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
