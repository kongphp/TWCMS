/*
亲爱的朋友，你是来找bug的吗？
@功能:前台所有JS相关功能
@版本:TWCMS_2.0.0
@作者:wuzhaohuan <kongphp@gmail.com>
@时间:2013-09-16
*/

function intval(i) {
	i = parseInt(i);
	return isNaN(i) ? 0 : i;
}

function getBrowser() {
	var browser = {
			msie: false, firefox: false, opera: false, safari: false,
			chrome: false, netscape: false, appname: '未知', version: ''
		},
		userAgent = window.navigator.userAgent.toLowerCase();
	if (/(msie|firefox|opera|chrome|netscape)\D+(\d[\d.]*)/.test(userAgent)){
		browser[RegExp.$1] = true;
		browser.appname = RegExp.$1;
		browser.version = RegExp.$2;
	}else if(/version\D+(\d[\d.]*).*safari/.test(userAgent)){
		browser.safari = true;
		browser.appname = 'safari';
		browser.version = RegExp.$2;
	}
	return browser;
}

//jQuery的cookie扩展
$.cookie = function(name, value, options) {
	if(typeof value != 'undefined') {
		options = options || {};
		if(value === null) {
			value = '';
			options.expires = -1;
		}
		var expires = '';
		if(options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
			var date;
			if(typeof options.expires == 'number') {
				date = new Date();
				date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
			}else{
				date = options.expires;
			}
			expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
		}
		var path = options.path ? '; path=' + options.path : '';
		var domain = options.domain ? '; domain=' + options.domain : '';
		var secure = options.secure ? '; secure' : '';
		document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
	}else{
		var cookieValue = null;
		if(document.cookie && document.cookie != '') {
			var cookies = document.cookie.split(';');
			for(var i = 0; i < cookies.length; i++) {
				var cookie = jQuery.trim(cookies[i]);
				if(cookie.substring(0, name.length + 1) == (name + '=')) {
					cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
					break;
				}
			}
		}
		return cookieValue;
	}
};

