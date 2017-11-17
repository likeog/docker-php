;(function($, undefined) {
    "use strict";

    var pluginName = 'widuu_ajax';

    $(document).on('click.'+pluginName , '[data-trigger="ajax"]', function() {
        var $this = $(this)
            ,data = $this.data()
            ,$target
            ,spinner
            ;
        if (typeof data['target'] != 'undefined') {
            $target = $("[data-target='"+data['target']+"']");
            var type  = 'GET';
            var param = {};
            if( typeof data['target'] != 'undefined' ) type = data['type'].toUpperCase();
            if( typeof data['param'] != 'undefined' )  param = eval( "(" +data['param']+ ")" );
            $.ajax({ 
                type  : type,
                url   : $this.attr('href'),
                cache : false, 
                data  : param,
                dataType : "json",
                success: function(data){
                    layer.closeAll();
                    if( !data.status ){
                        layer.msg(data.info,function(){});
                    }else{
                        layer.msg(data.info,{time:2000},function(){window.location.reload()});
                    }
                },
                beforeSend: function(){
                    layer.load(2);
                },
                error: function(data){
                    layer.closeAll();
                    layer.msg('服务器连接错误',{time:2000});
                }
            });
            return false;
        }
    });
})(jQuery);