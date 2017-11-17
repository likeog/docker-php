$(function(){
  function success(data){
    layer.closeAll();
    if( !data.status ){
      layer.msg(data.info,function(){});
    }else{
      window.location.href=data.info;
    }
  }
  function beforeSend(){
      layer.load(2);
  }
  function error(){
      layer.closeAll();
      layer.msg('服务器连接错误',{time:2000});
  }
  $('form').submit(function(){
    var that   = $(this);
    $.ajax({ 
      type  : 'POST',
      url   : that.attr('action'),
      data  : that.serialize(),
      dataType : "json",
      success: success,
      beforeSend: beforeSend,
      error: error
    });
    return false;
  });
})