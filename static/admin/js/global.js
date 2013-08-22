$(function() {
	loadTab();
	setTab();
});

/**
 * 通王Ajax对象
 */
(function($){
//加载半透明效果
function _loading() {
	unObj();
	$("body").prepend('<div class="ajaxoverlay"></div><div class="ajaxtips"><div class="ajaximg"></div></div>');
	_setTopLeft();
}
$(window).resize(function(){ _setTopLeft(); });

//隐藏object,select
function unObj() {
	$("object,select").each(function(){
		if($(this).css("visibility") != "hidden") $(this).attr("_tw_bugs_visibility_tw_", $(this).css("visibility")).css("visibility", "hidden");
	});
}

//显示object,select
function disObj() {
	$("[_tw_bugs_visibility_tw_]").each(function(){
		$(this).css("visibility", $(this).attr("_tw_bugs_visibility_tw_")).removeAttr("_tw_bugs_visibility_tw_");
	});
}

//删除半透明框和提示框
function _remove() {
	disObj();
	$(".ajaxoverlay,.ajaxtips").remove();
}

//关闭
function _close() {
	$(".ajaxtips").animate({top:0}, 250, _remove);
}

function getHeight(H){return ($("body").height()-$(".ajaxtips").height())/2-(H ? H : 0);}
function getWidth(){return ($("body").width()-$(".ajaxtips").width())/2;}

//设置提示框位置
function _setTopLeft(H) {
	$(".ajaxtips").css({"top":getHeight(H),"left":getWidth()});
}

//设置提示框动画
function _setTopAn(H) {
	var T = getHeight(H);
	$(".ajaxtips").css({"top":0,"left":getWidth()}).animate({top:T+10}, 150).animate({top:T-20}, 150).animate({top:T}, 150);
}

//写入对话框代码
function _tipsHtml(str) {
	$(".ajaxtips").html(str);
	$(".ajaxbox .cf").hide();
	$(".ajaxbox").width("auto");
	var W = $(".ajaxbox").width()+5;
	$(".ajaxbox").css({"width":(W>850?850:(W<180?180:W))});
	$(".ajaxbox .cf").show();
	_setTopAn();
}

//确定框
function _confirm(msg, func) {
	_loading();
	_tipsHtml('<div class="ajaxbox bnote"><i></i><b>'+ msg +'</b><p class="cf"><a id="noA" class="but3">取消</a><a id="okA" class="but3">确认</a></p></div>');
	$("#noA,#okA").attr("href","javascript:;");
	$("#noA").click(_close);
	$("#okA").click(function(){ _remove();setTimeout(func,0); });
}

//调试程序
function _debug(data) {
	var msg = "<div style='width:100%;overflow:auto;'><b>" + data.kp_error + "</b></div>";
	_tipsHtml('<div class="ajaxbox bfalse">'+ msg +'<u>\u6211\u77E5\u9053\u4E86</u></div>');

	$(".ajaxtips u").click(_close);
}

//提示框
function _alert(data) {
	window.twD = toJson(data);

	if(twD.kp_error) {
		_debug(twD);
		return false;
	}

	_tipsHtml('<div class="ajaxbox b'+ (twD.err==0 ? true : false) +'"><i></i><b>'+ twD.msg +'</b><u>\u6211\u77E5\u9053\u4E86</u></div>');

	$(".ajaxtips u").click(function(){
		_close();
		if(!window.twName && twD.name != '') $("[name='"+twD.name+"']").focus();
	});
	if(!window.twErr && twD.err==0) setTimeout(_close,1000);
}

//提交表单
function _submit(selector, callback) {
	$(selector).submit(function(){
		_postd($(this).attr("action"), $(this).serialize(), callback);
		return false;
	});
}

//提交数据(加强)
function _postd(url, param, callback) {
	_loading();
	_post(url, param, (!callback ? _alert : callback));
}

//提交数据
function _post(url, param, callback) {
	$.ajax({
		type	: "POST",
		cache	: false,
		url		: url,
		data	: param,
		success	: callback,
		error	: function(html){
			alert("提交数据失败，代码:" +html.status+ "，请稍候再试");
		}
	});
}

//获取数据
function _get(url, callback) {
	$.ajax({
		type	: "GET",
		cache	: true,
		url		: url,
		success	: callback,
		error	: function(html){
			alert("获取数据失败，代码:" +html.status+ "，请稍候再试");
		}
	});
}

window.twAjax = {
	loading : _loading,
	remove : _remove,
	close : _close,
	setTopLeft : _setTopLeft,
	setTopAn : _setTopAn,
	tipsHtml : _tipsHtml,
	confirm : _confirm,
	debug : _debug,
	alert : _alert,
	submit : _submit,
	postd : _postd,
	post : _post,
	get : _get
};
})(jQuery);

