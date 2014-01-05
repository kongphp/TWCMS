$(function() {
	loadTab();
	setTab();
});

// 通王 Ajax
window.isIE6 = window.VBArray && !window.XMLHttpRequest;
window.twAjax = {
	//加载半透明效果
	loading : function() {
		twAjax.remove();
		window.isIE6 && twAjax.unObj();
		$("body").prepend('<div class="ajaxoverlay"></div><div class="ajaxtips"><div class="ajaximg"></div></div>');
		if(window.isIE6) $(".ajaxoverlay").css({"width":document.documentElement.clientWidth, "height":document.documentElement.clientHeight});
		$(window).resize(twAjax.setTopLeft);
		twAjax.setTopLeft();
	},

	//隐藏object,select
	unObj : function() {
		$("object,select").each(function(){
			if($(this).css("visibility") != "hidden") $(this).attr("_tw_bugs_visibility_tw_", $(this).css("visibility")).css("visibility", "hidden");
		});
	},

	//显示object,select
	disObj : function() {
		$("[_tw_bugs_visibility_tw_]").each(function(){
			$(this).css("visibility", $(this).attr("_tw_bugs_visibility_tw_")).removeAttr("_tw_bugs_visibility_tw_");
		});
	},

	//删除半透明框和提示框
	remove : function() {
		document.onkeydown = null;
		window.isIE6 && twAjax.disObj();
		$(".ajaxoverlay,.ajaxtips").remove();
	},

	//关闭
	close : function() {
		$(".ajaxtips").animate({top:0}, 250, twAjax.remove);
	},

	//设置提示框位置
	setTopLeft : function(H) {
		if($(".ajaxtips").length == 0) return;
		$(".ajaxtips").css({"top":twAjax.getHeight(H), "left":twAjax.getWidth()});
	},

	getHeight : function(H) {
		if(window.isIE6) {
			return document.documentElement.scrollTop+(document.documentElement.clientHeight-$(".ajaxtips").height())/2-(typeof H == 'number' ? H : 0);
		}else{
			return ($(".ajaxoverlay").height()-$(".ajaxtips").height())/2-(typeof H == 'number' ? H : 0);
		}
	},

	getWidth : function() {
		return ($(".ajaxoverlay").width()-$(".ajaxtips").width())/2;
	},

	//设置提示框动画
	setTopAn : function(H) {
		var T = twAjax.getHeight(H);
		$(".ajaxtips").css({"top":0, "left":twAjax.getWidth()}).animate({top:T+10}, 150).animate({top:T-20}, 150).animate({top:T}, 150);
	},

	//写入对话框代码
	tipsHtml : function(str) {
		if($(".ajaxtips").length == 0) twAjax.loading();
		$(".ajaxtips").html(str);

		$(".ajaxbox").width("auto");
		var W = $(".ajaxbox").width()+5;
		$(".ajaxbox").css({"width":(W>850?850:(W<180?180:W))});
	},

	//调试程序
	debug : function(data) {
		var msg = "<div style='width:100%;overflow:auto;'><b>" + data.kp_error + "</b></div>";
		twAjax.tipsHtml('<div class="ajaxbox bfalse">'+ msg +'<u>\u6211\u77E5\u9053\u4E86</u></div>');
		twAjax.setTopAn();

		$(".ajaxtips u").click(twAjax.close);
	},

	//提示框
	alert : function(data) {
		window.twData = data = toJson(data);
		if(window.twExit) return;

		twAjax.tipsHtml('<div class="ajaxbox b'+ (data.err==0 ? true : false) +'"><i></i><b>'+ data.msg +'</b><u>\u6211\u77E5\u9053\u4E86</u></div>');
		twAjax.setTopAn();

		$(".ajaxtips u").click(function(){
			twAjax.close();
			if(!window.twName && data.name != '') $("[name='"+data.name+"']").focus();
		});
		if(!window.twErr && data.err==0) setTimeout(twAjax.close, 1000);
	},

	//确定框
	confirm : function(msg, func) {
		twAjax.tipsHtml('<div class="ajaxbox bnote"><i></i><b>'+ msg +'</b></div>');
		$(".ajaxbox").append('<p class="cf"><a id="noA" class="but3">取消</a><a id="okA" class="but3">确认</a></p>');
		twAjax.setTopAn();

		$("#noA,#okA").attr("href","javascript:;");
		$("#noA").click(twAjax.close);
		$("#okA").click(function(){ twAjax.remove(); func(); });

		document.onkeydown = function() {
			var k = e.which || e.keyCode;
			if(k == 27) {
				twAjax.close();
			}else if(k == 13) {
				twAjax.remove();
				func();
			}
		}
	},

	//提交表单
	submit : function(selector, callback) {
		$(selector).submit(function(){
			twAjax.postd($(this).attr("action"), $(this).serialize(), callback);
			return false;
		});
	},

	//提交数据(加强版，具有加载和提示框功能)
	postd : function(url, param, callback) {
		twAjax.loading();
		twAjax.post(url, param, (!callback ? twAjax.alert : callback));
	},

	//提交数据
	post : function(url, param, callback) {
		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
			data	: param,
			success	: callback,
			error	: function(html){
				alert("提交数据失败，代码:"+ html.status +"，请稍候再试");
			}
		});
	},

	//获取数据
	get : function(url, callback) {
		$.ajax({
			type	: "GET",
			cache	: true,
			url		: url,
			success	: callback,
			error	: function(html){
				alert("获取数据失败，代码:"+ html.status +"，请稍候再试");
			}
		});
	}
};

