mui.plusReady(function(){
	
	var webview = plus.webview.currentWebview();
	
	var option = {
		top : '50px',
		bottom : '0px',
		position : 'dock',
		dock : 'bottom',
		bounce : 'vertical'
	};
	
	var embed = plus.webview.create('http://api.smdouyou.com/index.php/StoreApi/','embed',option);
	
	webview.append(embed);
	
	embed.addEventListener('loaded',function(){
		$('header span').text(embed.getTitle());
		plus.nativeUI.closeWaiting();
	},false);
	
	embed.addEventListener('loading',function(){
		plus.nativeUI.showWaiting();
	},false);
	
});