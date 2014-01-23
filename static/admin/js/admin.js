$(window).resize(windowTab);
var twRoot = 1;
$(function(){
	setNav();//导航栏
	loadMenu("my");

	//标签页
	$("#adder").click(function(){addTab()});
	$("#closeer").click(delsTab);

	$("#leftbtn").click(leftTab);
	$("#rightbtn").click(rightTab);

	$("#closeer,#adder,#leftbtn,#rightbtn").hover(function(){$(this).addClass("on");}, function(){$(this).removeAttr("class");});

	$("#ifr_refresh").click(function(){ ifrRefresh($("#box_tab ul li.on").index()); return false; });
	$("#full_screen").click(function(){$(".acp").toggleClass("fsn");return false;});
});

//刷新一个框架
function ifrRefresh(i) {
	var newUrl = $("#box_tab ul li").eq(i).attr("url") + getR();
	$("#box_frame iframe").eq(i).attr("src", newUrl);
}

//导航栏
function setNav() {
	$(".nav ul li").hover(
		function(){$(this).children("b").addClass("on");$(this).children("dl").show();}, function(){$(this).children("b").removeClass("on");$(this).children("dl").hide();}
	).click(function(){
		loadMenu($(this).attr("pKey"));
	});

	$(".nav ul li dl dd").hover(
		function(){$(this).addClass("x");}, function(){$(this).removeAttr("class");}
	).click(function(){
		oneTab($(this).attr("urlKey"));
	});
}

//判断只加载一次标签
function oneTab(urlKey, url) {
	if(!url) url = urlKey;

	var newUrlKey = "my-newtab";
	var len = $("#box_tab ul li[urlKey='"+urlKey+"']").length;

	if(len>0) {
		onTab($("#box_tab ul li[urlKey='"+urlKey+"']:first"));
	}else if($("#box_tab ul li[urlKey='"+newUrlKey+"']").length>0){
		$("#box_tab ul li.on").removeClass("on");
		var newTab = $("#box_tab ul li[urlKey='"+newUrlKey+"']:first");
		newTab.addClass("on").attr("urlKey", urlKey);
		$("#box_frame iframe:eq("+newTab.index()+")").attr("src", "index.php?u="+url);
	}else{
		addTab("\u6b63\u5728\u52a0\u8f7d", urlKey, url);
	}
}

//添加标签页
function addTab(title, urlKey, url) {
	if(!title) title = '\u65b0\u6807\u7b7e\u9875';
	if(!urlKey) urlKey = 'my-newtab';
	if(!url) url = urlKey;

	$("#box_tab ul").width($("#box_tab ul").width()+200);
	$("#box_tab ul li.on").removeClass("on");
	$("#box_tab ul").append('<li urlKey='+ urlKey +' title="'+ title +'" class="on"><b>'+ title +'</b><i></i></li>');
	$("#box_frame iframe:visible").hide();
	$("#box_frame").append('<iframe src="index.php?u='+ url + getR() +'" frameborder="0" scrolling="yes"></iframe>');

	setUlwidth();
	if($("#box_tab ul").width() > $("#box_tab").width()) {
		var valLeft = $("#box_tab").width()-$("#box_tab ul").width();
		$("#box_tab ul").animate({left: valLeft}, "fast").css("left", valLeft);
	}
	setAdder();

	loadEvent();
}

//加载左部菜单
function loadMenu(pKey, is) {
	$(".nav ul li b.x").removeAttr("class");
	$(".nav ul li[pKey='"+pKey+"'] b").addClass("x");

	$("#menutit").html($(".nav ul li[pKey='"+pKey+"'] b").html());
	$("#menu").html($(".nav ul li[pKey='"+pKey+"'] dl").html());
	$("#menu dt").remove();

	if(is == "select") {
		$("#menu dd.x").removeAttr("class");
		$("#menu dd[urlKey='"+$("#box_tab ul li.on").attr("urlKey")+"']").addClass("x");
	}else if(!$("#menu dd").is(".x")) {
		$("#menu dd:first").addClass("x");
		oneTab($("#menu dd:first").attr("urlKey"));
	}

	$("#menu dd").hover(
		function(){$(this).addClass("on");}, function(){$(this).removeClass("on");}
	).click(function(){
		$("#menu dd").removeAttr("class");
		$(this).addClass("x");
		oneTab($(this).attr("urlKey"));
	});
}

//===========================================================
//设置Ulwidth宽度
function setUlwidth() {
	var wUl=0;
	for(var j=0; j<$("#box_tab ul li").length; j++) wUl += $("#box_tab ul li:eq("+j+")").width();
	$("#box_tab ul").width(wUl).attr("W",wUl);
	plusUlwidth();
}

//宽度递归加1
function plusUlwidth() {
	while($("#box_tab ul").height() > 27) $("#box_tab ul").width($("#box_tab ul").width()+1);
}

