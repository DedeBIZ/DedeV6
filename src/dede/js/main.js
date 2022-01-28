var fixupPos = false;
var canMove = false;
var leftLeaning = 0;
//异步上传缩略图相关变量
var nForm = null;
var nFrame = null;
var picnameObj = null;
var vImg = null;

function $Nav() {
	if (window.navigator.userAgent.indexOf("MSIE") >= 1) return 'IE';
	else if (window.navigator.userAgent.indexOf("Firefox") >= 1) return 'FF';
	else return "OT";
}

function $Obj(objname) {
	return document.getElementById(objname);
}

//旧的颜色选择框（已经过期）
/*
function ShowColor()
{
	var fcolor=showModalDialog("images/color.htm?ok",false,"dialogWidth:106px;dialogHeight:110px;status:0;dialogTop:"+(+120)+";dialogLeft:"+(+120));
	if(fcolor!=null && fcolor!="undefined") document.form1.color.value = fcolor;
}
*/

function ColorSel(c, oname) {
	var tobj = $Obj(oname);
	if (!tobj) tobj = eval('document.form1.' + oname);
	if (!tobj) {
		$Obj('colordlg').style.display = 'none';
		return false;
	}
	else {
		tobj.value = c;
		$Obj('colordlg').style.display = 'none';
		return true;
	}
}

function ShowColor(e, o) {
	LoadNewDiv(e, 'images/colornew.htm', 'colordlg');
}

function ShowHide(objname) {
	var obj = $Obj(objname);
	if (obj.style.display != "none") obj.style.display = "none";
	else obj.style.display = "inline-block";
}

function ShowHideT(objname) {
	var obj = $Obj(objname);
	if (obj.style.display != "none") obj.style.display = "none";
	else obj.style.display = ($Nav() == "IE" ? "inline-block" : "table");
}

function ShowObj(objname) {
	var obj = $Obj(objname);
	if (obj == null) return false;
	obj.style.display = ($Nav() == "IE" ? "inline-block" : "table");
}

function ShowObjRow(objname) {
	var obj = $Obj(objname);
	obj.style.display = ($Nav() == "IE" ? "inline-block" : "table-row");
}

function AddTypeid2() {
	ShowObjRow('typeid2tr');
	//$Obj('typeid2ct').innerHTML = $Obj('typeidct').innerHTML.replace('typeid','typeid2');
}

function HideObj(objname) {
	var obj = $Obj(objname);
	if (obj == null) return false;
	obj.style.display = "none";
}

function ShowItem1() {
	ShowObj('needset'); ShowObj('head1'); HideObj('head2'); HideObj('adset'); ShowObj('votehead');
}

function ShowItem2() {
	ShowObj('head2'); ShowObj('adset'); HideObj('voteset'); HideObj('head1'); HideObj('needset'); HideObj('votehead');
}

function SeePic(img, f) {
	if (f.value != '') img.src = f.value;
}

function SeePicNew(f, imgdid, frname, hpos, acname) {
	var newobj = null;
	if (f.value == '') return;
	vImg = $Obj(imgdid);
	picnameObj = document.getElementById('picname');
	nFrame = $Nav() == 'IE' ? eval('document.frames.' + frname) : $Obj(frname);
	nForm = f.form;
	//修改form的action等参数
	if (nForm.detachEvent) nForm.detachEvent("onsubmit", checkSubmit);
	else nForm.removeEventListener("submit", checkSubmit, false);
	nForm.action = 'archives_do.php';
	nForm.target = frname;
	nForm.dopost.value = 'uploadLitpic';
	nForm.submit();

	picnameObj.value = '';
	newobj = $Obj('uploadwait');
	if (!newobj) {
		newobj = document.createElement("DIV");
		newobj.id = 'uploadwait';
		newobj.style.position = 'absolute';
		newobj.className = 'uploadwait';
		newobj.style.width = 120;
		newobj.style.height = 20;
		newobj.style.top = hpos;
		newobj.style.left = 100;
		newobj.style.display = 'block';
		document.body.appendChild(newobj);
		newobj.innerHTML = '<img src="images/loadinglit.gif" width="16" height="16" alit="" />上传中...';
	}
	newobj.style.display = 'block';
	//提交后还原form的action等参数
	nForm.action = acname;
	nForm.dopost.value = 'save';
	nForm.target = '';
	nForm.litpic.disabled = true;
	//nForm.litpic = null;
	//if(nForm.attachEvent) nForm.attachEvent("onsubmit", checkSubmit);
	//else nForm.addEventListener("submit", checkSubmit, true);
}

