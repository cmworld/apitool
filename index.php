<?php
require dirname(__FILE__).'/apex/apexApi.php';
require dirname(__FILE__).'/api_config.php';

class apicall{
	public $api = null;

	public $api_arr = array();

	function __construct($appid,$appkey,$apphost,$header = array()){
		$this->api = new apexApi($appid,$appkey,$apphost,$header);
	}

	function setApiConfig($apis){
		$this->api_arr = $apis;
	}

	function setuuid($uuid,$platform){
		$this->api->set_uuid($uuid,$platform);
	}

	function getApi($api){
		return isset($this->api_arr[$api]) ? $this->api_arr[$api] : array();
	}

	function call($api,$args=array(),$returnherader = false){

		$ar = $this->getApi($api);

		$result = $this->api->api($ar['api'],$args,$ar['method']);
		$response = json_decode($result,true);
		if($returnherader){
			$res = array(
				'header' => $this->api->getHttpInfo(),
				'response' => $response ? $response : $result
			);

			return json_encode($res);
		}

		return $result;	
	}
}

$c = isset($_REQUEST['c']) ? $_REQUEST['c'] : '';
$testMode = isset($_REQUEST['testmode']) ? 1 : 0;
$method = isset($_REQUEST['a']) ? $_REQUEST['a'] : 0;

$device = isset($_REQUEST['device']) ? $_REQUEST['device'] : 'default';
$env = isset($_REQUEST['env']) ? $_REQUEST['env'] : 'default';

$env_conf = $config['api_host'][$env];
$device_conf = $config['devices'][$device];

$header = array('Host: '.$env_conf['host']);

$api = new apicall(APP_ID,APP_KEY,$env_conf['url'],$header);
$api->setuuid($device_conf['uuid'],$device_conf['platform']);
$api->setApiConfig($config['apis']);

if($c == 'device'){
	echo json_encode($device_conf);
	exit;
}elseif ($c == 'api'){
	$a = isset($_REQUEST['a']) ? $_REQUEST['a'] : '';
	$apiinfo = $api->getApi($a);

	echo json_encode($apiinfo);
	exit;
}elseif ($c == 'do'){
	header("Content-type:text/html;charset=utf-8");
	
	$a = isset($_REQUEST['a']) ? $_REQUEST['a'] : '';
	$returnheader = isset($_REQUEST['returnheader']) ? 1 : 0;

	unset($_REQUEST['a']);
	unset($_REQUEST['c']);
	unset($_REQUEST['api']);
	unset($_REQUEST['returnheader']);
	unset($_REQUEST['env']);
	unset($_REQUEST['device']);	
	$args = $_REQUEST;

	echo $api->call($a,$args,$returnheader);
	exit();
}
?>