/**
 * 通王dialog
 */
(function($){
$.fn.twdialog = function(options) {
	if(options == "open") { $(".twdialog,.twoverlay").show(); return false;
	}else if(options == "close") { $(".twdialog,.twoverlay").hide(); return false;
	}else if(options == "remove") { $(".twdialog,.twoverlay").remove(); return false;
	}else if($(".twdialog").length) { alert("已存在一个对话框了，不允许再创建!"); return false; }
	var objd, tval, dx, dy, sx, sy, objH, objW, bWidth, bHeight, left, top, maxLeft, maxTop, newH, newW;
	var defaults = {
		title:"标题",
		open:true,
		resizable:true,
		width:600,
		height:300,
		top:"center",
		left:"center",
		zIndex:99,
		modal:true,
		minW:300,
		minH:150
	};
	var o = $.extend(defaults, options);

	//init
	$("body").append('<div class="twdialog"><div class="twdialog_title"><span></span><a href="javascript:;">close</a></div><div class="twdialog_content"><div></div></div><div class="twdialog_button"><input type="button" value="确定" class="but1 ok"><input type="button" value="取消" class="but1 close"></div></div>');

	objd = $(".twdialog");
	$(this).replaceAll(".twdialog_content div"); //替换
	$(".twdialog_title span").html(o.title);
	if(o.modal) { $("body").append('<div class="twoverlay"></div>'); $(".twoverlay").css("z-index",o.zIndex-1); }
	if(o.open) { objd.show(); }else { $(".twoverlay").hide(); }

	//resizable
	if(o.resizable) objd.append('<div class="twdialog_resizable_n"></div><div class="twdialog_resizable_e"></div><div class="twdialog_resizable_s"></div><div class="twdialog_resizable_w"></div><div class="twdialog_resizable_nw"></div><div class="twdialog_resizable_ne"></div><div class="twdialog_resizable_sw"></div><div class="twdialog_resizable_se"></div>');

	//初始位置
	objd.css({"width":o.width, "height":o.height, "z-index":o.zIndex});
	if(o.top == "center") {objd.css("top",getHeight())}else{objd.css("top",o.top)}
	if(o.left == "center") {objd.css("left",getWidth())}else{objd.css("left",o.left)}
	_setH();

	//触发拖动
	$(".twdialog_title,.twdialog_resizable_n,.twdialog_resizable_e,.twdialog_resizable_s,.twdialog_resizable_w,.twdialog_resizable_nw,.twdialog_resizable_ne,.twdialog_resizable_sw,.twdialog_resizable_se").mousedown(function(e){
		objd = $(this).parent();
		document.onselectstart = objd[0].onselectstart = function(){return false};
		$("html,body,.twdialog").css("-moz-user-select","none");
		if(!tval) tval = $(this).attr("class");
		dx=e.pageX,dy=e.pageY,sx=objd.position().left,sy=objd.position().top,objH=objd.height(),objW=objd.width(),bWidth=$("body").width(),bHeight=$("body").height();
	});

	//关闭拖动
	$(document).mouseup(function(){
		if(objd) {
			$("html,body,.twdialog").css("-moz-user-select","-moz-all"); document.onselectstart = objd[0].onselectstart = function(){return true};
		}
		if(tval) tval = null;
	});

	function _close() { $(".twdialog,.twoverlay").hide(); }
	function _setH() { $(".twdialog_content").css("height", objd.height()-$(".twdialog_title").height()-$(".twdialog_button").height()-7); }
	function _n(e) { top=e.pageY-(dy-sy); newH = dy-top+objH; if(newH>o.minH && top>=0) objd.css({"top": top, "height": newH}); _setH(); }
	function _e(e) { left=e.pageX-(dx-sx); newW=left-sx+objW; if(newW>o.minW && e.pageX<bWidth-(objW-(dx-sx-1))) objd.css({"width": newW}); }
	function _s(e) { top=e.pageY-(dy-sy); newH=top-sy+objH; if(newH>o.minH && e.pageY<bHeight-(objH-(dy-sy-1))) objd.css({"height": newH}); _setH(); }
	function _w(e) { left=e.pageX-(dx-sx); newW=objW-(left-sx); if(newW>o.minW && left>=0) objd.css({"left": left, "width": newW}); }
	function getHeight(){return ($("body").height()-objd.height())/2;}
	function getWidth(){return ($("body").width()-objd.width())/2;}

	//获得鼠标指针在页面中的位置
	$(document).mousemove(function(e){
		switch(tval) {
			case "twdialog_title":
				left=e.pageX-(dx-sx), top=e.pageY-(dy-sy), maxLeft=bWidth-objd.width()-2, maxTop=bHeight-objd.height()-2;
				left = Math.max(0, Math.min(maxLeft, left)); top = Math.max(0, Math.min(maxTop, top)); objd.css({"left": left, "top": top});
				break;
			case "twdialog_resizable_n":
				_n(e); break;
			case "twdialog_resizable_e":
				_e(e); break;
			case "twdialog_resizable_s":
				_s(e); break;
			case "twdialog_resizable_w":
				_w(e); break;
			case "twdialog_resizable_nw":
				_n(e); _w(e); break;
			case "twdialog_resizable_ne":
				_n(e); _e(e); break;
			case "twdialog_resizable_sw":
				_s(e); _w(e); break;
			case "twdialog_resizable_se":
				_s(e); _e(e); break;
		}
	});

	$(window).resize(function() {
		var p=$(".twdialog").position(), obj=$(".twdialog"), objW=obj.width(), objH=obj.height(), bodyW=$("body").width(), bodyH=$("body").height();
		if(p.left+objW+2 > bodyW) obj.css("left", Math.max(bodyW-objW-2, 0));
		if(p.top+objH+2 > bodyH) obj.css("top", Math.max(bodyH-objH-2, 0));
	});

	//关闭显示
	$(".twdialog_title a,.twdialog_button .close").click(_close);
};
})(jQuery);

