
function parse_json(data){

    //var data = JSON && JSON.parse ? JSON.parse(data) : eval('(' + data + ')');

    try{
        var result = new JSONFormat(data);
        $("#result .inner").html(result.toString());
    }catch (e){
        $("#result .inner").html(data);
    }
}

function parse_xml(data){
    //to do
}

function parse_txt(data){
    $('#result .inner').html(data);
}

function apiCall(api,multi){

    var url = 'http://'+window.location.host+window.location.pathname;

    env = $("#env").val();
    device = $("#device").val();

    p = {'c':'do','a':api,'device':device,'env':env};
    $("input[name^=args]").each(function() {
        var name =$(this).attr("name");
        k = name.match(/args\[(.*)\]/)[1];
        p[k]=$(this).val();
    });

    var multi = multi == 'true' ? true : false;

    var ajaxSetting = {
        url: 'index.php',
        type: 'GET',
        data: p,
        dataType: 'json',
        global:false,
        beforeSend:function() {
            loading();
            if ($("#httpinfo a.tri")){
                $("#httpinfo a.tri").remove();
            }

             $('#httpinfo .inner').html('Loading ...');
             $('#httpinfo .inner').show();
        },
        success:function(data, status) {
            var header = new JSONFormat(data.header);
            
            $("#httpinfo .inner").html(header.toString());
            $("#httpinfo .inner").hide();
            $("#httpinfo h5").after('<a class="tri" href="javascript:showall();">show me</a>');

            if(data.data_type == 'json'){
                parse_json(data.response);
            }else if(data.data_type == 'xml'){
                parse_xml(data.response);
            }else{
                parse_txt(data.response);
            }
        },
        error: function(data, status, e){
            console.log(data);
            parse_txt(data.responseText);
        },
        complete:function(){
            loadingstop();
        }
    };

    if(multi){
        ajaxSetting.secureuri = false;
        ajaxSetting.fileElementId = 'fileToUpload';
        ajaxSetting.type = 'POST';

        $.ajaxFileUpload(ajaxSetting);
    }else{
        $.ajax(ajaxSetting);
    }
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
        
        $("#apiname").html(result.api);

        var multi = 'false';
        $.each(result.params, function(k, v){
            var li = $("<li><label>"+ k + "</label></li>");

            var input = $("<input />");
            input.attr("type",v.type);
            
            input.addClass('txt');

            if(v.type == 'file'){
                input.attr("id","fileToUpload");
                input.attr("name","fileToUpload");

                $("<input type='hidden' name='args[_apitool_filename]' value='"+k+"' />").appendTo(li);
                multi = 'true';
            }else{

                input.attr("name","args["+k+"]");

                if(v.placeholder && v.placeholder != ""){
                    input.attr("placeholder",v.placeholder);
                }

                if(v.required && v.required == 'true'){
                    input.attr("required","required");
                }

                if(v.defvalue && v.defvalue != ""){
                    input.val(v.defvalue);
                }
            }

            input.appendTo(li);

            if(v.tip && v.tip != ""){
                var tip = $("<p class='tip'>"+ v.tip + "</p>");
                tip.appendTo(li);
            }

            ul.append(li);
        });
        $("#extend").append(ul);

        var doaction = "<a class='btn' href='javascript:apiCall(\""+result.api+"\",\""+multi+"\");'>提交</a>";
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
    $("#api option:first").prop("selected", 'selected');
    $("#api").change(function(){
         selectApi($(this).val());
    });

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