function SelectFlash() {
	if ($Nav() == 'IE') { var posLeft = window.event.clientX - 300; var posTop = window.event.clientY; }
	else { var posLeft = 100; var posTop = 100; }
	window.open("./dialog/select_media.php?f=form1.flashurl", "popUpFlashWin", "scrollbars=yes,resizable=yes,statebar=no,width=500,height=350,left=" + posLeft + ", top=" + posTop);
}

function SelectMedia(fname) {
	if ($Nav() == 'IE') { var posLeft = window.event.clientX - 200; var posTop = window.event.clientY; }
	else { var posLeft = 100; var posTop = 100; }
	window.open("./dialog/select_media.php?f=" + fname, "popUpFlashWin", "scrollbars=yes,resizable=yes,statebar=no,width=500,height=350,left=" + posLeft + ", top=" + posTop);
}

function SelectSoft(fname) {
	if ($Nav() == 'IE') { var posLeft = window.event.clientX - 200; var posTop = window.event.clientY - 50; }
	else { var posLeft = 100; var posTop = 100; }
	window.open("./dialog/select_soft.php?f=" + fname, "popUpImagesWin", "scrollbars=yes,resizable=yes,statebar=no,width=600,height=400,left=" + posLeft + ", top=" + posTop);
}

function SelectImage(fname, stype, imgsel) {
	if ($Nav() == 'IE') { var posLeft = window.event.clientX - 100; var posTop = window.event.clientY; }
	else { var posLeft = 100; var posTop = 100; }
	if (!fname) fname = 'form1.picname';
	if (imgsel) imgsel = '&noeditor=yes';
	if (!stype) stype = '';
	window.open("./dialog/select_images.php?f=" + fname + "&noeditor=yes&imgstick=" + stype + imgsel, "popUpImagesWin", "scrollbars=yes,resizable=yes,statebar=no,width=650,height=400,left=" + posLeft + ", top=" + posTop);
}

