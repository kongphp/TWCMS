<?php defined('KONG_PATH') || exit; ?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>出错啦！</title>
<style>
body,div,ul,li,p,h1{margin:0;padding:0;font:14px/1.6 'Microsoft YaHei',Verdana,Arial,sans-serif}
body{background:#aaa;color:#000}
ul{list-style:none}
.kongcont{width:98%;margin:15px auto;overflow:hidden;border-radius:5px;box-shadow:5px 5px 12px #555;background:#fff;min-width:300px}
.kongcont h1{font-size:16px;height:26px;line-height:26px;padding:10px 3px 0;border-bottom:1px solid #dbdbdb;font-weight:700}
.kongcont .c1,.kongcont h1,.footer{width:98%;margin:auto;overflow:hidden}
.kongcont .c1{word-break:break-all;padding:3px}
.kongcont .c1 li,table tr td{padding:0 3px}
.kongcont .c1 li span{float:left;display:inline;width:70px}
.kongcont .c1 li.even{background:#ddd}
.footer{border-top:1px solid #dbdbdb;padding:5px 3px 10px;color:#666;text-align:right}
</style>
</head>
<body>
<div class="kongcont">
	<h1>错误信息</h1>
	<div class="c1">
		<p><span>消息:</span> <font color="red"><?php echo $message;?></font></p>
		<p><span>文件:</span> <?php echo $file;?></p>
		<p><span>位置:</span> 第 <?php echo $line;?> 行</p>
	</div>

	<h1>错误位置</h1>
	<div class="c1"><?php echo self::get_code($file, $line);?></div>

	<h1>基本信息</h1>
	<ul class="c1">
		<li><span>模型目录:</span> <?php echo MODEL_PATH;?></li>
		<li><span>视图目录:</span> <?php echo VIEW_PATH.(isset($_SERVER['_setting'][APP_NAME.'_theme']) ? $_SERVER['_setting'][APP_NAME.'_theme'] : 'default').'/'; ?></li>
		<li><span>控制器:</span> <?php echo CONTROL_PATH;?><font color="red"><?php echo $_GET['control'];?>_control.class.php</font></li>
		<li><span>日志目录:</span> <?php echo RUNTIME_PATH.'logs/';?></li>
	</ul>

	<h1>程序流程</h1>
	<ul class="c1"><?php echo self::arr2str(explode("\n", $tracestr), 0);?></ul>

	<h1>SQL</h1>
	<ul class="c1"><?php echo self::arr2str($_SERVER['_sqls'], 1, FALSE);?></ul>

	<h1>$_GET</h1>
	<ul class="c1"><?php echo self::arr2str($_GET);?></ul>

	<h1>$_POST</h1>
	<ul class="c1" style="white-space:pre"><?php echo print_r(_htmls($_POST), 1);?></ul>

	<h1>$_COOKIE</h1>
	<ul class="c1"><?php echo self::arr2str($_COOKIE);?></ul>

	<h1>包含文件</h1>
	<ul class="c1"><?php echo self::arr2str(get_included_files(), 1);?></ul>

	<h1>其他信息</h1>
	<ul class="c1">
		<li><span>请求路径:</span> <?php echo $_SERVER['REQUEST_URI'];?></li>
		<li><span>当前时间:</span> <?php echo date('Y-m-d H:i:s', $_SERVER['_time']);?></li>
		<li><span>当前网协:</span> <?php echo $_SERVER['_ip'];?></li>
		<li><span>运行时间:</span> <?php echo runtime();?></li>
		<li><span>内存开销:</span> <?php echo runmem();?></li>
	</ul>

	<div class="footer">&lt;?php echo 'KongPHP, Road to Jane.'; ?&gt;</div>
</div>
</body>
</html>
