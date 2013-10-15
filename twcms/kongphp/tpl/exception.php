<?php defined('KONG_PATH') || exit; ?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>出错啦！</title>
<style type="text/css">
body,div,ul,li,h1{margin:0;padding:0}
.kongcont h1,.kongcont ul,.kongcont ul li,.kongcont ul li span,.kongcont ul table tr td{font:14px/1.6 'Microsoft YaHei',Verdana,Arial,sans-serif}
.kongcont{width:98%;margin:8px auto;overflow:hidden;color:#000;border-radius:5px;box-shadow:0 0 20px #555;background:#fff;min-width:300px}
.kongcont h1{font-size:18px;height:26px;line-height:26px;padding:10px 3px 0;border-bottom:1px solid #dbdbdb;font-weight:700}
.kongcont ul,.kongcont h1{width:98%;margin:0 auto;overflow:hidden}
.kongcont ul{list-style:none;padding:3px;word-break:break-all}
.kongcont ul li,.kongcont ul table tr td{padding:0 3px}
.kongcont ul li span{float:left;display:inline;width:70px}
.kongcont ul li.even{background:#ddd}
.kongcont .fo{border-top:1px solid #dbdbdb;padding:5px 3px 10px;color:#666;text-align:right}
</style>
</head>
<body style="background:#aaa;padding:8px 0">
<div class="kongcont">
	<h1>错误信息</h1>
	<ul>
		<li><span>消息:</span> <font color="red"><?php echo $message;?></font></li>
		<li><span>文件:</span> <?php echo $file;?></li>
		<li><span>位置:</span> 第 <?php echo $line;?> 行</li>
	</ul>

	<h1>错误位置</h1>
	<ul><?php echo self::get_code($file, $line);?></ul>

	<h1>基本信息</h1>
	<ul>
		<li><span>模型目录:</span> <?php echo MODEL_PATH;?></li>
		<li><span>视图目录:</span> <?php echo VIEW_PATH.(isset($_ENV['_theme']) ? $_ENV['_theme'] : 'default').'/'; ?></li>
		<li><span>控制器:</span> <?php echo CONTROL_PATH;?><font color="red"><?php echo $_GET['control'];?>_control.class.php</font></li>
		<li><span>日志目录:</span> <?php echo RUNTIME_PATH.'logs/';?></li>
	</ul>

	<h1>程序流程</h1>
	<ul><?php echo self::arr2str(explode("\n", $tracestr), 0);?></ul>

	<h1>SQL</h1>
	<ul><?php echo self::arr2str($_ENV['_sqls'], 1, FALSE);?></ul>

	<h1>$_GET</h1>
	<ul><?php echo self::arr2str($_GET);?></ul>

	<h1>$_POST</h1>
	<ul style="white-space:pre"><?php echo print_r(_htmls($_POST), 1);?></ul>

	<h1>$_COOKIE</h1>
	<ul><?php echo self::arr2str($_COOKIE);?></ul>

	<h1>包含文件</h1>
	<ul><?php echo self::arr2str(get_included_files(), 1);?></ul>

	<h1>其他信息</h1>
	<ul>
		<li><span>请求路径:</span> <?php echo $_SERVER['REQUEST_URI'];?></li>
		<li><span>当前时间:</span> <?php echo date('Y-m-d H:i:s', $_ENV['_time']);?></li>
		<li><span>当前网协:</span> <?php echo $_ENV['_ip'];?></li>
		<li><span>运行时间:</span> <?php echo runtime();?></li>
		<li><span>内存开销:</span> <?php echo runmem();?></li>
	</ul>

	<ul class="fo">&lt;?php echo 'KongPHP, Road to Jane.'; ?&gt;</ul>
</div>
</body>
</html>
