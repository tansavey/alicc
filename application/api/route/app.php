<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

Route::get('c<id>', 'index/Index/category');

Route::get('d<id>', 'index/Index/detail');

Route::get('m<id>', 'index/Index/my_list');

Route::get('keyword_<keyword>', 'index/Index/search',['keyword'=>'.*']);

Route::get('search_<search_word>', 'index/Index/search',['search_word'=>'.*']);


Route::get('keyword<keyword?>', 'index/Index/search',[],['keyword'=>'.+']);

Route::get('search<search_word?>', 'index/index/search',[],['search_word'=>'.+']);


return [
    //别名配置,别名只能是映射到控制器且访问时必须加上请求的方法
    '__alias__'   => [
    ],
    //变量规则
    '__pattern__' => [
    ]
];