//html转json
function toJson(data) {
	var json = {};
	try{
		json = eval("("+data+")");
	}catch(e){
		alert(data);
	}
	return json;
}

function time() {
	return (new Date).getTime();
}

function P(str) {
	return parent.$(str);
}

function I(str) {
	return document.getElementById(str);
}

//设置Tabul位置 Adder位置
function setTabulAdder(){
	var tabWidth = P("#box_tab").width();
	var ulWidth = P("#box_tab ul").width();
	var ulLeft = P("#box_tab ul").position().left;

	if(ulWidth > tabWidth){
		P("#leftbtn:hidden,#rightbtn:hidden").show();
		if(P("#adder").position().left != tabWidth+35) P("#adder").css("left", tabWidth+35);

		if(ulLeft < tabWidth-ulWidth) {
			P("#box_tab ul").animate({left: tabWidth-ulWidth}, "fast");
		}else{
			var thisLeft = P("#box_tab ul li.on").offset().left;
			var endLeft = 167+tabWidth-P("#box_tab ul li.on").width();
			if(thisLeft > endLeft) P("#box_tab ul").animate({left: ulLeft-(thisLeft-endLeft)}, "fast");
		}
	}else{
		P("#leftbtn:visible,#rightbtn:visible").hide();
		P("#adder").offset({left: P("#box_tab ul li:last").offset().left + P("#box_tab ul li:last").width() });
		if(ulLeft != 0 ) P("#box_tab ul").css("left", 0);
	}
}

//父级加载Tab
function loadTab() {
	if(!parent.twRoot) return;

	var title = $("title").html();
	var stopNum = location.search.indexOf("&r=");
	var urlSearch = (stopNum == -1) ? location.search : location.search.substring(0, stopNum);
	var eq = P("#box_frame iframe[src='"+location.search+"']").index();

	P("#box_tab ul").width(P("#box_tab ul").width()+200);
	P("#box_tab ul li:eq("+eq+")").attr({"url":urlSearch, "modkey":modKey, "ackey":acKey, "title":title, "place":place});
	P("#box_tab ul li:eq("+eq+") b").html(title);
	P("#box_place").html(place);

	parent.setUlwidth();
	setTabulAdder();
	parent.loadMenu(modKey, "select");
}

//设置选项卡
function setTab() {
	$(".head dl dd").each(function(i){$(this).attr('i', i)}).click(function(){
		$(".head dl dd").removeAttr("class");
		$(this).addClass("on");
		$(".p .cc").hide();
		$(".p .cc").eq($(this).attr('i')).show();
	});
}
