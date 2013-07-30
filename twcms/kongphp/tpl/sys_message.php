<?php defined('KONG_PATH') || exit; ?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>操作提示！</title>
<style>
body,div,ul,li,p,h1{margin:0;padding:0;font:14px/1.8 'Microsoft YaHei',Verdana,Arial,sans-serif}
body{background:#aaa;color:#000}
a,a:active{text-decoration:none;color:#333}
a:hover{color:#f30}
ul{list-style:none}
.kongcont{width:65%;margin:150px auto 0;overflow:hidden;border-radius:5px;box-shadow:5px 5px 12px #555;background:#fff;min-width:300px}
.kongcont h1{font-size:18px;height:26px;line-height:26px;padding:10px 3px 0;border-bottom:1px solid #dbdbdb;font-weight:700}
.kongcont .c1,.kongcont h1,.footer{width:95%;margin:auto;overflow:hidden}
.kongcont .c1{word-break:break-all;padding:3px}
.kongcont .c1 li,table tr td{padding:0 3px}
.kongcont .c1 li.even{background:#ddd}
.footer{border-top:1px solid #dbdbdb;padding:5px 3px 10px;color:#666;text-align:right}
</style>
</head>
<body>
<div class="kongcont">
	<h1><?php echo $status ? '成功' : '失败';?>啦！</h1>
	<div class="c1">
		<p><b style="font-size:16px;color:<?php echo $status ? 'green' : 'red';?>"><?php echo $message;?></b></p>
		<p id="jump"></p>
		<p><a href="javascript:jumpurl();">立即跳转</a></p>
	</div>

	<div class="footer">&lt;?php echo 'KongPHP, Road to Jane.'; ?&gt;</div>
</div>
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
	jump.innerHTML = (time--) + '秒后自动跳转' + dot;
	if(time == -1) {
		clearInterval(t);
		jumpurl();
	}
}
display();
t = setInterval(display, 1000);
</script>
</body>
</html>