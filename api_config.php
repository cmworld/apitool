<?php
//接口签名算法需要的  可以去掉 
define( "APP_ID" , '1000' );
define( "APP_KEY" , '3542e676b4c80983f6131cdfe577ac9b' );

$config['api_host'] = array(
	'default'=>array(
		'title' =>'开发环境',
		'host' => '127.0.0.1',
		'url' => 'http://127.0.0.1/rest'
	),
	'pro'=>array(
		'title' =>'正式环境',
		'host' => 'yourdomain.com',
		'url' => 'http://yourdomain.com/rest'
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

//系统参数
$config['system_params'] = array(
	'v' => '1.0',
    '_timestamp' => time(),
    //more params
);

$config['apis'] = array(
		'testapi'=>array(
			'title'=> '测试接口',
			'api' => 'testapi',
			'method'=>'GET',
			'params' => array(
				'param1' => array(
					'type' => 'text',
					'placeholder' => '请输入',
					'tip' =>'参数说明'
				),
				'param2' => array(
					'type' => 'text',
					'defvalue' => '默认值'
				)
			)
		),
);