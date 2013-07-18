<?php defined('KONG_PATH') || exit; ?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>致命错误！</title>
<style>
body,div,ul,li,p,h1{margin:0;padding:0;font:14px/1.8 'Microsoft YaHei',Verdana,Arial,sans-serif}
body{background:#aaa;color:#000}
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
    <h1>错误信息</h1>
    <div class="c1">
        <p><span>消息:</span> <font color="red"><?php echo $message;?></font></p>
        <p><span>文件:</span> <?php echo $file;?></p>
        <p><span>位置:</span> 第 <?php echo $line;?> 行</p>
    </div>

    <div class="footer">&lt;?php echo 'KongPHP, Road to Jane.'; ?&gt;</div>
</div>
</body>
</html>