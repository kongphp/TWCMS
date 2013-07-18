(function(){
var cid, mid, isIndex, timeout, editor, editMid, editIsIndex;

//初始化
function _init(_cid, _mid, _isIndex) {
	if(!_cid) {
		cid = null;
		mid = 3;
		isIndex = 0;
	}else{
		cid = _cid;
		mid = _mid;
		isIndex = _isIndex;
	}
	I("formcate").reset();	//清空文本框
	if(editor && editor.getContent()) editor.setContent("");
	setAll();
}

//设置表单全部参数
function setAll() {
	if(mid==1) {	//外部链接
		$("#cadd tr:eq(2)").fadeOut();
		$("#cadd tbody tr:not(:last)").hide();
		$("#cadd tr[midval=2]").hide();

		$("input[name='dir']").parent().prev("th").html("外部URL");
	}else if(mid==2) {	//单页
		$("#cadd tr:not(:eq(2))").show();
		$("#cadd tr:eq(2)").fadeOut();
		$("input[name='show_tpl']").parent().parent().hide();
		$("input[name='show_url']").parent().parent().hide();
		$("#cadd tr[midval=2]").show();

		$("input[name='dir']").parent().prev("th").html("英文目录");
		$("input[name='cate_tpl']").parent().prev("th").html("单页模板");
		$("input[name='cate_url']").parent().prev("th").html("单页URL规则");
		ajaxSetTplOrUrl("page");
		getE();
	}else{	//核心模型
		$("#cadd tr").show();
		$("#cadd tr[midval=2]").hide();

		$("input[name='dir']").parent().prev("th").html("英文目录");
		setIsIndex();
	}
}

//设置 频道||非频道 的参数
function setIsIndex() {
	if(isIndex==1) {
		$("input[name='show_tpl']").parent().parent().hide();
		$("input[name='show_url']").parent().parent().hide();

		$("input[name='cate_tpl']").parent().prev("th").html("频道模板");
		$("input[name='cate_url']").parent().prev("th").html("频道URL规则");
		ajaxSetTplOrUrl("isindex");
	}else{
		$("input[name='show_tpl']").parent().parent().show();
		$("input[name='show_url']").parent().parent().show();

		$("input[name='cate_tpl']").parent().prev("th").html("分类模板");
		$("input[name='cate_url']").parent().prev("th").html("分类URL规则");
		ajaxSetTplOrUrl();
	}
}

//Ajax设置模板或静态URL
function ajaxSetTplOrUrl(T) {
	if(!editMid || editMid!=mid || editIsIndex!=isIndex) {
		twAjax.get("?mod=category&action=show&do=ajax_get_mid&mid="+mid+(T=="isindex" ? "&isindex=1" : ""), function(data){
			data = toJson(data);
			if(T=="isindex") {
				$("input[name='cate_tpl']").val(data.index_tpl);
				$("input[name='cate_url']").val(data.index_url);
			}else if(T=="page") {
				$("input[name='cate_tpl']").val(data.cate_tpl);
				$("input[name='cate_url']").val(data.cate_url);
			}else{
				$("input[name='cate_tpl']").val(data.cate_tpl);
				$("input[name='show_tpl']").val(data.show_tpl);
				$("input[name='cate_url']").val(data.cate_url);
				$("input[name='show_url']").val(data.show_url);
			}
		});
	}else{
		if(T=="isindex") {
			$("input[name='cate_tpl']").val($("input[name='cate_tpl']").attr("oldv"));
			$("input[name='cate_url']").val($("input[name='cate_url']").attr("oldv"));
		}else if(T=="page") {
			$("input[name='cate_tpl']").val($("input[name='cate_tpl']").attr("oldv"));
			$("input[name='cate_url']").val($("input[name='cate_url']").attr("oldv"));
		}else{
			$("input[name='cate_tpl']").val($("input[name='cate_tpl']").attr("oldv"));
			$("input[name='show_tpl']").val($("input[name='show_tpl']").attr("oldv"));
			$("input[name='cate_url']").val($("input[name='cate_url']").attr("oldv"));
			$("input[name='show_url']").val($("input[name='show_url']").attr("oldv"));
		}
	}
}

//加载分类附加功能
function loadAddition() {
	//树枝折叠
	$(".is").click(function(){
		$(".ac").css("position", "static");
		$(this).toggleClass("btm");
		$(this).parent().parent().next(".pre").toggle();
		$(".ac").css("position", "relative");
	});

	//选择树枝下级
	$(".see").change(function(){
		var CHK = $(this).attr("checked"), obj = $(this).parent().parent().parent().parent().find(".see");
		if(CHK == "checked") {
			obj.attr("checked", CHK);
		}else{
			obj.removeAttr("checked");
		}
	});

	//分类其它功能
	$(".ac").hover(
		function(){
			var cid = $(this).attr("cid");
			$(this).append('<div class="oper"><a class="but2" href="javascript:C.addSon('+cid+');"">增加子分类</a><a class="but2" href="javascript:C.edit('+cid+');">编辑</a><a class="but2" href="javascript:C.del('+cid+');">删除</a><a class="but2" href="index.php?u='+url_cate+cid+'" target="_blank">查看</a></div>');
		},
		function(){
			$(this).children(".oper").remove();
		}
	);
}

//提交表单
function _submit() {
	if(editor && editor.getContent()) editor.sync();
	twAjax.postd($(this).attr("action"), (cid ? "cid="+cid+"&" : '')+$(this).serialize(), function(data){
		window.twD = toJson(data);		
		if(twD.err==0) {
			$(".sky").remove();
			_getCate();
			twAjax.tipsHtml('<div class="ajaxbox btrue"><i></i><b>'+ twD.msg +'</b><p class="cf"><a class="but3" href="javascript:C.close();"">关闭</a><input type="button" value="继续" class="but1 jx"></p></div>');
			function contie() {
				twAjax.close();
				if(!cid) {
					$("[name='categoryname'],[name='dir'],[name='seotitle'],[name='keywords'],[name='description']").val("");
					if(editor && editor.getContent()) editor.setContent("");
				}
				clearTimeout(timeout);
			}
			timeout = setTimeout((!cid ? contie : C.close), 2000);
			$(".ajaxtips .jx").click(contie);
		}else{
			twAjax.alert(data);
		}
	});
	return false;
}

//增加分类
function _add() {
	_init();
	_getCateSelect(0);
	if($(this).val() == "增加分类") {
		$("#cadd").dialog("open");
		$(this).val("关闭增加");
		setDialog("增加分类");
	}else{
		$("#cadd").dialog("close");
		$(this).val("增加分类");
	}
}

//增加子分类
function _addSon(upid) {
	_init();
	$("#cadd").dialog("open");
	setDialog("增加子分类");
	_getCateSelect(upid);
}

//编辑分类
function _edit(cid) {
	twAjax.post("?mod=category&action=show&do=ajax_get_category_cid", {cid:cid}, function(data){
		data = toJson(data);
		editMid = data.mid;
		editIsIndex = data.isindex;
		_init(cid, data.mid, data.isindex);		//初始化相关属性
		$("#cadd").dialog("open");
		setDialog("编辑分类："+data.categoryname+" [cid:"+cid+"]");

		twAjax.get("?mod=category&action=show&do=ajax_get_category_select&is_edit=1&cid="+cid+"&upid="+data.upid+"&r="+(new Date).getTime(), function(data){
			$("[name='upid']").html(data);
		});
		$("[name='mid']").val([data.mid]);
		$("[name='isindex']").val([data.isindex]);
		$("[name='categoryname']").val(data.categoryname);
		$("[name='dir']").val(data.dir);
		$("[name='seotitle']").val(data.seotitle);
		$("[name='keywords']").val(data.keywords);
		$("[name='description']").val(data.description);
		$("[name='cate_tpl']").val(data.cate_tpl);
		$("[name='show_tpl']").val(data.show_tpl);
		$("[name='cate_url']").val(data.cate_url);
		$("[name='show_url']").val(data.show_url);
		$("[name='cate_tpl']").attr("oldv", data.cate_tpl);
		$("[name='show_tpl']").attr("oldv", data.show_tpl);
		$("[name='cate_url']").attr("oldv", data.cate_url);
		$("[name='show_url']").attr("oldv", data.show_url);
		$("[name='orderby']").val(data.orderby);
		
		if(data.mid==2) {
			if(editor) {
				editor.ready(function(){editor.setContent(data.pagecontent)});
			}else{
				setTimeout(function(){
					if(editor) editor.ready(function(){editor.setContent(data.pagecontent)});
				}, 500);
			}
		}
	});
}

//删除分类
function _del(cid) {
	twAjax.confirm("删除不可恢复，确定删除？", function(){
		twAjax.postd("?mod=category&action=show&do=ajax_del", {cid:cid}, function(data){
			twAjax.alert(data);
			if(window.twD.err==0) _getCate();
		});
	});
}

//关闭dialog
function _close() {
	twAjax.close();
	$("#cadd").dialog("close");
	$("#addCate").val("增加分类");
	clearTimeout(timeout);
}

//获取分类
function _getCate() {
	_getCateSelect($("[name='upid']").val());
	twAjax.get("?mod=category&action=show&do=ajax_get_category_html&r="+(new Date).getTime(), function(data){
		$(".cat").html(data);
		loadAddition();
	});
}

//获取Select分类
function _getCateSelect(upid) {
	twAjax.get("?mod=category&action=show&do=ajax_get_category_select&upid="+upid+"&r="+(new Date).getTime(), function(data){
		$("[name='upid']").html(data);
	});
}

//改变更多选项
function changeMore() {
	if($(this).attr("checked")) {
		$("#cadd tbody").show();
	}else{
		$("#cadd tbody").hide();
	}
	setDialogHeight();
}

//改变模型
function changeMid() {
	mid = $(this).val();
	setAll();
	setDialogHeight();
}

//改变分类属性
function changeIsIndex() {
	isIndex = $(this).val();
	setIsIndex();
	setDialogHeight();
}

//设置对话框属性
function setDialog(t) {
	$("#ui-id-1").html(t);
	$("#cadd").parent().css({left:"auto",right:20,top:35});
	setDialogHeight();
}

//设置对话框高度
function setDialogHeight() {
	$("#cadd").height("auto");
	$("#cadd").parent().height("auto");
	var H = $("#cadd").height(), Max = $(".c2").height()-100;
	$("#cadd").height(H > Max ? Max : H);
}

//加载编辑器
function getE() {
	if(!editor) {
		$("head").append('<script type="text/javascript" charset="utf-8" src="'+twEditor+'editor_config.js"></script><script type="text/javascript" charset="utf-8" src="'+twEditor+'editor_all_min.js"></script>');
		editor = UE.getEditor('pagecontent',{minFrameHeight:200});
	}
}

$(window).resize(setDialogHeight);
var C = {
	init : _init,
	submit : _submit,
	add : _add,
	addSon : _addSon,
	edit : _edit,
	del : _del,
	close : _close,
	getCate : _getCate,
	getCateSelect : _getCateSelect
};
window.C = C;

//创建隐藏对话框
$("#cadd").dialog({
	autoOpen: false,
	width:600,
	zIndex:99,
	buttons: {
		"确定": function() {
			$("form:first").submit();
		},
		"取消": function() {
			$(this).dialog("close");
			$("#addCate").val("增加分类");
		}
	}
});

loadAddition();										//加载分类附加功能
$("#addCate").click(C.add);							//显示增加分类对话框
$("form:first").submit(C.submit);					//添加分类
$("#more").change(changeMore);						//改变更多选项
$("input[name='mid']").change(changeMid);			//改变模型
$("input[name='isindex']").change(changeIsIndex);	//改变分类属性
$(".ui-dialog-titlebar-close").click(function(){ $("#addCate").val("增加分类"); });	//单击对话框关闭按钮
})();