$(function() {
	// 导航JS效果
	(function() {
		if($(".n_c dl").length < 1) return false;
		var o = $(".n_c dl.on").index();
		var p = $(".n_c dl.on").position();
		var l = !p ? 9 : p.left+9;

		if(o != -1) $(".n_hover").html($(".n_c dl:eq("+o+")").html()).css("left", l);

		$(".n_c dl").hover(function(){
			var l = $(this).position().left+9;
			$(".n_hover").stop(true).html($(this).html());
			$(".n_hover dd").fadeIn();
			$(".n_hover").animate({left: l+3},150).animate({left: l-3},150).animate({left: l},150);
		},function(){});

		$(".nav").hover(function(){
			$(".n_hover dd").fadeIn();
		}, function() {
			var s = o != -1 ? $(".n_c dl:eq("+ o +")").html() : "";
			$(".n_hover").stop(true, true).html(s);
			$(".n_hover").animate({left: l+3},150).animate({left: l-3},150).animate({left: l},150);
		});
	})();

	//搜索
	(function(){
		$("#search_form,#search_form2").submit(function(){
			var mid = $(this).find("[name='mid']").val();
			var keyword = $(this).find("[name='keyword']").val();
			window.location.href = "index.php?search-index-mid-"+mid+"-keyword-"+encodeURIComponent(keyword);
			return false;
		});
	})();

	// banner
	(function(){
		var time = 3000; // 滚动间隔时间
		var len = $(".banner ul li").length;

		if(len < 1) return false;
		var w = $(".banner").width();

		$(".banner").append('<a class="b_prev" href="javascript:;"></a><a class="b_next" href="javascript:;"></a><div class="b_push"></div>');
		$(".banner ul").width(w*len); // 设置宽度

		var b_push = $(".banner .b_push");
		for(var j=0; j<len; j++) {
			b_push.append('<a href="javascript:;"></a>');
		}
		$(".banner .b_push").css("left", (w-b_push.width())/2); // 居中

		var setPosition = function(i) {
			$(".banner ul").animate({left: -(w*i)}, 500, function(){
				$(".banner .b_push a").removeAttr("class");
				$(".banner .b_push a:eq("+i+")").addClass("on");
			});
		}
		setPosition(0);

		var run = function(type) {
			var i = $(".banner .b_push a.on").index();
			if(type > 0) {
				i = (i == 0) ? len-1 : i-1;
			}else{
				i = (i == len-1) ? 0 : i+1;
			}
			setPosition(i);
		}

		$(".banner .b_push a").click(function(){ setPosition($(this).index()); });
		$(".banner .b_prev").click(function(){ run(1); });
		$(".banner .b_next").click(function(){ run(-1); });

		var r = setInterval(function(){ run(-1); }, time);
		$(".banner").hover(function(){
			clearInterval(r);
			$(".banner .b_prev,.banner .b_next").show();
		},function(){
			r = setInterval(function(){ run(-1); }, time);
			$(".banner .b_prev,.banner .b_next").hide();
		});
	})();

	// 首页滚动图片
	(function() {
		var time = 2000; // 滚动间隔时间
		var obj = $(".piclist .p_cont ul li");
		var len = obj.length;
		if(len < 5) return false;
		var obj2 = $(".piclist .p_cont ul");
		var w = obj.outerWidth(true);
		obj2.width(w*len*2+10).append(obj2.html());

		var old_left = obj2.position().left;
		var new_left = old_left;
		var run = function (type) {
			if(type > 0) {
				if(new_left >= old_left) {
					var d_l = -(w*len);
					obj2.stop(true).css("left", d_l);
					new_left = d_l;
				}
				new_left = new_left + w;
			}else{
				if(new_left <= -(w*len)) {
					obj2.stop(true).css("left", old_left);
					new_left = old_left;
				}
				new_left = new_left - w;
			}
			obj2.animate({left: new_left+3},150).animate({left: new_left-3},150).animate({left: new_left},150);
		}

		var r = setInterval(function() { run(-1); }, time);
		$(".piclist").hover(function(){
			clearInterval(r);
		}, function() {
			r = setInterval(function() { run(-1); }, time);
		});

		$(".piclist .p_prev").click(function(){ run(1); });
		$(".piclist .p_next").click(function(){ run(-1); });
	})();

	// 发表评论
	(function(){
		if($("#ctf_content").length < 1) return false;
		var cont = $("#ctf_content");
		var author = $("#ctf_author");
		var cont_v = cont.val();
		var tw_author = $.cookie("tw_comment_author");
		if(tw_author) {
			var author_v = tw_author;
		}else{
			var author_arr = ["游客", "网友"];
			var i = parseInt(Math.random() * author_arr.length);
			var author_v = author_arr[i];
		}
		author.val(author_v);

		$(cont).focusin(function() {
			if(cont.val() == cont_v) cont.val("");
		}).focusout(function() {
			if(!cont.val()) cont.val(cont_v);
		});

		$(author).focusin(function() {
			if(author.val() == author_v) author.val("");
		}).focusout(function() {
			if(!author.val()) author.val(author_v);
		});

		// 评论提交
		window.ctf_form_one = false;
		$("#ctf_form").submit(function(){
			if(window.ctf_form_one) return false;
			window.ctf_form_one = true;
			var browser = getBrowser();
			var author_v = $("#ctf_author").val();
			$.cookie("tw_comment_author", author_v, {expires:3650}); // 写入cookie
			if(!browser.firefox) $("#ctf_submit").attr("disabled", "disabled");
			setTimeout(function(){
				if(!browser.firefox) $("#ctf_submit").removeAttr("disabled");
				window.ctf_form_one = false;
				$("#ctf_tips").html("");
			}, 2000);
			if($("#ctf_content").val() == cont_v) {
				$("#ctf_tips").html('<font color="red">请填写评论内容！</font>');
				return false;
			}
			if(!author_v) {
				$("#ctf_tips").html('<font color="red">请填写昵称！</font>');
				return false;
			}
			var _this = $(this);
			$.post(_this.attr("action"), _this.serialize(), function(data){
				try{
					var json = eval("("+data+")");
					if(json.kong_status) {
						$("#ctf_tips").html('<font color="green">'+json.message+'</font>');
						setTimeout(function(){
							var Uarr = location.href.split('#');
							location.href = Uarr[0] + "#ctf";
							location.reload();
						}, 500);
					}else{
						if(json.kp_error) {
							$("#ctf_form").after("<div style='padding:10px'>"+json.kp_error+"</div>");
						}else{
							$("#ctf_tips").html('<font color="red">'+json.message+'</font>');
						}
					}
				}catch(e){
					alert(data);
				}
			});
			return false;
		});
	})();

	// 产品内容页图组
	(function(){
		var len = $("#spec_item li").length;
		if(len < 1) return false;

		var sw = $("#spec_item").width();
		var lw = $("#spec_item li").outerWidth(true);
		$("#spec_item ul").css("width", lw*len+10);
		$("#spec_item img").removeAttr("title").removeAttr("alt");
		$("#spec_prev").addClass("dis");

		if(len <= 4) $("#spec_next").addClass("dis");

		var setPic = function(i) {
			$("#spec_item img").removeClass("on");
			$("#spec_item img").eq(i).addClass("on");
			$("#spec_pic").html('<img src="'+ $("#spec_item img").eq(i).attr("big") +'">');
		}
		setPic(0);

		$("#spec_prev").click(function() {
			if(!$(this).is(".dis")) {
				var l = $("#spec_item ul").position().left + lw;
				$("#spec_item ul").css("left", l);
				if(l == 0) $(this).addClass("dis");
				$("#spec_next").removeClass("dis");
			}
		});

		$("#spec_next").click(function() {
			var minL = sw - lw*len + 6;
			if(!$(this).is(".dis")) {
				var l = $("#spec_item ul").position().left - lw;
				$("#spec_item ul").css("left", l);
				if(l == minL) $(this).addClass("dis");
				$("#spec_prev").removeClass("dis");
			}
		});

		$("#spec_item img").mouseover(function(){ setPic($(this).parent().index()); });

		$("#spec_pic").hover(function(){
			$(this).append('<div id="spec_zoom"></div>');
			$(this).before('<div id="spec_big"><img src="'+$(this).find("img").attr("src")+'"></div>');

			var o = $(this).offset();
			var w = $(this).children("img").width();
			var h = $(this).children("img").height();
			var w2 = $("#spec_zoom").outerWidth();
			var h2 = $("#spec_zoom").outerHeight();
			var w3 = $("#spec_big").width() * (w/w2);
			var h3 = $("#spec_big").height() * (h/h2);

			$("#spec_big").css({"left": o.left + w + 12, "top": o.top});
			$("#spec_big img").css({"width": w3, "height": h3});

			$(this).on('mousemove', function(e) {
				var l = e.pageX - w2/2 - o.left;
				var t = e.pageY - h2/2 - o.top;
				l = Math.max(0, Math.min(w - w2, l));
				t = Math.max(0, Math.min(h - h2, t));
				$("#spec_zoom").css({"left": l, "top": t});

				// 小框在区域中移动的比例
				var	perX = l/w;
				var	perY = t/h;
				$("#spec_big img").css({"left": -(perX*w3), "top": -(perY*h3)});
			});
		},function() {
			$("#spec_zoom,#spec_big").remove();
			$(this).off('mousemove');
		});
	})();

	// 图集内容页图组
	(function(){
		var len = $("#main_d dl").length;
		if(len < 1) return false;

		// 补充HTML代码
		$("#main_a").html('<a class="go_l" href="javascript:;"><b></b></a><div class="pic"></div><a class="go_r" href="javascript:;"><b></b></a>');
		$("#main_c").html('<div class="small_pic"><a class="go_l" href="javascript:;"></a><div class="warp"><ul class="cf"></ul></div><a class="go_r" href="javascript:;"></a></div><div class="scroll_line"><b></b></div>');
		$("#main_d dl").each(function(i){
			$("#main_c .warp ul").append('<li><a href="javascript:;"><img src="'+ $(this).attr("small") +'" /><b>'+ (i+1) +'/'+ len +'</b></a></li>');
		});

		var liW = $(".small_pic .warp ul li").outerWidth(true);
		var warpW = $(".small_pic .warp").width();
		var maxLeft = liW*len-warpW; // 最大left
		var scrollW = $(".scroll_line b").outerWidth();
		var maxLeft2 = $(".scroll_line").width() - scrollW; // 卷轴条最大left
		var playRun; // 自动幻灯播放进程

		// 设置小图片组显示一行的宽度
		$(".small_pic .warp ul").width(liW*len+10);

		// 设置小图片组和卷轴条位置
		var setPosition = function(left) {
			var l = left - ((warpW-liW)/2);
			l = Math.max(0, Math.min(maxLeft, l));
			$(".small_pic .warp ul").stop(true).animate({"left": -l});

			var l2 = maxLeft2*(l/maxLeft);
			$(".scroll_line b").stop(true).animate({"left": l2});
		}

		// 设置显示第几张图片
		var setDisplay = function(i) {
			var vLen = $("#main_end:visible").length;
			if(i < 0 || i >= len) {
				if(vLen > 0) return false;
				var o = $("#main_a").offset();
				var l = ( ( $("#main_a").width() - $("#main_end").outerWidth() ) / 2);
				var t = ( ( $("#main_a").height() - $("#main_end").outerHeight() ) / 2);

				$("#main_end").css({"left": o.left+l}).animate({"top": o.top+t}).show();
				return false;
			}else if(vLen > 0) {
				mainEndClose();
			}

			$(".small_pic .warp ul li").removeAttr("class");
			$(".small_pic .warp ul li").eq(i).addClass("on");
			$("#main_a .pic").html('<img src="'+ $("#main_d dl").eq(i).attr("big") +'">');
			$("#main_b").html($("#main_d dl dd").eq(i).html());

			setPosition($(".small_pic .warp ul li").eq(i).position().left);

			$("#pic_save").attr("href", $("#main_a img").attr("src"));

			var uArr = location.href.split('#');
			if(i>0 || uArr[1]) window.location.href = uArr[0] + "#p=" + (i+1);

			// 预加载下一张图片，这是为减轻服务器压力和用户体验而设计
			$("#main_a .pic img")[0].onload = function() {
				var next_img = $("#main_d dl").eq(i+1).attr("big");
				if(next_img) $("#main_a").append('<img src="'+ next_img +'" style="display:none">');
			}
		}

		var uArr = location.hash.split('#p=');
		var i = Math.max(1, intval(uArr[1])) - 1;
		setDisplay(i);

		// 关闭上下图集
		var mainEndClose = function() {
			$("#main_end").animate({"top": -($("#main_end").outerHeight())}, function() { $("#main_end").hide(); });
		}
		$("#main_end .close").click(mainEndClose);

		// 重播图集
		$("#main_end .replay").click(function() {
			setDisplay(0);
		});

		// 自动幻灯播放
		$("#pic_play").click(function() {
			var _this = $(this);
			var play = _this.attr("play");

			if(!play) {
				_this.attr("play_lang", _this.html());
				_this.html(_this.attr("stop_lang"));
				playRun = setInterval(function(){
					var i = $(".small_pic .warp ul li.on").index();
					setDisplay(i+1);
					if(i+1 == len) _this.click();
				}, 5000);
				_this.attr("play", "true");
			}else{
				clearInterval(playRun);
				_this.removeAttr("play");
				_this.attr("stop_lang", _this.html());
				_this.html(_this.attr("play_lang"));
			}
		});

		// 左右键盘翻页
		document.onkeydown = function(e) {
			e = window.event || e;
			var i = $(".small_pic .warp ul li.on").index();
			e.keyCode == 37 && setDisplay(i-1);
			e.keyCode == 39 && setDisplay(i+1);
		}

		$(".small_pic .warp ul li").click(function() {
			var i = $(this).index();
			setDisplay(i);
		});

		$(".small_pic .go_l, #main_a .go_l").click(function() {
			var i = $(".small_pic .warp ul li.on").index();
			setDisplay(i-1);
		});

		$(".small_pic .go_r, #main_a .go_r").click(function() {
			var i = $(".small_pic .warp ul li.on").index();
			setDisplay(i+1);
		});

		// 鼠标拖拽卷轴条滚动小图片组位置
		$(".scroll_line b").mousedown(function(e){
			$("html,body").css("user-select","none");
			document.onselectstart = function(){return false};
			var x = e.pageX;
			var left = $(this).position().left;

			$(document).on('mousemove', function(e) {
				var l = (e.pageX - x) + left;
				l = Math.max(0, Math.min(maxLeft2, l));
				$(".scroll_line b").css("left", l);

				var l2 = maxLeft*(l/maxLeft2);
				$(".small_pic .warp ul").css("left", -l2);
			});
		});
		$(document).mouseup(function(){
			$("html,body").css("user-select","auto");
			document.onselectstart = function(){return true};
			$(document).off('mousemove');
		});
	})();

	// 右侧边栏
	(function(){
		if($(".sidebar").length < 1) return false;
		var sw = false;
		var t = $(".sidebar").offset().top;
		$(window).scroll(function() {
			if($(window).scrollTop() >= t) {
				if(!sw) {
					sw = true;
					$(".sidebar").addClass("s_fixed");
				}
			}else if(sw) {
				sw = false;
				$(".sidebar").removeClass("s_fixed");
			}
		});
	})();

	// 返回顶部
	(function(){
		var sw = false;
		$(window).scroll(function() {
			if($(window).scrollTop() >= 200) {
				if(!sw) {
					sw = true;
					$("body").append('<a id="gotop" href="javascript:;"></a>');
					$("#gotop").click(function() {
						$("html,body").animate({scrollTop: 0});
						//$(window).scrollTop(0);
					});
				}
			}else if(sw) {
				sw = false;
				$("#gotop").remove();
			}
		});
	})();

	// 跳转到评论框
	(function(){
		var Uarr = location.href.split('#');
		if(Uarr[1] == "ctf") $("html,body").animate({scrollTop: $("#ctf").offset().top});
	})();
});
