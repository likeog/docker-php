$(function(){
	function checkEmpty(id,info,flag = 2){
		$id = $('#'+id);
		if( $.trim($id.val()) == '' ){
			layer.tips(info, $id ,{tips: [flag, '#F24100']} );
		 	$id.focus();
		 	return false;
		}
		return true 
	}

	function checkEmail(id)
	{
		var is_email = /^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/;
		$id = $('#'+id);
		if( !is_email.test($id.val()) ){
			layer.tips('请输入正确的邮箱格式', $id ,{tips: [2, '#F24100']} );
			$id.focus();
			return false;
		}
		return true;
	}

	function changeCaption(){
		$caption = $('.captcha');
		$caption.attr('src',$caption.attr('src')+'?'+Math.random());
	}

	function request(type,url,data,success,before,error){
		$.ajax({
	 	 	type: type,
	 	 	url:url,
		    cache:false,
		    dataType: "json", 
		    data: data,
		    success: success,
		    beforeSend: before,
		    error: error
		});
	}

	$('#auth input:eq(0)').blur(function(){
		check = checkEmail('username');
		if( check ){
			var that = $(this);
			request('GET','/getUser',that.serialize(),function(data){
				layer.closeAll();
				if( !data.status ){
					layer.tips('用户不存在', that);
					$('#login').attr('disabled',true);
				}else{
					$('#login').attr('disabled',false);
				}
			},function(){
				layer.load(2);
			});
		}
		
	});

	$('.captcha').click(changeCaption);
	
	$('#auth').submit(function(){
		var that = $(this);

		result = checkEmpty('username','用户名不能为空') &&  checkEmpty('password','密码不能为空') && checkEmpty('captcha','验证码不能为空',1);
		if( !result ) return false; 

		request('POST',that.attr('action'),that.serialize(),function(data){
			if( data.status ){
				if( data.status == 2 ){
					changeCaption();
					$('#captcha').focus();
				}
				layer.msg(data.info,function(){});
			}else{
				window.location.href = data.info;
			}
		})
		return false;
	})
});