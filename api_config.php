<?php

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

//调用api的参数名
$config['api_name'] = 'r';

//签名算法
$config['use_sign'] = true;

//签名参数
$config['sign_name'] = 'sign';

//签名私钥
$config['sign_key'] = '3542e676b4c80983f6131cdfe577ac9b';

//系统参数
$config['system_params'] = array(
	'v' => '1.0',
    '_timestamp' => time(),
    //more params
);

/*
	'params' => array(
		'参数名' => array(
					'type' => 输入文本框input类型,
					'defvalue' => '默认值',
					'placeholder' => 文本框提示
					'required' => true //是否必须
					'tip' => 字段说明
				)
		....
	)
*/

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
		'testapi2'=>array(
			'title'=> '测试接口2',
			'api' => 'testapi2',
			'method'=>'POST',
			'params' => array(
				'param1' => array(
					'type' => 'text',
					'placeholder' => '请输入',
					'tip' =>'参数说明'
				),
				'param2' => array(
					'type' => 'file'
				)
			)
		),
);