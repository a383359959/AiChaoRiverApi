mui.plusReady(function(){
	
	$('.login input').bind('keyup',function(){
		var username = $('input[name="username"]').val();
		var password = $('input[name="password"]').val();
		if(username != '' && password != "" && password.length >= 6){
			$('.login a').addClass('submit');
		}else{
			$('.login a').removeClass('submit');
		}
	});
	
	mui(document).on('tap','.submit',function(){
		var username = $('input[name="username"]').val();
		var password = $('input[name="password"]').val();
		var option = {
			username : username,
			password : password
		};
		sendAjax('User/login',option,function(result){
			if(result.status == 'success'){
				plus.storage.setItem('store_id',result.store_id);
			}else{
				plus.nativeUI.alert(result.msg,'','提示','确定');
			}
		});
	});
	
	mui(document).on('tap','.open',function(){
		openWindow('iframe.html');
	});

});