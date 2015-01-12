<html class="ui-mobile"><head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>绑定教务系统帐号</title>
<link rel="stylesheet" href="./static/css/jquery.mobile-1.3.1.min.css">
</head>
<body class="ui-mobile-viewport ui-overlay-c" ryt12199="1" style="zoom: 1;">
<div data-role="page" data-url="/api/bangding.php?id=ofnHDt453nDzs7KnP9Fau50x4IK4" tabindex="0" class="ui-page ui-body-c ui-page-active" style="min-height: 343px;">
	<div class="content">
		<div data-role="content" class="ui-content" role="main">
			<div data-role="fieldcontain" class="ui-field-contain ui-body ui-br"><fieldset data-role="controlgroup" class="ui-corner-all ui-controlgroup ui-controlgroup-vertical" aria-disabled="false" data-disabled="false" data-shadow="false" data-corners="true" data-exclude-invisible="true" data-type="vertical" data-mini="false" data-init-selector=":jqmData(role='controlgroup')"><div class="ui-controlgroup-controls">
					<label for="stuid" class="ui-input-text">学号</label>
					<div ><input id="stuid" placeholder="" value="" type="text" class="ui-input-text ui-body-c"></div>
				</div></fieldset></div>
			<div data-role="fieldcontain" class="ui-field-contain ui-body ui-br"><fieldset data-role="controlgroup" class="ui-corner-all ui-controlgroup ui-controlgroup-vertical" aria-disabled="false" data-disabled="false" data-shadow="false" data-corners="true" data-exclude-invisible="true" data-type="vertical" data-mini="false" data-init-selector=":jqmData(role='controlgroup')"><div class="ui-controlgroup-controls">
					<label for="jwpwd" class="ui-input-text">密码</label>
					<div><input id="jwpwd" placeholder="" value="" type="password" class="ui-input-text ui-body-c" style="background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAASCAYAAABSO15qAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3QsPDhss3LcOZQAAAU5JREFUOMvdkzFLA0EQhd/bO7iIYmklaCUopLAQA6KNaawt9BeIgnUwLHPJRchfEBR7CyGWgiDY2SlIQBT/gDaCoGDudiy8SLwkBiwz1c7y+GZ25i0wnFEqlSZFZKGdi8iiiOR7aU32QkR2c7ncPcljAARAkgckb8IwrGf1fg/oJ8lRAHkR2VDVmOQ8AKjqY1bMHgCGYXhFchnAg6omJGcBXEZRtNoXYK2dMsaMt1qtD9/3p40x5yS9tHICYF1Vn0mOxXH8Uq/Xb389wff9PQDbQRB0t/QNOiPZ1h4B2MoO0fxnYz8dOOcOVbWhqq8kJzzPa3RAXZIkawCenHMjJN/+GiIqlcoFgKKq3pEMAMwAuCa5VK1W3SAfbAIopum+cy5KzwXn3M5AI6XVYlVt1mq1U8/zTlS1CeC9j2+6o1wuz1lrVzpWXLDWTg3pz/0CQnd2Jos49xUAAAAASUVORK5CYII=); background-attachment: scroll; cursor: auto; background-position: 100% 50%; background-repeat: no-repeat no-repeat;"></div>
				</div></fieldset></div>
			<a data-role="button" data-transition="fade" id="bind-btn" data-theme="a" data-corners="true" data-shadow="true" data-iconshadow="true" data-wrapperels="span" class="ui-btn ui-shadow ui-btn-corner-all ui-btn-up-a"><span class="ui-btn-inner"><span class="ui-btn-text">绑定</span></span></a>
		</div>
	</div>
    <br>
    <br>
    <p align='center'><font color="gray">绑定之后您可以发送“解绑”以解除绑定</font></p>
</div>
<div class="openid" id = <?php echo $_GET['openid'];?>>
</div>

<script src="./static/js/jquery-1.9.0.min.js"></script>
<script src="./static/js/jquery.mobile-1.3.1.min.js"></script> 
<script>
$('#bind-btn').on('click',function(){
	$.mobile.showPageLoadingMsg(); 
	$.ajax({
        url:'./checklogin.php',
		type:'POST',
		data:{username:$('#stuid').val(),password:$('#jwpwd').val(),openid:$('.openid').attr('id')},
		dataType:'json',
		success:function(e){
			$.mobile.hidePageLoadingMsg();
			if(e == '1'){
				alert('绑定成功，返回微信聊天窗口发送"成绩"即可查询本学期成绩！');
			}else{
				alert('输入有误！');
			}
		}, 
		error:function(e){
			$.mobile.hidePageLoadingMsg();
			alert('绑定失败,请检查网络'); 
		}
	});
});
</script>

<div class="ui-loader ui-corner-all ui-body-a ui-loader-default"><span class="ui-icon ui-icon-loading"></span><h1>loading</h1></div></body></html>