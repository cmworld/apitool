apitool
---------------------------------------

给客户端的同学们方便测试rest接口的工具

注意：默认请求接口有签名算法的，如果自己的接口没有签名算法，可以不理睬 API_ID  和 APP_KEY  2个常量


---------------------------------------
api_config.php
---------------------------------------
-可以配置不同服务器环境
```
$config['api_host'] = array(
    'default'=>array(
        'title' =>'开发环境',
        'host' => '127.0.0.1',
        'url' => 'http://127.0.0.1/rest'
    ),
    'pro'=>array(
        'title' =>'正式环境',
        'host' => 'xx.com/rest',
        'url' => 'http://xx.com/rest'
    )
);
```

-可以模拟多种请求设备 ，如果有项目需要额外手机参数 可以自己添加扩展
```
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
```
-签名设置
-系统参数
$config['system_params'] = array(
    'v' => '1.0',
    '_timestamp' => time(),
    //more params
);

-接口地址
```
$config['apis'] = array(
    'test_api'=>array(
        'title'=> '例子接口',
        'api' => 'testapi',
        'method'=>'GET',     //post              
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
```