// 通王 dialog
$.twDialog = function(options) {
	if(options == "open") { $("#twdialog").show(); return false;
	}else if(options == "close") { $("#twdialog").hide(); return false;
	}else if(options == "remove") { $("#twdialog").remove(); $(window).off("resize", resize_position); return false;
	}else if($("#twdialog").length) { alert("已存在一个对话框了，不允许再创建!"); return false; }
	var objd, tval, dx, dy, sx, sy, objH, objW, bWidth, bHeight, left, top, maxLeft, maxTop, newH, newW;
	var defaults = {
		title:"标题",
		open:true,
		modal:true,
		resizable:true,
		width:600,
		height:300,
		top:"center",
		left:"center",
		zIndex:199,
		minW:300,
		minH:150,
		remove:true
	};
	var o = $.extend(defaults, options);

	//init
	$("body").append('<div id="twdialog"><div id="twdialogbox"><div id="twdialog_title"><span></span><a href="javascript:;">close</a></div><div id="twdialog_content"><div style="padding:8px">玩命加载中...</div></div><div id="twdialog_button"><input type="button" value="确定" class="but1 ok"><input type="button" value="取消" class="but1 close"></div></div></div>');

	objd = $("#twdialogbox");
	if(o.content) $("#twdialog_content").html(o.content);
	$("#twdialog_title span").html(o.title);
	if(o.open) { $("#twdialog").show(); }else { $("#twdialog").hide(); }
	if(o.modal) {
		$("#twdialog").prepend('<div id="twoverlay"></div>');
		$("#twoverlay").css({"z-index":o.zIndex-1, "width":document.documentElement.clientWidth, "height":document.documentElement.clientHeight});
	}

	//resizable
	if(o.resizable) objd.append('<div id="twdialog_resizable_n"></div><div id="twdialog_resizable_e"></div><div id="twdialog_resizable_s"></div><div id="twdialog_resizable_w"></div><div id="twdialog_resizable_nw"></div><div id="twdialog_resizable_ne"></div><div id="twdialog_resizable_sw"></div><div id="twdialog_resizable_se"></div>');

	//初始位置
	objd.css({"width":o.width, "height":o.height, "z-index":o.zIndex});
	if(o.top == "center") {objd.css("top",getTop())}else{objd.css("top",o.top)}
	if(o.left == "center") {objd.css("left",getLeft())}else{objd.css("left",o.left)}
	_setH();

	//触发拖动
	$("#twdialog_title,#twdialog_resizable_n,#twdialog_resizable_e,#twdialog_resizable_s,#twdialog_resizable_w,#twdialog_resizable_nw,#twdialog_resizable_ne,#twdialog_resizable_sw,#twdialog_resizable_se").mousedown(function(e){
		objd = $(this).parent();
		$("html,body,#twdialog").css("user-select","none");
		document.onselectstart = objd[0].onselectstart = function(){return false};
		if(!tval) tval = $(this).attr("id");
		dx=e.pageX,dy=e.pageY,sx=objd.position().left,sy=objd.position().top,objH=objd.height(),objW=objd.width(),bWidth=document.documentElement.clientWidth,bHeight=document.documentElement.clientHeight;
	});

	//关闭拖动
	$(document).mouseup(function(){
		if(objd) {
			$("html,body,#twdialog").css("user-select","auto");
			document.onselectstart = objd[0].onselectstart = function(){return true};
		}
		if(tval) tval = null;
	});

	function _setH() { $("#twdialog_content").css("height", objd.height()-$("#twdialog_title").height()-$("#twdialog_button").height()-7); }
	function _n(e) { top=e.pageY-(dy-sy); newH = dy-top+objH; if(newH>o.minH && top>=0) objd.css({"top": top, "height": newH}); _setH(); }
	function _e(e) { left=e.pageX-(dx-sx); newW=left-sx+objW; if(newW>o.minW && e.pageX<bWidth-(objW-(dx-sx-1))) objd.css({"width": newW}); }
	function _s(e) { top=e.pageY-(dy-sy); newH=top-sy+objH; if(newH>o.minH && e.pageY<bHeight-(objH-(dy-sy-1))) objd.css({"height": newH}); _setH(); }
	function _w(e) { left=e.pageX-(dx-sx); newW=objW-(left-sx); if(newW>o.minW && left>=0) objd.css({"left": left, "width": newW}); }
	function getTop(){return Math.max(0, (document.documentElement.clientHeight-objd.height())/2);}
	function getLeft(){return Math.max(0, (document.documentElement.clientWidth-objd.width())/2);}

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

	var resize_position = function() {
		var obj=$("#twdialogbox"), p=obj.position(), objW=obj.width(), objH=obj.height(), bodyW=document.documentElement.clientWidth, bodyH=document.documentElement.clientHeight;
		$("#twoverlay").css({"width":bodyW, "height":bodyH});
		if(p.left+objW+2 > bodyW) obj.css("left", Math.max(bodyW-objW-2, 0));
		if(p.top+objH+2 > bodyH) obj.css("top", Math.max(bodyH-objH-2, 0));
	}
	$(window).on("resize", resize_position);

	//关闭
	$("#twdialog_title a,#twdialog_button .close").click(function(){
		if(o.remove) { $.twDialog("remove"); }else{ $.twDialog("close"); }
	});
};

