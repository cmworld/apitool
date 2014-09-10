<?php
require dirname(__FILE__).'/apex/apiCall.php';
require dirname(__FILE__).'/api_config.php';

$c = isset($_REQUEST['c']) ? $_REQUEST['c'] : '';
$testMode = isset($_REQUEST['testmode']) ? 1 : 0;
$method = isset($_REQUEST['a']) ? $_REQUEST['a'] : 0;

$device = isset($_REQUEST['device']) ? $_REQUEST['device'] : array_keys($config['devices'])[0];
$env = isset($_REQUEST['env']) ? $_REQUEST['env'] : array_keys($config['api_host'])[0];

$env_conf = $config['api_host'][$env];
$device_conf = $config['devices'][$device];

$api = new apicall();
$api->apiurl = $env_conf['url'];
$api->apihost = $env_conf['host'];
$api->conf = $config;

if($c == 'device'){
	echo json_encode($device_conf);
	exit;
}elseif ($c == 'api'){
	$a = isset($_REQUEST['a']) ? $_REQUEST['a'] : '';
	$apiinfo = $api->getApi($a);

	echo json_encode($apiinfo);
	exit;
}elseif ($c == 'do'){

	$a = isset($_REQUEST['a']) ? $_REQUEST['a'] : '';

	unset($_REQUEST['a']);
	unset($_REQUEST['c']);
	unset($_REQUEST['api']);
	unset($_REQUEST['env']);
	unset($_REQUEST['device']);	
	$args = $_REQUEST;

	if(isset($args['_apitool_filename']) && isset($_FILES["fileToUpload"])){
		if($_FILES["fileToUpload"]['error'] > 0){
			switch($_FILES["fileToUpload"]['error']){
				case '1':
					$error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
					break;
				case '2':
					$error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
					break;
				case '3':
					$error = 'The uploaded file was only partially uploaded';
					break;
				case '4':
					$error = 'No file was uploaded.';
					break;

				case '6':
					$error = 'Missing a temporary folder';
					break;
				case '7':
					$error = 'Failed to write file to disk';
					break;
				case '8':
					$error = 'File upload stopped by extension';
					break;
				case '999':
				default:
					$error = 'No error code avaiable';
			}

			header('HTTP/1.1 500 Internal Server Error'); 
			exit("ApiTool Report : ".$error);
		}

		if (!@is_uploaded_file($_FILES["fileToUpload"]["tmp_name"])) {
			header('HTTP/1.1 500 Internal Server Error'); 
			exit("ApiTool Report : Upload Failed.");
	    }

	    $target = dirname(__FILE__)."/tmp/".basename($_FILES['fileToUpload']['name']);
		if(@move_uploaded_file($_FILES['fileToUpload']['tmp_name'],$target)){
			$args[$args['_apitool_filename']] = '@'.$target;
			unset($args['_apitool_filename']);
		}else{
			header('HTTP/1.1 500 Internal Server Error'); 
			exit("ApiTool Report : Failed to move uploaded file to ".$target);
		}
	}

	$args = array_merge(
		$args,
		array('uuid'  => $device_conf['uuid'],'platform' => $device_conf['platform'])
	);

	header("Content-type: application/json");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Pragma: no-cache");

	$res = $api->call($a,$args);
	echo json_encode($res);
	exit();
}
?>

<html>
<head>
<title>api 测试</title>
<meta content="text/html;charset=utf-8" http-equiv="content-type">
<link rel="stylesheet" type="text/css" href="./style.css">
<script type="text/javascript" src="js/jquery-1.9.1.js"></script>
<script type="text/javascript" src="js/jsonform.js"></script>
<script type="text/javascript" src="js/common.js"></script>
<script type="text/javascript" src="js/ajaxfileupload.js"></script>
</head>
<body>
<div id="ajaxloading" style="display:none"></div>
<div id="head">
	<div class="inner">
		<h3>Api Tool</h3>
		<div class="env">
			环境 
			<select id="env">
				<?php foreach ($config['api_host'] as $k=>$v):?>
					<option value="<?=$k?>" <?php if($env == $k):?>selected="true"<?php endif;?>><?=$v['title']?></option>
				<?php endforeach;?>
			</select>		
		</div>
		<div class="nav">
			<ul>				
				<li class="on"><a href='index.php'>api</a></li>
			</ul>
		</div>	
		<div style="clear:both"></div>
	</div>
</div>
<div id="col">
	<ul>
		<li>
			<label>模拟设备 </label>
			<select id="device" onchange="javascript:loadDevice(this.value)">
				<?php foreach ($config['devices'] as $k=>$v):?>
					<option value="<?=$k?>" <?php if($device == $k):?>selected="true"<?php endif;?>><?=$v['title']?></option>
				<?php endforeach;?>
			</select>
			<p id="device_uuid"><?=$device_conf['uuid']?></p>
		</li>
	</ul>
	
	<ul>		
		<li>
			<label>接口名称</label>
			<select id="api">
				<option value="0" selected="selected">选择API</option>
				<?php foreach($config['apis'] as $k=>$a):?>
				<option value="<?=$k?>" method="<?=$a['method']?>"><?=$a['title']?></option>
				<?php endforeach;?>
			</select>
		</li>
		<li>
			<label>api</label> <span id="apiname">--</span>
		</li>
		<li>
			<label>提交方式</label><input type="radio" id="rd_post" name="method" value="post" disabled /> POST　<input type="radio" id="rd_get" name="method" value="get" disabled /> GET
		</li>
	</ul>
	<ul id="systemparams">
		<?php foreach($config['system_params'] as $k => $v){ ?>
		<li class="p">
			<label><?=$k?></label>
			<input class='txt' type='text' name='args[<?=$k?>]' value='<?=$v?>' />
		</li>
		<?php }?>
		<li><p class="tip"><a href="#" id="sysshowbtn">收起系统参数</a></p></li>
	</ul>
	<div id="extend">
	</div>	
	<div id="action">
	</div>
</div>
<div id="cor">
	<div id="httpinfo">
		<h5>请求头信息</h5>
		<div class="inner"></div>
	</div>
	<div id="result">
		<h5>返回结果</h5>
		<div class="inner"></div>
	</div>
</div>
</body>
</html>