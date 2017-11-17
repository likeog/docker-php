function success(data){
    layer.closeAll();
    if( !data.status ){
        layer.msg(data.info,function(){});
    }else{
        layer.msg(data.info,{time:2000});
    }
}
function beforeSend(){
	  layer.load(2);
}
function error(){
	  layer.closeAll();
    layer.msg('服务器连接错误',{time:2000});
}
$(function(){
  $("#certs").uploadify({
        height     : 40,
        swf        : DOCKER_PATH.JSPATH+'uploadify/uploadify.swf',
        uploader   : DOCKER_PATH.UPLOAD_PATH,
        width      : 300,
        buttonText : '上传证书压缩包',
        method     : 'POST',
        fileObjName : 'certs',
        onUploadSuccess:function(file,response){
            response = eval( "(" + response + ")" );
            layer.msg(response.info,function(){});
        }
  });
  if( $('input[name="stream_type"]:checked').val() == 3 ) $('.upload_ssl').show();
  $('input[name="stream_type"]').change(function(){
    if( $(this).val() != 3 ){
      $('input[name="is_ssl"]').val(0);
      $('.upload_ssl').hide();
    }else{
      $('.upload_ssl').show();
      $('input[name="is_ssl"]').val(1);
    }
  });
	$('#add-sticky').click(function(){
		var type   = $('input[name="stream_type"]:checked').val();
		var socket = $('input[name="remote_socket"]').val();
        var id     = $('input[name="uid"]').val();
		$.ajax({ 
            type  : 'POST',
            url   : DOCKER_PATH.PING_PATH,
            data  : {stream_type:type,remote_socket:socket,id:id},
            dataType : "json",
            success: success,
            beforeSend: beforeSend,
            error: error
        });
	});
	$('form').submit(function(){
		var that   = $(this);
		$.ajax({ 
            type  : 'POST',
            url   : DOCKER_PATH.FROM_ACTION,
            data  : that.serialize(),
            dataType : "json",
            success: success,
            beforeSend: beforeSend,
            error: error
        });
        return false;
	});
})