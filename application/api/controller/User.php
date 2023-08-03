<?php

namespace app\api\controller;

use app\common\controller\Api;
use Rtgm\sm\RtSm2;
use think\Env;
use think\Request;
use function fast\e;

/**
 * 首页接口
 */
class User extends Api
{

    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['publicKey', 'token', 'channel', 'hls', 'mqinfoCallback', 'mqinfo'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];
    //用户token
    protected $user_token = null;
    //服务器的token
    protected $server_token = null;


    public function __construct(Request $request = null)
    {

        if (isset($request->header()['token'])) {
            $user_token = $request->header()['token'];
            $this->user_token = $user_token;
            $server_tokenArr = cache($user_token);
            $refresh_token = Server::refreshToken($server_tokenArr['token']['refresh_token']);
            $server_tokenArr['token']['access_token'] = $refresh_token['data']['access_token'];
            cache($user_token, $server_tokenArr);
            $this->server_token = cache($user_token)['token'];
        }
        /*本地测试*/
//        else {
//            $user_token = '7379aa2c-b16b-4234-8cfe-19beb04c2146';
//            $this->user_token = $user_token;
//            $server_tokenArr = cache($user_token);
//            $refresh_token = Server::refreshToken($server_tokenArr['token']['refresh_token']);
//            $server_tokenArr['token']['access_token'] = $refresh_token['data']['access_token'];
//            cache($user_token, $server_tokenArr);
//            $this->server_token = cache($user_token)['token'];
//        }
        parent::__construct($request);
    }

    /**
     *获取publicKey
     * 2023/8/2
     * @return void
     */
    public function publicKey()
    {
        $m2 = new RtSm2();
        $generatekey = $m2->generatekey();
        $private_key = $generatekey[0];
        $public_key = $generatekey[1];
        cache($public_key,$private_key,3000);

        $this->success(__('success'), ['publickey'=>$public_key]);
    }

