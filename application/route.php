<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;
Route::rule('evo/oauth/public-key','api/user/publicKey'); //获取公钥
Route::rule('oauth/extend/token','api/user/token'); //认证，获取token
Route::rule('device/subsystem/page','api/user/subsystem'); //获取设备分页
Route::rule('channel/subsystem/page','api/user/channel'); //获取设备通道分页evo-event/1.0.0/subscribe/mqinfo
Route::rule('video/stream/realtime','api/user/hls'); //获取hls视频流
Route::rule('Record/Get/ChannelMonthRecordStatus','api/user/GetChannelMonthRecordStatus'); //查询单个通道录像存在状态
Route::rule('Record/Query/Records','api/user/QueryRecords'); //查询普通录像信息列表
Route::rule('video/stream/record','api/user/record'); //获取hls视频流
Route::rule('evo-event/subscribe/mqinfo','api/user/mqinfo'); //时间订阅
Route::rule('DMS/Ptz/OperateDirect','api/user/OperateDirect'); //球机控制
Route::rule('alarm/record/page','api/user/alarmRecordPage'); //alarm事件分页查询
Route::rule('alarm/record/count','api/user/count'); //alarm事件分页查询

/*实时预览*/
Route::rule('live/cameraid/:cameraid/substream/:m3u8','api/user/hlsRedirect');
/*录像回放*/
Route::rule('vod/device/cameraid/:cameraid/substream/:substream/recordtype/:recordtype/totallength/:totallength/begintime/:begintime/endtime/:m3u8','api/user/hlsRedirect');


//Route::miss('api/user/hlsRedirect');

return [
    //别名配置,别名只能是映射到控制器且访问时必须加上请求的方法
    '__alias__'   => [
    ],
    //变量规则
    '__pattern__' => [
    ],
//        域名绑定到模块
//        '__domain__'  => [
//            'admin' => 'admin',
//            'api'   => 'api',
//        ],
];
