<?php defined('KONG_PATH') || exit; ?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>操作提示！</title>
<style type="text/css">
body,div,ul,li,h1{margin:0;padding:0}
.kongcont h1,.kongcont ul,.kongcont ul li,.kongcont ul li span{font:14px/1.6 'Microsoft YaHei',Verdana,Arial,sans-serif}
.kongcont{width:65%;margin:150px auto 0;overflow:hidden;color:#000;border-radius:5px;box-shadow:0 0 20px #555;background:#fff;min-width:300px}
.kongcont h1{font-size:18px;height:26px;line-height:26px;padding:10px 3px 0;border-bottom:1px solid #dbdbdb;font-weight:700}
.kongcont ul,.kongcont h1{width:95%;margin:0 auto;overflow:hidden}
.kongcont ul{list-style:none;padding:3px;word-break:break-all}
.kongcont ul li{padding:0 3px}
.kongcont .fo{border-top:1px solid #dbdbdb;padding:5px 3px 10px;color:#666;text-align:right}
</style>
</head>
<body style="background:#aaa">
<div class="kongcont">
	<h1><?php echo $status ? '成功' : '出错';?>啦！</h1>
	<ul>
		<li><b style="font-size:16px;color:<?php echo $status ? 'green' : 'red';?>"><?php echo $message;?></b></li>
		<li id="jump"></li>
	</ul>

	<ul class="fo">&lt;?php echo 'KongPHP, Road to Jane.'; ?&gt;</ul>
</div>
<?php if($jumpurl != -1) { ?>
<script type="text/javascript">
var dot = '', t;
var jump = document.getElementById("jump");
var time = <?php echo $delay;?>;
function jumpurl(){
	<?php echo $jumpurl == 'history.back()' ? 'history.back()' : 'location.href = "'.$jumpurl.'"';?>;
}
function display(){
	dot += '.';
	if(dot.length > 6) dot = '.';
	jump.innerHTML = (time--) + '秒后自动跳转' + dot + '<br><a href="javascript:jumpurl();">立即跳转</a>';
	if(time == -1) {
		clearInterval(t);
		jumpurl();
	}
}
display();
t = setInterval(display, 1000);
</script>
<?php } ?>
</body>
</html>
