<?php
// 本页面定义路由规则。规则从上到下解析。
// 例子： "正则表达式" => "控制器名/方法名"
// 捕获到的内容会作为参数顺次传入方法中。
return array(
	'^$' => 'Index/index',
    '^info$' => 'Index/info',
    '^api/user-reg' => 'User/reg',
    '^api/pre-login' => 'User/preLogin',
    '^api/user-login' => 'User/login',
    '^api/user-logout' => 'User/logout',
    '^api/user-detail' => 'User/userdetail',
    '^api/update-rn' => 'User/updateRn',
    '^api/reset-streamkey' => 'User/reset_upkey',
    '^api/set-roomstatus' => 'User/changeRoomStatus',
    '^api/list-streaming' => 'Index/getlist',
    '^api/get-roominfo' => 'Index/playurl',

    '^rtmp_server/publish' => 'Server/on_publish'
);