<?php
//接口签名算法需要的  可以去掉 
define( "APP_ID" , '1000' );
define( "APP_KEY" , '3542e676b4c80983f6131cdfe577ac9b' );

$config['api_host'] = array(
	'default'=>array(
		'title' =>'开发环境',
		'host' => '127.0.0.1',
		'url' => 'http://rest.tyqiu.com'
	),
	'pro'=>array(
		'title' =>'正式环境',
		'host' => 'xxx.xxx.xx.xxx',
		'url' => 'http://xx.com'
	)
);

$config['devices'] = array(
	'default'=>array(
		'title' =>'iphone 1',  
		'uuid' => 'dcc01b481a7245325eacac170d4da0b5200b7d6b7',
		'platform' => 'iphone'
	),
	'and1'=>array(
		'title' =>'android 1',
		'uuid' => '357070051856062',
		'platform' => 'android'
	)
);

$config['apis'] = array(
		'system.setting.preload'=>array(
			'title'=> '设备初始化',
			'api' => 'system.setting.preload',
			'method'=>'GET',
			'params' => array(
				'param1' => 'default value',
				'param2' => ''
			)
		),
);