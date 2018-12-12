<?php
return array(
	//基本配置
	'sitename' => "FeelyBlog",
	'default_title' => 'Feelyblog',
	'copyright' => '',
	'is_hsts' => true, //是否使用HTTPS严格传输安全
    'is_acao' => true,
	'streaming_address' => 'rtmp://live.minyami.net/livesend',
	'livestreaming_prefix' => 'https://stream.minyami.net/streaming/',
	//数据库配置
	'db_type' => 'mysql',
	'db_host' => 'localhost',
	'db_port' => '3306',
	'db_user' => 'sora',
	'db_pass' => 'soralive',
	'db_database' => 'soralive',
	//站点设置
	'AdminTheme' => 'Admin', //管理界面主题
	'Theme' => 'simwhite', //前台主题
);