    /**
     *获取token
     * 2023/7/31
     * @return void
     */
    public function token()
    {

        $data = $this->request->post();
        $client_id = $data['client_id'];
        $public_key = $data['public_key'];
        if (cache($public_key)){
            $private_key = cache($public_key);
        }else{
            $this->error($this->auth->getError());
        }
        $m2 = new RtSm2();
        $client_secret = $m2->doDecrypt($data['client_secret'], $private_key);
        if (!$client_id || !$client_secret) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($client_id, $client_secret);
        if ($ret) {

            /*获取服务器Token*/
            $server_public_key = Server::public_key();
            $server_token = Server::token($server_public_key);
            /*创建用户的Token*/
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $user_token = $data['userinfo']['token'];
            cache($user_token, ['token' => $server_token, 'clientIp' => getUserIP(), 'time' => time()]);
            $this->success(__('Logged in successful'), [
                'token' => $user_token
            ]);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     *设备分页查询
     * 2023/7/31
     * @return void
     */
    public function subsystem()
    {
        $params = $this->request->post();
        $params['channelTypeList'] = [1];//只能查询视频设备
        $data = Server::subsystem($this->server_token, $params);
        $this->success(__('success'), $data['data']);
    }

    /**
     *设备通道分页查询
     * 2023/8/1
     * @return void
     */
    public function channel()
    {
        $params = $this->request->post();
        $params['channelTypeList'] = [1];//只能查询视频设备
        $data = Server::channel($this->server_token, $params);
        $this->success(__('success'), $data['data']);
    }

    /**
     *获取hls视频流
     * 2023/7/31
     * @return \think\response\View
     */
    public function hls()
    {
        //url
        $user_token = '7379aa2c-b16b-4234-8cfe-19beb04c2146';
        $this->user_token = $user_token;
        $this->server_token = cache($user_token)['token'];
        $params = [
            "channelId" => "1000072$1$0$1",
            "streamType" => "1",
            "effectiveTime" => "3000"
        ];
        $params_exit['data'] = [
            'channelId' => $params['channelId'],
            'streamType' => $params['streamType'],
            'type' => 'hls',
        ];
        $data = Server::hls($this->server_token, $params_exit);
        $url = $data['data']['url'];
        $url = $this->hlsToken($url, $params['effectiveTime']);

        return view('hls2', [
            'url' => $url,
            'token' => $this->user_token
        ]);

        //post
        $params = $this->request->post();
        $params_exit['data'] = [
            'channelId' => $params['channelId'],
            'streamType' => $params['streamType'],
            'type' => 'hls',
        ];
        $data = Server::hls($this->server_token, $params_exit);
        $url = $data['data']['url'];
        $url = $this->hlsToken($url, $params['effectiveTime']);
        $this->success(__('success'), [
            'url' => $url,
            'token' => $this->user_token
        ]);
    }

    /**
     *查询单个通道录像存在状态
     * 2023/7/31
     * @return void
     */
    function GetChannelMonthRecordStatus()
    {
        $params = $this->request->post();
        $params_exit['data'] = $params;
        $data = Server::GetChannelMonthRecordStatus($this->server_token, $params_exit);
        $this->success(__('success'), $data['data']);
    }

    /**
     *查询普通录像信息列表
     * 2023/8/1
     * @return void
     */
    function QueryRecords()
    {
        $params = $this->request->post();
        $params_exit['data'] = $params;
        $data = Server::QueryRecords($this->server_token, $params_exit);
        $this->success(__('success'), $data['data']);
    }

    /**
     *录像回放
     * 2023/8/1
     * @return void
     */
    function record()
    {
        //url
//        $user_token = '7379aa2c-b16b-4234-8cfe-19beb04c2146';
//        $this->user_token = $user_token;
//        $this->server_token = cache($user_token)['token'];
//        $params = [
//            "channelId" => "1000070$1$0$4",
//            "streamType" => "1",
//            "recordType" => "1",
//            "beginTime" => "2023-7-26 11:10:11",
//            "endTime" => "2023-7-26 12:10:11",
//            "recordSource" => "2",
//        ];
//        $params_exit['data'] = [
//            'channelId' => $params['channelId'],
//            'streamType' => $params['streamType'],
//            'recordType' => $params['recordType'],
//            'beginTime' => $params['beginTime'],
//            'endTime' => $params['endTime'],
//            'recordSource' => $params['recordSource'],
//            'type' => 'hls',
//        ];
//        $data = Server::record($this->server_token, $params_exit);
//        $url = $data['data']['url'];
//        $url = $this->hlsToken($url, 120);
//
//        return view('record', [
//            'url' => $url,
//            'token' => $this->user_token
//        ]);

        //post
        $params = $this->request->post();
        $params_exit['data'] = [
            'channelId' => $params['channelId'],
            'streamType' => $params['streamType'],
            'recordType' => $params['recordType'],
            'beginTime' => $params['beginTime'],
            'endTime' => $params['endTime'],
            'recordSource' => $params['recordSource'],
            "effectiveTime" => "86400",
            'type' => 'hls',
        ];
        $data = Server::record($this->server_token, $params_exit);
        $url = $data['data']['url'];
        $url = $this->hlsToken($url, 120);
        $this->success(__('success'), [
            'url' => $url,
            'token' => $this->user_token
        ]);
    }

    /**
     *球机控制
     * 2023/8/2
     * @return void
     */
    public function OperateDirect()
    {
        $params = $this->request->post();
        $params['data'] = $params;
        $data = Server::OperateDirect($this->server_token, $params);
        ht($data);
        $this->success(__('success'), $data['data']);
    }

    /**
     *事件订阅，订阅成功后可以接收来自平台端监控报警推送
     * 2023/8/1
     * @return void
     */
    public function mqinfo()
    {
        $data = '{
            "param": {
                "monitors": [
                    {
                        "monitor": "http://192.168.6.113:8090/api/user/mqinfoCallback",
                        "monitorType": "url",
                        "events": [
                            {
                                "category": "alarm",
                                "subscribeAll": 1,
                                "domainSubscribe": 2
                            }
                        ]
                    }
                ],
                "subsystem": {
                    "subsystemType": 0,
                    "name": "192.168.6.113_8090",
                    "magic": "192.168.6.113_8090"
                }
            }
        }';
        $data = json_decode($data, true);
        $data = Server::mqinfo($this->server_token, $data);
        halt($data);
        $data = $data['data'];
        $this->success(__('success'), $data);
    }

    /**
     *统计
     * 2023/8/2
     * @return void
     */
    public function statistical()
    {
        $params = $this->request->post();
        $params = [
            "pageNum" => 1,
            "pageSize" => 10,
//            "nodeCodeList" => ["1000072"],
        ];
        $data = Server::alarmRecordPage($this->server_token, $params);
        ht($data);
        $newData = array();

    }


    public function statistical3()
    {
        $params = $this->request->post();
        $params['channelTypeList'] = [1];//只能查询视频设备
        $data = Server::channel($this->server_token, $params);
        $data = $data['data']['pageData'];
        $newData = array();
        foreach ($data as $dataKey => $dataVo) {
            $isData = array();
            $isData['deviceCode'] = $dataVo['deviceCode'];//通道编号
            $isData['channelSeq'] = $dataVo['channelSeq'];//通道序号
            $isData['channelCode'] = $dataVo['channelCode'];//通道编码
            $isData['channelName'] = $dataVo['channelName'];//通道名称
            $isData['isOnline'] = $dataVo['isOnline'];//设备通道在线状态
            $isData['stat'] = $dataVo['stat'];//状态 0:关闭 1:开启
            $isData['deviceCode'] = $dataVo['deviceCode'];
            $newData[] = $isData;
        }

        ht($data);
    }

    public function statistical2()
    {
        $params = $this->request->post();
        $params['unitTypes'] = [1];//单元类型 视频通道
        $params['categorys'] = [1];//设备大类 编码设备
        $params['types'] = ["1_2"];//设备小类 	IPC
        $data = Server::subsystem($this->server_token, $params);
        $data = $data['data']['pageData'];
        $newData = array();
        foreach ($data as $dataKey => $dataVo) {
            ht($dataVo);
            $deviceCode = $dataVo['deviceCode'];//设备编码
            $deviceName = $dataVo['deviceName'];//设备名称
            $deviceModel = $dataVo['deviceModel'];//设备型号
            $gpsX = $dataVo['gpsX'];//经度
            $gpsY = $dataVo['gpsY'];//经度
            foreach ($dataVo['units'] as $units) {
                foreach ($units['channels'] as $channels) {
                    if (isset($channels['cameraType']) && $channels['cameraType'] == 1) {
                        $is_data = array();
                        $is_data['deviceCode'] = $deviceCode;
                        $is_data['deviceName'] = $deviceName;
                        $is_data['deviceModel'] = $deviceModel;
                        $is_data['gpsX'] = $gpsX;
                        $is_data['gpsY'] = $gpsY;
                        $is_data['channelCode'] = $channels['channelCode'];//通道编码
                        $is_data['channelName'] = $channels['channelName'];//通道名称
                        $is_data['stat'] = $channels['stat'];//通道编码
                        $is_data['isOnline'] = $channels['isOnline'];//设备在线状态
                        $newData[] = $is_data;
                    }
                }
            }
        }

        ht($newData);
        halt($data);
    }

    /**
     *alarm事件分页查询
     * 2023/8/2
     * @return void
     */
    public function alarmRecordPage()
    {
        $params = $this->request->post();
        $data = Server::alarmRecordPage($this->server_token, $params);
        $this->success(__('success'), $data['data']);
    }

    /**
     *路由转换、添加pk随机字符串、设置过期时间
     * 2023/7/31
     * @param $url
     * @return string
     */
    public function hlsToken($url, $effectiveTime)
    {
        $user_token = $this->user_token;
        $hlsUrl = $url . "?pk=" . md5("ts$user_token" . time() . rand(10000, 99999));

        $parse_url = parse_url($hlsUrl);
        $host = $parse_url['host'];
        if (isset($parse_url['port'])) {
            $host .= ":" . $parse_url['port'];
        }
        $path = substr($hlsUrl, strripos($hlsUrl, $host) + strlen($host));
        $user_tokenArr = cache($user_token);
        $user_tokenArr[$path] = ['createTime' => time(), 'effectiveTime' => $effectiveTime];
        cache($user_token, $user_tokenArr);
        $hlsUrl = request()->domain() . $path;


        return $hlsUrl;
    }

    /**
     *RSA解密
     * 2023/7/31
     * @param $password
     * @param $rsa_public_key
     * @return string
     */
    function rsaDecode($password, $rsa_public_key)
    {
        // 要执行的代码
        $rsa_private = "-----BEGIN PRIVATE KEY-----\n";
        $rsa_private = $rsa_private . $rsa_public_key;
        $rsa_private = $rsa_private . "\n-----END PRIVATE KEY-----";
        $private_key = openssl_pkey_get_private($rsa_private);
        if (!$private_key) {
            return ('私钥不可用');
        }
        $return_de = openssl_private_decrypt(base64_decode($password), $decrypted, $private_key);
        if (!$return_de) {
            return ('解密失败,请检查RSA秘钥');
        }
        return $decrypted;

    }

    /**
     *路由反向代理
     * 2023/8/1
     * @return string|void
     */
    public function hlsRedirect()
    {
        $params = $this->request;
        /*验证cookie*/
        if (!isset($params->cookie()['token'])) {
            return '缺少跨域cookie.token';
        }
        $cookieToken = $params->cookie()['token'];

        /*验证user_token是否有效*/
        if (!cache($cookieToken)) {
            return '404 Not Found!----no_token';
        }
        $user_tokenArr = cache($cookieToken);
//        /*严重是否非法请求*/
//        $pk = $params->param()['pk'];
//        if ($pk != md5("ts$cookieToken")) {
//            return '404 Not Found!----no_token2';
//        }
        /*验证链接是否有效*/
        $url = request()->url();
        if (!isset($user_tokenArr[$url])) {
            return '404 Not Found!----url';
        }
        /*验证时间、IP*/
        $effectiveTime = $user_tokenArr[$url];
        $clientIp = getUserIP();
        if (cache($cookieToken)) {
            if ($user_tokenArr['clientIp'] != $clientIp) {
                return '404 Not Found!----clientIp';
            }
            if (($effectiveTime['createTime'] + $effectiveTime['effectiveTime']) < time()) {
                return '404 Not Found!----time';
            }
        } else {
            return '404 Not Found!----time';
        }

        /*开始代理请求*/
        $access_token = $user_tokenArr['token']['access_token'];
        $ip = Env::get('server.hls_ip');
        $pathinfo = $params->pathinfo();
        $pathinfo = str_replace("$", "%24", $pathinfo);
        $url = $ip . '/' . $pathinfo . "?token=$access_token";
        $params = curlGet($url);


        header('Content-Type: application/vnd.apple.mpegurl');
        header('Access-Control-Allow-Credentials: true');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: deny');
        header('X-XSS-Protection: 1; mode=block');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        header('Connection: keep-alive');
        header('Cache-Control: no-cache');
        header('Content-Range: bytes 0-154/155');
        echo $params;
    }

    /**
     *事件会调地址
     * 2023/8/1
     * @return void
     */
    public function mqinfoCallback()
    {
        $filename = "E:\phpstudy_pro\WWW\www.alicc.loca\public/test/" . date("Y-m-d hisa") . ".txt";
        $file = fopen($filename, "w") or die("Unable to open file!");
        $params = $this->request->post();
        $txt = json_encode($params, JSON_UNESCAPED_UNICODE);
        fwrite($file, $txt);
        fclose($file);
    }
}