function imageCut(fname) {
	if ($Nav() == 'IE') { var posLeft = window.event.clientX - 100; var posTop = window.event.clientY; }
	else { var posLeft = 100; var posTop = 100; }
	if (!fname) fname = 'picname';
	file = document.getElementById(fname).value;
	if (file == '') {
		alert('请先选择网站内已上传的图片');
		return false;
	}
	window.open("imagecut.php?f=" + fname + "&file=" + file, "popUpImagesWin", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left=" + posLeft + ", top=" + posTop);
}

function SelectImageN(fname, stype, vname) {
	if ($Nav() == 'IE') { var posLeft = window.event.clientX - 100; var posTop = window.event.clientY; }
	else { var posLeft = 100; var posTop = 100; }
	if (!fname) fname = 'form1.picname';
	if (!stype) stype = '';
	window.open("./dialog/select_images.php?f=" + fname + "&imgstick=" + stype + "&v=" + vname, "popUpImagesWin", "scrollbars=yes,resizable=yes,statebar=no,width=600,height=400,left=" + posLeft + ", top=" + posTop);
}

function SelectKeywords(f) {
	if ($Nav() == 'IE') { var posLeft = window.event.clientX - 350; var posTop = window.event.clientY - 200; }
	else { var posLeft = 100; var posTop = 100; }
	window.open("article_keywords_select.php?f=" + f, "popUpkwWin", "scrollbars=yes,resizable=yes,statebar=no,width=600,height=450,left=" + posLeft + ", top=" + posTop);
}

function InitPage() {
	var selsource = $Obj('selsource');
	var selwriter = $Obj('selwriter');
	var titlechange = $Obj('title');
	var colorbt = $Obj('color');
	if (selsource) { selsource.onmousedown = function (e) { SelectSource(e); } }
	if (selwriter) { selwriter.onmousedown = function (e) { SelectWriter(e); } }
	if (titlechange) { titlechange.onchange = function (e) { TestHasTitle(e); } }
}

function OpenMyWin(surl) {
	window.open(surl, "popUpMyWin", "scrollbars=yes,resizable=yes,statebar=no,width=500,height=350,left=200, top=100");
}

function OpenMyWinCoOne(surl) {
	window.open(surl, "popUpMyWin2", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=450,left=100,top=50");
}

function PutSource(str) {
	var osource = $Obj('source');
	if (osource) osource.value = str;
	$Obj('mysource').style.display = 'none';
	ChangeFullDiv('hide');
}

function PutWriter(str) {
	var owriter = $Obj('writer');
	if (owriter) owriter.value = str;
	$Obj('mywriter').style.display = 'none';
	ChangeFullDiv('hide');
}

// 增加选择投票内容
function PutVote(str) {
	var ovote = $Obj('voteid');
	if (ovote) {
		ovote.value = str;
		tb_remove(false);
	} else {
		parent.document.form1.voteid.value = str;
		tb_remove(true);
	}

}

function ClearDivCt(objname) {
	if (!$Obj(objname)) return;
	$Obj(objname).innerHTML = '';
	$Obj(objname).style.display = 'none';
	ChangeFullDiv("hide");
}

function ChangeFullDiv(showhide, screenheigt) {
	var newobj = $Obj('fullpagediv');
	if (showhide == 'show') {
		if (!newobj) {
			newobj = document.createElement("DIV");
			newobj.id = 'fullpagediv';
			newobj.style.position = 'absolute';
			newobj.className = 'fullpagediv';
			newobj.style.height = document.body.clientHeight + 50 + 'px';
			document.body.appendChild(newobj);
		}
		else {
			newobj.style.display = 'block';
		}
	}
	else {
		if (newobj) newobj.style.display = 'none';
	}
}

function SelectSource(e) {
	LoadNewDiv(e, 'article_select_sw.php?t=source&k=8&rnd=' + Math.random(), 'mysource');
	//ChangeFullDiv('show');
}

function SelectWriter(e) {
	LoadNewDiv(e, 'article_select_sw.php?t=writer&k=8&rnd=' + Math.random(), 'mywriter');
	//ChangeFullDiv('show');
}

function LoadNewDiv(e, surl, oname) {
	var pxStr = '';
	if ($Nav() == 'IE') {
		var posLeft = window.event.clientX - 20;
		var posTop = window.event.clientY - 30;
		// IE下scrollTop的兼容性问题
		var scrollTop = document.documentElement.scrollTop || window.pageYOffset;
		if (typeof (scrollTop) == 'undefined') scrollTop = document.body.scrollTop;
		posTop += scrollTop;

	}
	else {
		var posLeft = e.pageX - 20;
		var posTop = e.pageY - 30;
		pxStr = 'px';
	}
	posLeft = posLeft - 100;
	var newobj = $Obj(oname);
	if (!newobj) {
		newobj = document.createElement("DIV");
		newobj.id = oname;
		newobj.style.position = 'absolute';
		newobj.className = oname;
		newobj.className += ' dlgws';
		newobj.style.top = posTop + pxStr;
		newobj.style.left = posLeft + pxStr;
		document.body.appendChild(newobj);
	}
	else {
		newobj.style.display = "block";
	}
	if (newobj.innerHTML.length < 10) {
		var myajax = new DedeAjax(newobj);
		myajax.SendGet(surl);
	}
}

function TestHasTitle(e) {
	LoadNewDiv2(e, 'article_test_title.php?t=' + $Obj('title').value, 'mytitle', "dlgTesttitle");
}

function LoadNewDiv2(e, surl, oname, dlgcls) {
	var posLeft = 300;
	var posTop = 50;
	var newobj = $Obj(oname);
	if (!newobj) {
		newobj = document.createElement("DIV");
		newobj.id = oname;
		newobj.style.position = 'absolute';
		newobj.className = dlgcls;
		newobj.style.top = posTop;
		newobj.style.left = posLeft;
		newobj.style.display = 'none';
		document.body.appendChild(newobj);
	}
	newobj.innerHTML = '';
	var myajax = new DedeAjax(newobj);
	myajax.SendGet2(surl);
	if (newobj.innerHTML == '') newobj.style.display = 'none';
	else newobj.style.display = 'block';
	jQuery(newobj).css('top', '50px').css('left', '300px');
	DedeXHTTP = null;
}

function ShowUrlTr() {
	var jumpTest = $Obj('flagsj');
	var jtr = $Obj('redirecturltr');
	var jf = $Obj('redirecturl');
	if (jumpTest.checked) jtr.style.display = "block";
	else {
		jf.value = '';
		jtr.style.display = "none";
	}
}

function ShowUrlTrEdit() {
	ShowUrlTr();
	var jumpTest = $Obj('isjump');
	var rurl = $Obj('redirecturl');
	if (!jumpTest.checked) rurl.value = "";
}

function CkRemote() {
	document.getElementById('picname').value = '';
}

//载入指定宽高的AJAX窗体
function LoadQuickDiv(e, surl, oname, w, h) {
	if ($Nav() == 'IE') {
		if (window.event) {
			var posLeft = window.event.clientX - 20;
			var posTop = window.event.clientY - 30;
		}
		else {
			var posLeft = e.clientX - 20;
			var posTop = e.clientY + 30;
		}
	}
	else {
		var posLeft = e.pageX - 20;
		var posTop = e.pageY - 30;
	}
	posTop += MyGetScrollTop();
	posLeft = posLeft - 400;
	//固定位置的高度
	if (fixupPos) {
		posLeft = posTop = 50;
	}
	var newobj = $Obj(oname);
	if (!newobj) {
		newobj = document.createElement("DIV");
		newobj.id = oname;
		newobj.style.position = 'absolute';
		newobj.className = 'pubdlg';
		newobj.style.width = w;
		newobj.style.height = h + 30;
		document.body.appendChild(newobj);
	}
	if (posTop > 500) posTop = 500;
	if (posLeft < 50) posLeft = 50;
	newobj.style.minWidth = "450px";
	newobj.style.top = posTop + "px";
	newobj.style.left = posLeft + "px";
	newobj.innerHTML = '<div style="margin-top:10px;margin-left:10px;"><img src="images/loadinglit.gif" /> Loading...</div>';
	newobj.style.display = 'block';
	var myajax = new DedeAjax(newobj);
	myajax.SendGet(surl);
	fixupPos = false;
}

function MyGetScrollTop() {
	return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
}

//通用事件获取接口
function getEvent() {
	if ($Nav() == 'IE') return window.event;
	func = getEvent.caller;
	while (func != null) {
		var arg0 = func.arguments[0];
		if (arg0) {
			if ((arg0.constructor == Event || arg0.constructor == MouseEvent)
				|| (typeof (arg0) == "object" && arg0.preventDefault && arg0.stopPropagation)) {
				return arg0;
			}
		}
		func = func.caller;
	}
	return null;
}

//模拟ondrop事件相关代码
/*----------------------------
leftLeaning = 300;
如果对象内容固定，用onmousedown=DropStart去除底下的DropStop
newobj.ondblclick =  DropStart;
newobj.onmousemove = DropMove;
newobj.onmousedown = DropStop;
----------------------------*/
function DropStart() {
	this.style.cursor = 'move';
}

function DropStop() {
	this.style.cursor = 'default';
}

function DropMove() {
	if (this.style.cursor != 'move') return;
	var event = getEvent();
	if ($Nav() == 'IE') {
		var posLeft = event.clientX - 20;
		var posTop = event.clientY - 30;
		posTop += document.body.scrollTop;
	}
	else {
		var posLeft = event.pageX - 20;
		var posTop = event.pageY - 30;
	}
	this.style.top = posTop;
	this.style.left = posLeft - leftLeaning;
}

//对指定的元素绑定move事件
/*-----------------------------
onmousemove="DropMoveHand('divname', 225);"
onmousedown="DropStartHand();"
onmouseup="DropStopHand();"
-----------------------------*/
function DropStartHand() {
	canMove = (canMove ? false : true);
}
function DropStopHand() {
	canMove = false;
}
function DropMoveHand(objid, mleftLeaning) {
	var event = getEvent();
	var obj = $Obj(objid);
	if (!canMove) return;

	if ($Nav() == 'IE') {
		var posLeft = event.clientX - 20;
		var posTop = event.clientY - 20;
		posTop += window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
	}
	else {
		var posLeft = event.pageX - 20;
		var posTop = event.pageY - 20;
	}
	obj.style.top = posTop + "px";
	obj.style.left = posLeft - mleftLeaning + "px";
}

//复制内容到剪切板
function copyToClipboard(txt) {
	if (txt == null || txt == '') {
		alert("没有选择任何内容!");
		return;
	}
	if (window.clipboardData) {
		window.clipboardData.clearData();
		window.clipboardData.setData("Text", txt);
	}
	else if (navigator.userAgent.indexOf('Opera') != -1) {
		window.location = txt;
	}
	else {
		try {
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		} catch (e) {
			alert("被浏览器拒绝！\n请在浏览器地址栏输入'about:config'并回车\n然后将'signed.applets.codebase_principal_support'设置为'true'");
		}
		var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
		if (!clip) return;

		var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
		if (!trans) return;

		trans.addDataFlavor('text/unicode');
		var str = new Object();
		var len = new Object();
		var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
		var copytext = txt;
		str.data = copytext;
		trans.setTransferData("text/unicode", str, copytext.length * 2);
		var clipid = Components.interfaces.nsIClipboard;
		if (!clip) return false;
		clip.setData(trans, null, clipid.kGlobalClipboard);
	}
}

function getSelTxt() {
	var g, r;
	if (document.all) {
		g = document.selection.createRange().text;
	} else {
		g = document.getSelection();
	}
	return g;
}

//显示栏目Map地图
function ShowCatMap(e, obj, cid, targetId, oldvalue) {
	fixupPos = true;
	LoadQuickDiv(e, 'archives_do.php?dopost=getCatMap&targetid=' + targetId + '&channelid=' + cid + '&oldvalue=' + oldvalue + '&rnd=' + Math.random(), 'getCatMap', '700px', '500px');
	ChangeFullDiv('show');
}

function getSelCat(targetId) {
	var selBox = document.quicksel.seltypeid;
	var targetObj = $Obj(targetId);
	var selvalue = '';
	//副栏目（多选）
	if (targetId == 'typeid2') {
		var j = 0;
		for (var i = 0; i < selBox.length; i++) {
			if (selBox[i].checked) {
				j++;
				if (j == 10) break;
				selvalue += (selvalue == '' ? selBox[i].value : ',' + selBox[i].value);
			}
		}
		if (targetObj) targetObj.value = selvalue;
	}
	//主栏目（单选）
	else {
		if (selBox) {
			for (var i = 0; i < selBox.length; i++) {
				if (selBox[i].checked) selvalue = selBox[i].value;
			}
		}
		if (selvalue == '') {
			alert('你没有选中任何项目！');
			return;
		}
		if (targetObj) {
			for (var j = 0; j < targetObj.length; j++) {
				op = targetObj.options[j];
				if (op.value == selvalue) op.selected = true;
			}
		}
	}
	HideObj("getCatMap");
	ChangeFullDiv("hide");
}

function getElementLeft(element) {
	var actualLeft = element.offsetLeft;
	var current = element.offsetParent;

	while (current !== null) {
		actualLeft += current.offsetLeft;
		current = current.offsetParent;
	}

	return actualLeft;
}

function getElementTop(element) {
	var actualTop = element.offsetTop;
	var current = element.offsetParent;

	while (current !== null) {
		actualTop += current.offsetTop;
		current = current.offsetParent;
	}

	return actualTop;
}

// Copyright 2020 The MuEMS Authors. All rights reserved.
// license that can be found in the LICENSE file.

// -----msgbox-------------------------------------

// 生成一个随机ID
function guid() {
	function S4() {
		return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
	}
	return (S4() + S4() + "-" + S4() + "-" + S4() + "-" + S4() + "-" + S4() + S4() + S4());
}

// 显示对话框，动态创建modal并显示，退出自动销毁窗体
// args是以下结构体
/*
args = {
	title : "",         // 标题，默认是MuEMS
	footer : "",        // 底部按钮，可以自定义按钮
	noClose : false,    // 是否显示右上角关闭按钮，默认显示
}
*/
// 函数会返回一个modalID，通过这个ID可自已定义一些方法
// 这里用到了一个展开语法
// https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Operators/Spread_syntax
function ShowMsg(content, ...args) {
	title = "DedeBIZ信息提示";
	if (typeof content == "undefined") content = "";
	modalID = guid();
	var footer = `<button type="button" class="btn btn-primary" onClick="CloseModal(\'GKModal${modalID}\')">Ok</button>`;
	var noClose = false;

	if (args.length == 1) {
		// 存在args参数
		if (typeof args[0].title !== 'undefined' && args[0].title != "") {
			title = args[0].title;
		}
		if (typeof args[0].footer !== 'undefined' && args[0].footer != "") {
			footer = args[0].footer;
		}
		if (typeof args[0].noClose !== 'undefined' && args[0].noClose == true) {
			noClose = true;
		}
	}

	footer = footer.replace("~modalID~", modalID);
	content = content.replace("~modalID~", modalID);

	var modal = `<div id="GKModal${modalID}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="GKModalLabel${modalID}" aria-hidden="true">
<div class="modal-dialog" role="document">
<div class="modal-content"><div class="modal-header">
<h5 class="modal-title" id="GKModalLabel${modalID}">${title}</h5>`;
	if (!noClose) {
		modal += `<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">&times;</span>
		</button>`;
	}
	modal += `</div><div class="modal-body">${content}</div><div class="modal-footer">${footer}</div></div></div></div>`;
	$("body").append(modal)
	$("#GKModal" + modalID).modal({
		backdrop: 'static',
		show: true
	});
	$("#GKModal" + modalID).on('hidden.bs.modal', function (e) {
		$("#GKModal" + modalID).remove();
	})
	return modalID;
}

// 隐藏并销毁modal
function CloseModal(modalID) {
	$("#" + modalID).modal('hide');
	$("#" + modalID).on('hidden.bs.modal', function (e) {
		if ($("#" + modalID).length > 0) {
			$("#" + modalID).remove();
		}
	})
}

// 获取缩略图
var litpicImgSrc = "";
var litpicImg = "";
var currentCID = 0;
var mdlCropperID = "";
var pubAt = 0;
var optCropper = {
	preview: ".pv",
	crop: function (e) {
		$("#cropWidth").text(Math.round(e.detail.height));
		$("#cropHeight").text(Math.round(e.detail.width));
		var dataUrl = $(this).cropper("getCroppedCanvas")
			.toDataURL();
		litpicImg = dataUrl;
		$("#litPic").attr("src", litpicImg);
		$("#litpic_b64").val(litpicImg);
	},
	aspectRatio: 4 / 3,
	// 拖动截取缩略图后，截取的缩略图更新到imageItems中
	cropend: function (data) {
		// 这里的ID要单独取出来
		var dataUrl = $(this).cropper("getCroppedCanvas")
			.toDataURL();
		litpicImg = dataUrl;
		$("#litPic").attr("src", litpicImg);
		$("#litpic_b64").val(litpicImg);
	}
}

var cropperAspectRatio = {
	0: 16 / 9,
	1: 4 / 3,
	2: 1 / 1,
	3: 2 / 3,
	4: NaN,
}

function setAspectRatio(ar) {
	var opts = optCropper;
	opts.aspectRatio = cropperAspectRatio[ar];
	$("#cropImg" + mdlCropperID).cropper('destroy').cropper(opts);
}

function useDefault(modalID) {
	$("#litpic_b64").val(litpicImgSrc);
	$("#litPic").attr("src", litpicImgSrc);
	CloseModal('GKModal' + modalID);
}

$(document).ready(function () {

	$("#btnClearAll").click(function (event) {
		litpicImgSrc = "";
		litpicImg = "";
		$("#litpic_b64").val(litpicImg);
		$("#litPic").attr("src", "../static/defaultpic.gif");
	})

	// 添加图片
	$("#iptAddImages").change(function (event) {
		var files = event.target.files;
		for (var i = 0, f; f = files[i]; i++) {
			// 如果不是图片忽略
			if (!f.type.match('image.*')) {
				continue;
			}

			// 将图片渲染到浏览器
			var reader = new FileReader();
			reader.onload = (function (theFile) {
				return function (e) {
					litpicImgSrc = e.target.result;
					SetThumb(litpicImgSrc);
				};
			})(f);
			reader.readAsDataURL(f);
		}
		$("#iptAddImages").val("");
	});

	// 截取缩略图
	function SetThumb(srcURL) {
		var footer =
			"<p><a href='javascript:useDefault(\"~modalID~\");' class='btn btn-success'>使用原图</a> <a href='?' class='btn btn-success' data-dismiss='modal' aria-label='Close'>确定</a></p>";
		var optButton = `<p><div class="form-group">
			  <label for="aspectRatio">比例</label>
			  <select class="form-control" id="aspectRatio" onchange="setAspectRatio(this.selectedIndex)">
				<option>16:9</option>
				<option selected>4:3</option>
				<option>1:1</option>
				<option>2:3</option>
				<option>自定义</option>
			  </select>
			</div></p>`;
		mdlCropperID = ShowMsg(
			'<div><div class="float-left" style="width:300px;"><img id="cropImg~modalID~" src="' +
			srcURL +
			'" width=200><p>宽度：<span id="cropWidth"></span>px，高度：<span id="cropHeight"></span>px</p>' + optButton + '</div><div class="pv float-right" style="width:150px;height:100px;overflow:hidden;"></div></div>', {
			footer: footer,
			noClose: false,
			title: 'DedeBIZ缩略图裁剪',
		});

		setTimeout(function () {
			$("#cropImg" + mdlCropperID).cropper(optCropper);
		}, 500);
	}

	if ($.fn.daterangepicker) {
		$('.datepicker').daterangepicker({
			"singleDatePicker": true,
			"autoApply": true,
			"showDropdowns": true,
			"linkedCalendars": false,
			"timePicker": true,
			"timePicker24Hour": true,
			"timePickerSeconds": true,
			"showCustomRangeLabel": false,
			"drops": "up",
			ranges: {
				'今日': [moment(), moment()],
				'昨日': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				'本月': [moment().startOf('month'), moment().startOf('month')],
				'上月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').startOf('month')]
			},
			"locale": {
				format: 'YYYY-MM-DD HH:mm:ss',
				applyLabel: '确定',
				cancelLabel: '取消',
				daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
				monthNames: ['一月', '二月', '三月', '四月', '五月', '六月',
					'七月', '八月', '九月', '十月', '十一月', '十二月'],
				firstDay: 1
			}
		}, function (start) {
			$(this).val(start.format("YYYY-MM-DD HH:mm:ss"));
		});
		$('.datepicker').on('show.daterangepicker', function (ev, picker) {
			if (picker.element.offset().top - $(window).scrollTop() + picker.container.outerHeight() > $(window).height()) {
				picker.drops = 'up';
			} else {
				picker.drops = 'down';
			}
			picker.move();
		})
	}



})