//加载JS
function twLoadJs() {
	var args = arguments;

	//循环加载JS
	var load = function(i) {
		if(typeof args[i] == 'string') {
			var file = args[i];

			// 不重复加载
			var tags = document.getElementsByTagName('script');
			for(var j=0; j<tags.length; j++) {
				if(tags[j].src.indexOf(file) != -1) {
					if(i < args.length) load(i+1);
					return;
				}
			}

			var script = document.createElement("script");
				script.type = "text/javascript";
				script.src = file;

			// callback next
			if(i < args.length) {
				// Attach handlers for all browsers
				script.onload = script.onreadystatechange = function() {
					if(!script.readyState || /loaded|complete/.test(script.readyState)) {
						// Handle memory leak in IE
						script.onload = script.onreadystatechange = null;

						// Remove the script (取消移除，判断重复加载时需要读 script 标签)
						//if(script.parentNode) { script.parentNode.removeChild(script); }

						// Dereference the script
						script = null;

						load(i+1);
					}
				};
			}
			document.getElementsByTagName('head')[0].appendChild(script);
		}else if(typeof args[i] == 'function') {
			args[i]();
			if(i < args.length) {
				load(i+1);
			}
		}
	}

	load(0);
}

//加载CSS
function twLoadCss(file) {
	// 不重复加载
	var tags = document.getElementsByTagName('link');
	for(var j=0; j<tags.length; j++) {
		if(tags[j].href.indexOf(file) != -1) {
			return false;
		}
	}

	var link = document.createElement("link");
	link.rel = "stylesheet";
	link.type = "text/css";
	link.href = file;
	document.getElementsByTagName('head')[0].appendChild(link);
}

//html转json
function toJson(data) {
	var json = {};
	try{
		json = eval("("+data+")");

		if(json.kp_error) {
			twAjax.debug(json);
			window.twExit = true;	// 用来终止程序执行
		}else{
			window.twExit = false;
		}
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
	var url = location.search;
	var i = P("#box_frame iframe[src='"+url+"']").index();
	if(i == -1) {
		i = P("#box_tab ul li[urlKey='"+urlKey+"']").index();
		if(i == -1) return;
	}

	var obj1 = P("#box_tab ul");
	var obj2 = obj1.children("li").eq(i);
	var stopNum = url.indexOf("&r=");
	var newUrl = (stopNum == -1) ? url : url.substring(0, stopNum);

	obj1.width(obj1.width()+200);
	obj2.attr({"url":newUrl, "urlKey":urlKey, "pKey":pKey, "title":title, "place":place});
	obj2.children("b").html(title);
	P("#box_place").html(place);

	parent.setUlwidth();
	setTabulAdder();
	parent.loadMenu(pKey, "select");
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