<html>
<head>
<title>api 测试</title>
<meta content="text/html;charset=utf-8" http-equiv="content-type">
<style type="text/css">
*{ margin:0;padding: 0; list-style: none;}
a{ text-decoration: none; color: #666}
body{ padding-top: 65px}
#ajaxloading{position:absolute; top:60px; left:0px; padding: 0 20px; background:#e90; color: #fff}

#head{border-bottom: 1px solid #ddd; position: fixed; top:0; width: 100%; background: #fff} 
#head .inner{padding:15px 20px; padding-right: 50px; line-height: 27px;}
#head h3{ float: left;}
#head .env{ float: left; margin-left: 100px}
#head .nav li{ display: block; float: right; padding: 0px 15px}
#head .nav li.on{ background:#00ADEE;}
#head .nav li.on a{ color: #fff}

#col{ width: 350px;position: fixed; top:65px;}
#col ul{ padding: 10px 15px; }
#col ul li.action{ padding-left: 110px}
#col ul.client{ border-top: 1px solid #efefef; border-bottom: 1px solid #efefef;}
#col ul li{  line-height: 31px; display: block; overflow: hidden;}
#col ul li label{ display: block; float: left; width: 100px; text-align: right; padding-right: 10px}
#col ul li input.txt{ width: 100px}

.login{ float: left; width: 150px;}
.login input{ margin-bottom: 5px;  line-height: 27px; height: 27px}

#cor{ padding-left: 370px; padding-top: 15px}
#cor h5{ font-weight: normal; line-height: 27px}
#cor .inner{ padding: 5px 0; margin-bottom: 20px; border: 1px solid #efefef; padding: 10px 20px}
#action a{ margin-left: 125px; }
a.btn {
    border-radius: 0.5em;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    display: inline-block;
    font: 12px/100% Arial,Helvetica,sans-serif;
    margin: 0 2px;
    outline: medium none;
    padding: 5px 10px; 
    text-align: center;
    text-decoration: none;
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
    vertical-align: baseline;

    background: -moz-linear-gradient(center top , #00ADEE, #0078A5) repeat scroll 0 0 rgba(0, 0, 0, 0);
    border: 1px solid #0076A3;
    color: #D9EEF7;
}

a.btn:hover {
    background: -moz-linear-gradient(center top , #0095CC, #00678E) repeat scroll 0 0 rgba(0, 0, 0, 0);
    text-decoration: none;
}
a.btn:active {
    background: -moz-linear-gradient(center top , #0078A5, #00ADEE) repeat scroll 0 0 rgba(0, 0, 0, 0);
    color: #80BED6;
    position: relative;
    top: 1px;
}

a.tri{ border: 1px solid #666; padding: 2px 5px; display:inline-block; margin:5px 2px}

#device_uuid{ font-size: 9px; padding-left: 110px}

</style>
<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="js/jsonform.js"></script>
<script type="text/javascript">

	function parse_json(data){

    	var data = JSON && JSON.parse ? JSON.parse(data) : eval('(' + data + ')')
    	var header = new JSONFormat(data.header);
    	$("#httpinfo .inner").html(header.toString());
    	$("#httpinfo .inner").hide();
    	$("#httpinfo h5").after('<a class="tri" href="javascript:showall();">show me</a>');

        try{
            var result = new JSONFormat(data.response);
            $("#result .inner").html(result.toString());
        }catch (e){
        	//$("#httpinfo .inner").html('');
            $("#result .inner").html(data.response);
        }
	}

	function apiCall(api,callback){
		var url = 'http://'+window.location.host+window.location.pathname;

		env = $("#env").val();
		device = $("#device").val();

		p = {'c':'do','a':api,'returnheader':true,'device':device,'env':env}
        $("input[name^=args]").each(function() {
        	name =$(this).attr("name")
        	k=name.match(/args\[(.*)\]/)[1];
        	p[k]=$(this).val();
        });

        if (typeof(callback) == 'undefined'){
        	callback = parse_json;
        }

		var ajaxSetting = {
			url: 'index.php',
			type: 'GET',
			data: p,
			dataType: 'JSON',
			global:false,
			beforeSend:function() {
		        loading();
		        if ($("#httpinfo a.tri")){
		        	$("#httpinfo a.tri").remove();
		        }

				 $('#httpinfo .inner').html('Loading ...');
				 $('#httpinfo .inner').show();
				 $('#result .inner').html('Loading ...');
			},			
			success:function(data, status) {
			    if (!data.match("^\{(.+:.+,*){1,}\}$")){
			        $('#result .inner').html(data);
			    }else{
			    	callback(data);
			    }
			},
			error: function(data, status, e){
				$('#result .inner').html(e);
			},
			complete:function(){
				loadingstop();
			}
		}

		$.ajax(ajaxSetting);
	}

	function loadDevice(device){
		$.getJSON('index.php',{'c':'device','device':device},function(result){
			$('#device_uuid').html(result.uuid);
		});
	}

	function selectApi(api){
		$("#extend").html('');
		$("#action").html('');
		var method = $("#api").find("option:selected").attr('method');
		$("#rd_"+method.toLowerCase()).attr('checked','checked');

		$.getJSON('index.php',{'c':'api','a':api},function(result){
			var ul = $("<ul></ul>");
			$("#col").append(ul);
		    $.each(result.params, function(k, v){
				var li = "<li><label>"+ k + "</label> <input class='txt' name='args["+k+"]' value='"+v+"' /></li>";
				ul.append(li);
		    });
		    $("#extend").append(ul);

		    var doaction = "<a class='btn' href='javascript:apiCall(\""+result.api+"\");'>提交</a>";
		    $("#action").append(doaction);
		});
	}

	function showall(){
		var obj = $("#httpinfo .inner");
		if (obj.is(":hidden")){
			obj.show();
			$("a.tri").html('hidden me');
		}else{
			obj.hide();
			$("a.tri").html('show me');
		}
	}

	function loading(){
		$("#ajaxloading").show();
		$("#ajaxloading").html('loading ....');
	}

	function loadingstop(){
		$("#ajaxloading").hide();		
	}

	$(document).ready(function(){
		$('#api option:first').attr('selected','selected');
		//loadenv();
	});

</script>
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
	<!--
	<ul class="client">
		<li>
			<label>用户</label>
			<div class="login">None</div>		
		</li>
	</ul>
-->
	<ul>		
		<li>
			<label>api</label>
			<select id="api" onchange="javascript:selectApi(this.value);">
				<option value="0">选择API</option>
				<?php foreach($config['apis'] as $k=>$a):?>
				<option value="<?=$k?>" method="<?=$a['method']?>"><?=$a['title']?></option>
				<?php endforeach;?>
			</select>
		</li>
		<li>
			<label>提交方式</label><input type="radio" id="rd_post" name="method" value="post" disabled /> POST　<input type="radio" id="rd_get" name="method" value="get" disabled /> GET
		</li>
		<li>
			<label>APP ID</label><?=APP_ID?>
		</li>
		<li>	
			<label>APP KEY</label><span title="<?=APP_KEY?>">*********</span>
		</li>
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