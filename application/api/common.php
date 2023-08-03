<?php
if (!function_exists('curlPost')) {

    function curlPost($url = '', $postData = '', $headers = ["Content-Type:application/json"], $options = array())
    {
        $header = $headers;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //发送POST请求
        $data = curl_exec($ch);
        //错误中断
        if ($data === false) {
            echo 'Curl error: ' . curl_error($ch);
            exit();
        }
        //关闭curl
        curl_close($ch);
        //输出响应结果
        return $data;
    }
}

if (!function_exists('curlGet')) {
     function curlGet($url)
    {
        //初始化curl
        $ch = curl_init();
        //设置curl选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //发送GET请求
        $data = curl_exec($ch);
        //错误中断
        if ($data === false) {
            echo 'Curl error: ' . curl_error($ch);
            exit();
        }
        //关闭curl
        curl_close($ch);
        //输出响应结果
        return $data;
    }
}

if (!function_exists('getUserIP')) {
    function getUserIP()
    {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknow")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknow")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknow")) {
            $ip = getenv("REMOTE_ADDR");
        } else if (isset($_SERVER["REMOTE_ADDR"]) && $_SERVER["REMOTE_ADDR"] && strcasecmp($_SERVER["REMOTE_ADDR"], "unknow")) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } else {
            $ip = "unknow";
        }
        return $ip;
    }
}

if (!function_exists('ht')) {
    function ht($vars)
    {
        print_r($vars);exit;
    }
}

