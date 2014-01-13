<!doctype html>
<head>
<title>通王CMS 安装向导</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<link href="img/style.css"rel="stylesheet" type="text/css"  />
</head>

<body scroll="no">
<div class="b">
	<div class="main">
		<div class="head">
			<div class="h_right"><a href="http://www.twcms.com" target="_blank">官方网站</a><span>|</span><a href="http://bbs.twcms.com" target="_blank">交流论坛</a></div>
			<img src="img/logo.gif" />
		</div>
		<div class="cont">
			<div class="c_top"></div>
			<div class="c_c">
				<div class="c_c_left">
					<ul>
						<li class="<?php echo $do=='license'?'on':(in_array($do,array('check_env','check_db','complete'))?'ok':''); ?>">1、阅读协议</li>
						<li class="<?php echo $do=='check_env'?'on':(in_array($do,array('check_db','complete'))?'ok':''); ?>">2、环境检测</li>
						<li class="<?php echo $do=='check_db'?'on':(in_array($do,array('complete'))?'ok':''); ?>">3、参数配置</li>
						<li class="<?php echo $do=='complete'?'on':''; ?>">4、安装结束</li>
					</ul>
				</div>
				<div class="c_c_right">