//设置Adder位置
function setAdder(){
	if($("#box_tab ul").width() > $("#box_tab").width()) {
		$("#leftbtn:hidden,#rightbtn:hidden").show();
		if($("#adder").position().left != $("#box_tab").width()+35) $("#adder").css("left", $("#box_tab").width()+35);
	}else{
		$("#leftbtn:visible,#rightbtn:visible").hide();
		var valLeft = $("#box_tab ul li:last").width() ? $("#box_tab ul li:last").offset().left + $("#box_tab ul li:last").width() : 167;
		$("#adder").offset({left: valLeft });
	}
}
//==========================================================

//加载事件
function loadEvent() {
	$("#box_tab ul li:last").click(function(){onTab($(this))});
	$("#box_tab ul li i:last,#box_tab ul li b:last").hover(function(){$(this).addClass("on")},function(){$(this).removeAttr("class")});
	$("#box_tab ul li i:last").click(function(){
		var obj = $(this).parent();
		rmTab($("#box_tab ul li").index(obj));
	});
}

//选择标签
function onTab(obj) {
	$("#box_tab ul li.on").removeClass("on");
	obj.addClass("on");
	$("#box_place").html(obj.attr("place"));

	$("#box_frame iframe").hide();
	$("#box_frame iframe").eq($("#box_tab ul li").index(obj)).show();

	setUlwidth();
	var thisLeft = obj.offset().left;
	var endLeft = 167+$("#box_tab").width()-obj.width();
	var valLeft = (thisLeft<167) ? $("#box_tab ul").position().left+(167-thisLeft) : ((thisLeft>endLeft ? $("#box_tab ul").position().left-(thisLeft-endLeft) : "no"));
	if(valLeft != "no") $("#box_tab ul").animate({left: valLeft}, "fast").css("left", valLeft);
	setAdder();
	loadMenu(obj.attr("pKey"), "select");
}

//删除标签页
function rmTab(i) {
	var obj = $("#box_tab ul li").eq(i);
	if(obj.is(".on")) {
		var eqOn = $("#box_tab ul li").eq(i+1).html() != undefined ? i+1 : i-1;
		$("#box_tab ul li").eq(eqOn).addClass("on");
		$("#box_frame iframe").eq(eqOn).show();
	}

	obj.remove();
	$("#box_frame iframe").eq(i).remove();

	setUlwidth();
	if($("#box_tab ul").width() > $("#box_tab").width()) {
		if($("#box_tab ul li:last").offset().left < 167+$("#box_tab").width()-$("#box_tab ul li:last").width()) {
			var leftVal = $("#box_tab").width()-$("#box_tab ul").width();
			$("#box_tab ul").animate({"left": leftVal}, "fast").css("left",leftVal);
		}
	}else{
		$("#box_tab ul").animate({left: 0}, "fast").css("left",0);
	}
	setAdder();
	loadMenu($("#box_tab ul li.on").attr("pKey"), "select");
	if($("#box_tab ul li").length<1) loadMenu("my");
}

//删除其他
function delsTab(){
	$("#box_tab ul li[class!='on']").remove();
	$("#box_frame iframe:hidden").remove();

	$("#box_tab ul").css("left", 0).width("auto");
	setAdder();
}

//左移动
function leftTab() {
	var vLeft = $("#box_tab ul").position().left+($("#box_tab").width()-200);
	$("#box_tab ul").css("left", vLeft>=0 ? 0 : vLeft);

	for(var i=0; i<$("#box_tab ul li").length; i++) {
		if($("#box_tab ul li").eq(i).offset().left >= 167-$("#box_tab ul li").eq(i).width()) {
			vLeft = $("#box_tab ul").position().left+(167-$("#box_tab ul li").eq(i).offset().left);
			$("#box_tab ul").animate({left: vLeft>=0 ? 0 : vLeft}, "fast");
			break;
		}
	}
}

//右移动
function rightTab() {
	var widthMax = $("#box_tab").width();
	var ulWidth = $("#box_tab ul").width();
	var vLeft = $("#box_tab ul").position().left-(widthMax-200);
	$("#box_tab ul").css("left", vLeft<=widthMax-ulWidth ? widthMax-ulWidth : vLeft);

	for(var i=0; i<$("#box_tab ul li").length; i++) {
		if($("#box_tab ul li").eq(i).offset().left >= 167+widthMax-$("#box_tab ul li").eq(i).width()) {
			vLeft = $("#box_tab ul").position().left-($("#box_tab ul li").eq(i).offset().left-(167+widthMax-$("#box_tab ul li").eq(i).width()));
			$("#box_tab ul").animate({left: vLeft<=widthMax-ulWidth ? widthMax-ulWidth : vLeft}, "fast");
			break;
		}
	}
}

//改变窗口时设置
function windowTab() {
	if($("#box_tab ul").width() > $("#box_tab").width()) {
		$("#box_tab ul").css("left", $("#box_tab").width()-$("#box_tab ul").width());
	}else{
		$("#box_tab ul").css("left", 0);
	}
	setAdder();
}

//解决IE下缓存问题
function getR() {
	return "&r="+(new Date).getTime();
}
