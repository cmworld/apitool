
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
	if(api == 0){
		return;
	}

	$("#extend").html('');
	$("#action").html('');

	var method = $("#api").find("option:selected").attr('method');
	$("#rd_"+method.toLowerCase()).attr('checked','checked');

	$.getJSON('index.php',{'c':'api','a':api},function(result){
		var ul = $("<ul></ul>");
		$("#col").append(ul);
	    $.each(result.params, function(k, v){
			var li = $("<li><label>"+ k + "</label></li>");

	    	var input = $("<input class='txt' type='"+v.type+"' name='args["+k+"]' value='' />");
	    	
	    	if(v.placeholder && v.placeholder != ""){
	    		input.attr("placeholder",v.placeholder);
	    	}

	    	if(v.required && v.required == 'true'){
	    		input.attr("required","required");
	    	}

	    	if(v.defvalue && v.defvalue != ""){
	    		input.val(v.defvalue);
	    	}

	    	input.appendTo(li);

	    	if(v.tip && v.tip != ""){
	    		var tip = $("<p class='tip'>"+ v.tip + "</p>");
	    		tip.appendTo(li);
	    	}

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

	$("#sysshowbtn").click(function(){
		if($('#systemparams').hasClass('hide')){
			$('#systemparams').removeClass('hide');
			$(this).text('收起系统参数');
		}else{
			$('#systemparams').addClass('hide');
			$(this).text('展开系统参数');
		}
	});
});