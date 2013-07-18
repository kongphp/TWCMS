(function(){
//获取链接
function getLinks() {
	twAjax.get("?mod=content&action=links&ajax=get_links&r="+(new Date).getTime(), function(data){
		$("#allL tbody").html(data);
		loadFunc();
	});
}

//获取某条链接
function getLinkId(obj, id) {
	twAjax.get("?mod=content&action=links&ajax=get_link_id&id="+id+"&r="+(new Date).getTime(), function(data){
		if(!id) {
			$("#allL tbody").prepend(data);
		}else{
			obj.replaceWith(data);
		}
		loadFunc();
	});
}

//获取值
function _getVal(obj) {
	return {
		linkid : obj.find("[name='linkid']").attr("val"),
		orderby : obj.find("[name='orderby']").val(),
		name : obj.find("[name='name']").val(),
		url : obj.find("[name='url']").val(),
		logo : obj.find("[name='logo']").val(),
		note : obj.find("[name='note']").val(),
		status : obj.find("[name='status']").val(),
		type : obj.find("[name='type']").val()
	}
}

//提交表单
function _submit(o) {
	var obj = o.parent().parent(), D = _getVal(obj);
	twAjax.postd("?mod=content&action=links&ajax=add_edit", D, function(data){
		window.twName = true;
		twAjax.alert(data);
		$(".ajaxtips u").click(function(){
			if(twD.name != '') obj.find("[name='"+twD.name+"']").focus();
		});

		if(twD.err==0) {
			if(!D.linkid) {	//添加
				 getLinkId();
				_remove(o);
			}else{	//编辑
				getLinkId(obj, D.linkid);
			}
		}
	});
}

//取消添加
function _remove(o) {
	o.parent().parent().find("*").animate({height: '0',overflow:'hidden'}, "fast", function(){
		$(this).remove();
	});
}

//取消编辑
function _cancel(o) {
	var obj = o.parent().parent();
	obj.find("input.inp").each(function(){
		$(this).val($(this).attr("title"));	
	});
	obj.find("select").remove();
	obj.find("td:last input").remove();
	obj.find(".linkse,.but3").show();
	obj.find(".inp").removeAttr("style");
}

//添加
function _add() {
	if($(".head dl dd[istype='1'][class='on']").length > 0) {
		$("#allLT tbody").append('<tr class="li"><td> </td><td><input class="inp" name="typenameadd" type="text"><a class="but3" href="javascript:;" style="display:none;margin-left:8px">取消</a></td></tr>');
		$("#allLT tbody .but3:last").click(typeCancel);
		$("#allLT tbody .but3:last").parent().parent().hover(function(){$(this).find(".but3").show();}, function(){$(this).find(".but3").hide();});
	}else{
		$("#isEempty").hide();
		var obj = $("#allL thead tr:eq(1)");
		obj.after('<tr class="li">'+obj.html()+'</tr>');
		$("#allL thead tr:eq(2)").find(".btn_ok").click(function(){L.submit($(this))});
		$("#allL thead tr:eq(2)").find(".btn_cl").click(function(){L.remove($(this))});
	}
}

//编辑
function _edit() {
	var obj = $(this).parent().parent();
	obj.find(".inp").css("border-color", "#8E8F8F");
	obj.find(".linkse,.but3").hide();
	obj.find(".linkse").parent().find("select").remove();
	obj.find("td:last input").remove();

	var html1 = $("#allL thead tr:eq(1)").find("[name='status']").parent().html();
	var html2 = $("#allL thead tr:eq(1)").find("[name='type']").parent().html();
	obj.find(".linkse:eq(0)").after(html1);
	obj.find("[name='status']").val(obj.find(".linkse:eq(0)").attr("val"));
	obj.find(".linkse:eq(1)").after(html2);
	obj.find("[name='type']").val(obj.find(".linkse:eq(1)").attr("val"));

	obj.find("td:last").append('<input type="button" value="" class="btn_ok" /><input type="button" value="" class="btn_cl" />');
	obj.find(".btn_ok").click(function(){L.submit($(this))});
	obj.find(".btn_cl").click(function(){L.cancel($(this))});
}

//删除
function _del(id) {
	twAjax.confirm("删除不可恢复，确定删除？", function(){
		twAjax.postd("?mod=content&action=links&ajax=del", {id:id}, function(data){
			twAjax.alert(data);
			if(window.twD.err==0) {$("#allL [name='linkid'][val='"+id+"']").parent().parent().remove(); checkBatchDel();}
		});
	});
}

//批量删除
function _batchDel() {
	var arr = new Array(), obj = $("#allL tbody input[name='linkid'][checked='checked']");
	obj.each(function(i) {
		arr[i] = $(this).attr("val");
	});
	twAjax.confirm("删除不可恢复，确定删除？", function(){
		twAjax.postd("?mod=content&action=links&ajax=batch_del", {idarr:arr}, function(data){
			twAjax.alert(data);
			if(window.twD.err==0) {obj.parent().parent().remove(); checkBatchDel();}
		});
	});
}

//链接分类删除
function _typeDel(id) {
	twAjax.confirm("删除不可恢复，确定删除？", function(){
		twAjax.postd("?mod=content&action=links&ajax=type_del", {typeid:id}, function(data){
			twAjax.alert(data);
			if(window.twD.err==0) getLinksType();
		});
	});
}

var L = {
	getVal : _getVal,
	submit : _submit,
	remove : _remove,
	cancel : _cancel,
	add : _add,
	edit : _edit,
	del : _del,
	batchDel : _batchDel,
	typeDel : _typeDel
};
window.L = L;

$(document).ready(function(){
	$("#addLinks").click(L.add);
	loadFunc();

	//全选
	$("input[name='chkall']").change(function(){
		var CHK = $(this).attr("checked"), obj = $("#allL tbody input[name='linkid']");
		if(CHK == "checked") {
			obj.attr("checked", CHK);
		}else{
			obj.removeAttr("checked");
		}
		checkBatchDel();
	});

	$(".head dl dd[istype='1']").click(function(){$("#allL tbody input[name='linkid'],input[name='chkall']").removeAttr("checked"); checkBatchDel();});
	$("#linkType").click(typeAddEdit);	//链接分类
	getLinksType();
});

//链接分类取消
function typeCancel() {
	$(this).parent().parent().remove();
}

//获取所有链接分类
function getLinksType() {
	twAjax.get("?mod=content&action=links&ajax=get_links_type_select&r="+(new Date).getTime(), function(data){
		$(".cfgType").html(data);
		if($(".cfgType").length > 1) {
			$(".cfgType").each(function(){
				$(this).val($(this).parent().find(".linkse").attr("val"));
			});
		}
	});
	twAjax.get("?mod=content&action=links&ajax=get_links_type&r="+(new Date).getTime(), function(data){
		$("#allLT tbody").html(data);
		typeLoad();
	});
}

//加载链接分类相关
function typeLoad() {
	$("[name='typename']").each(function(){ $(this).attr("title", $(this).val()); });
	$("#allLT tbody tr").hover(
		function() { $(this).find(".inp").after('<a class="but3" href="javascript:L.typeDel('+$(this).find("[typeid]").attr("typeid")+');" style="margin-left:8px">删除</a>'); },
		function() { $(this).find(".but3").remove(); }
	);
}

//添加和编辑链接分类
function typeAddEdit() {
	var addArr = new Array(), addObj = $("[name='typenameadd']"), editArr = new Array(), editObj = $("[name='typename']");
	addObj.each(function(i) {
		addArr[i] = $(this).val();
	});
	var i=0;
	editObj.each(function() {
		var Val = $(this).val();
		if(Val != $(this).attr("title")) {
			editArr[i] = [$(this).attr("typeid"), Val];
			i++;
		}
	});
	twAjax.postd("?mod=content&action=links&ajax=type_add_edit", {add:addArr, edit:editArr}, function(data){
		twAjax.alert(data);
		if(window.twD.err==0) getLinksType();
	});
}

//单选
function radioDel() {
	var CHK = $(this).attr("checked");
	if(CHK == "checked") {
		$(this).attr("checked", CHK);
	}else{
		$(this).removeAttr("checked");
	}
	checkBatchDel();
}

//检查是否激活批量删除
function checkBatchDel() {
	var linkidLen = $("#allL tbody input[name='linkid'][checked='checked']").length;
	if(linkidLen > 0) {
		if($("#delLinks").length == 0) $(".head dl").append('<input id="delLinks" type="button" value="批量删除" onclick="L.batchDel()" class="but1">');
	}else{
		$("#delLinks").remove();
	}
}

//加载事件
function loadFunc() {
	$("#allL tbody input.inp").each(function(){
		if(!$(this).attr("title")) $(this).attr("title", $(this).val());
	});
	$(".btn_ok").each(function(){
		if($(this).attr("isOne")!=1) $(this).click(function(){L.submit($(this))}).attr("isOne", 1);
	});
	$(".btn_cl").each(function(){
		if($(this).attr("isOne")!=1) $(this).click(function(){L.remove($(this))}).attr("isOne", 1);
	});
	$("#allL tbody .li").each(function(){
		if($(this).attr("isOne")!=1) {
			$(this).hover(function(){$(this).find(".inp").addClass("on");}, function(){$(this).find(".inp").removeClass("on");}).attr("isOne", 1);
		}
	});
	$("#allL tbody div.inp").each(function(){
		if($(this).attr("isOne")!=1) $(this).click(L.edit).attr("isOne", 1);
	});
	$("#allL tbody input.inp").each(function(){
		if($(this).attr("isOne")!=1) $(this).focusin(L.edit).attr("isOne", 1);
	});
	$("#allL [name='linkid']").each(function(){
		if($(this).attr("isOne")!=1) $(this).change(radioDel).attr("isOne", 1);
	});
}
})();