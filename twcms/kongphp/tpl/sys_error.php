<?php defined('KONG_PATH') || exit; ?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>错误啦！</title>
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
	<h1>错误信息</h1>
	<ul>
		<li><span>消息:</span> <font color="red"><?php echo $message;?></font></li>
		<li><span>文件:</span> <?php echo $file;?></li>
		<li><span>位置:</span> 第 <?php echo $line;?> 行</li>
	</ul>

	<ul class="fo">&lt;?php echo 'KongPHP, Road to Jane.'; ?&gt;</ul>
</div>
</body>
</html>
