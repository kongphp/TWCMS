<?php defined('KONG_PATH') || exit; ?>
<style type="text/css">
#kong_trace_win{display:none;z-index:999;position:fixed;left:1%;bottom:10px;width:98%;min-width:300px;border-radius:5px;box-shadow:-2px 2px 20px #555;background:#fff;border:1px solid #ccc}
#kong_trace_win,#kong_trace_win,#kong_trace_win div,#kong_trace_win h6,#kong_trace_win ol,#kong_trace_win li{margin:0;padding:0;font:14px/1.6 'Microsoft YaHei',Verdana,Arial,sans-serif}
#kong_trace_open{display:none;z-index:999;position:fixed;right:5px;bottom:5px;width:80px;height:24px;line-height:24px;text-align:center;border:1px solid #ccc;border-radius:5px;background:#eee;cursor:pointer;box-shadow:0 0 12px #555}
#kong_trace_size,#kong_trace_close{float:right;display:inline;margin:3px 5px 0 0!important;border:1px solid #ccc;border-radius:5px;background:#eee;width:24px;height:24px;line-height:24px;text-align:center;cursor:pointer}
#kong_trace_title{height:32px;overflow:hidden;padding:0 3px;border-bottom:1px solid #ccc}
#kong_trace_title h6{float:left;display:inline;width:100px;height:32px;line-height:32px;font-size:16px;font-weight:700;text-align:center;color:#999;cursor:pointer;text-shadow:1px 1px 0 #F2F2F2}
#kong_trace_cont{width:100%;height:240px;overflow:auto}
#kong_trace_cont ol{list-style:none;padding:5px;overflow:hidden;word-break:break-all}
#kong_trace_cont ol.ktun{display:none}
#kong_trace_cont ol li{padding:0 3px}
#kong_trace_cont ol li span{float:left;display:inline;width:70px}
#kong_trace_cont ol li.even{background:#ddd}
</style>
<div id="kong_trace_open"><?php echo $runtime = runtime();?></div>
<div id="kong_trace_win">
	<div id="kong_trace_title">
		<div id="kong_trace_close">关</div>
		<div id="kong_trace_size">大</div>
		<h6 style="color:#000">基本信息</h6>
		<h6>SQL</h6>
		<h6>$_GET</h6>
		<h6>$_POST</h6>
		<h6>$_COOKIE</h6>
		<h6>包含文件</h6>
		<h6>自动加载</h6>
	</div>
	<div id="kong_trace_cont">
		<ol>
			<li><span>模型:</span> <?php echo MODEL_PATH;?></li>
			<li><span>视图:</span> <?php echo VIEW_PATH.(isset($_ENV['_theme']) ? $_ENV['_theme'] : 'default').'/'; if(isset($_ENV['_tplname'])) { echo '<font color="red">'.$_ENV['_tplname'].'</font>'; } ?></li>
			<li><span>控制器:</span> <?php echo CONTROL_PATH;?><font color="red"><?php echo $_GET['control'];?>_control.class.php</font></li>
			<li><span>日志目录:</span> <?php echo RUNTIME_PATH.'logs/';?></li>
			<li><span>当前页面:</span> <?php echo $_SERVER['SCRIPT_FILENAME'];?></li>
			<li><span>当前时间:</span> <?php echo date('Y-m-d H:i:s', $_ENV['_time']);?></li>
			<li><span>当前网协:</span> <?php echo $_ENV['_ip'];?></li>
			<li><span>请求路径:</span> <?php echo $_SERVER['REQUEST_URI'];?></li>
			<li><span>运行时间:</span> <?php echo $runtime;?></li>
			<li><span>内存开销:</span> <?php echo runmem();?></li>
		</ol>
		<ol class="ktun"><?php echo self::arr2str($_ENV['_sqls'], 1, FALSE);?></ol>
		<ol class="ktun"><?php echo self::arr2str($_GET);?></ol>
		<ol class="ktun" style="white-space:pre"><?php echo print_r(_htmls($_POST), 1);?></ol>
		<ol class="ktun"><?php echo self::arr2str($_COOKIE);?></ol>
		<ol class="ktun"><?php echo self::arr2str(get_included_files(), 1);?></ol>
		<ol class="ktun"><?php echo self::arr2str($_ENV['_include'], 1);?></ol>
	</div>
</div>
<script type="text/javascript">
(function(){
var isIE = !!window.ActiveXObject;
var isIE6 = window.VBArray && !window.XMLHttpRequest;
var isQuirks = document.compatMode == 'BackCompat';
var isDisable = (isIE && isQuirks) || isIE6;
var win = document.getElementById('kong_trace_win');
var size = document.getElementById('kong_trace_size');
var open = document.getElementById('kong_trace_open');
var close = document.getElementById('kong_trace_close');
var cont = document.getElementById('kong_trace_cont');
var tab_tit = document.getElementById('kong_trace_title').getElementsByTagName('h6');
var tab_cont = document.getElementById('kong_trace_cont').getElementsByTagName('ol');
var cookie = document.cookie.match(/kongphp_trace_page_show=(\d\|\d\|\d)/);
var history = (cookie && typeof cookie[1] != 'undefined' && cookie[1].split('|')) || [0,0,0];
var is_size = 0;
var set_cookie = function() {
	document.cookie = 'kongphp_trace_page_show=' + history.join('|');
}
open.onclick = function() {
	win.style.display='block';
	this.style.display='none';
	history[0] = 1;
	set_cookie();
}
close.onclick = function() {
	win.style.display = 'none';
	open.style.display = 'block';
	history[0] = 0;
	set_cookie();
}
size.onclick = function() {
	if(is_size == 0) {
		this.innerHTML = "小";
		//win.style.top = "10px";
		var H = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight;
		H = H > window.screen.availHeight ? window.screen.availHeight - 200 : H;
		H = H < 350 ? 350 : H;
		cont.style.height = H - 63 +"px";
		is_size = 1;
		history[1] = 1;
	}else{
		this.innerHTML = "大";
		//win.style.top = "auto";
		cont.style.height = "240px";
		is_size = 0;
		history[1] = 0;
	}
	set_cookie();
}
for(var i = 0; i < tab_tit.length; i++) {
	tab_tit[i].onclick = (function(i) {
		return function() {
			for(var j = 0; j < tab_cont.length; j++) {
				tab_cont[j].style.display = 'none';
				tab_tit[j].style.color = '#999';
			}
			tab_cont[i].style.display = 'block';
			tab_tit[i].style.color = '#000';
			history[2] = i;
			set_cookie();
		};
	})(i);
}
if(!isDisable) {
	open.style.display = 'block';

	if(typeof open.click == 'function') {
		parseInt(history[0]) && open.click();
		parseInt(history[1]) && size.click();
		tab_tit[history[2]].click();
	}else{
		parseInt(history[0]) && open.onclick();
		parseInt(history[1]) && size.onclick();
		tab_tit[history[2]].onclick();
	}
}
})();
</script>
