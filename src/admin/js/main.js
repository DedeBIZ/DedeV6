var fixupPos = false;
var canMove = false;
var leftLeaning = 0;
//异步上传缩略图相关变量
var nForm = null;
var nFrame = null;
var picnameObj = null;
var vImg = null;
function GetWinPos(w, h) {
	var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
	var dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;
	var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
	var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
	var systemZoom = width / window.screen.availWidth;
	var left = (width - w) / 2 / systemZoom + dualScreenLeft;
	var top = (height - h) / 2 / systemZoom + dualScreenTop;
	return { left: left, top: top };
}
function $Nav() {
	if (window.navigator.userAgent.indexOf("MSIE") >= 1) return 'IE';
	else if (window.navigator.userAgent.indexOf("Firefox") >= 1) return 'FF';
	else return "OT";
}
function $Obj(objname) {
	return document.getElementById(objname);
}
function ColorSel(c, oname) {
	var tobj = $Obj(oname);
	if (!tobj) tobj = eval('document.form1.' + oname);
	if (!tobj) {
		$Obj('colordlg').style.display = 'none';
		return false;
	} else {
		tobj.value = c;
		$Obj('colordlg').style.display = 'none';
		return true;
	}
}
function ShowColor(e, o) {
	LoadNewDiv(e, '../../static/web/img/colornew.htm', 'colordlg');
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
		newobj.innerHTML = '<img src="../../static/web/img/loadinglit.gif">';
	}
	newobj.style.display = 'block';
	//提交后还原form的action等参数
	nForm.action = acname;
	nForm.dopost.value = 'save';
	nForm.target = '';
	nForm.litpic.disabled = true;
}
function SelectFlash() {
	var pos = GetWinPos(800,600);
	window.open("./dialog/select_media.php?f=form1.flashurl", "popUpFlashWin", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left=" + pos.left + ", top=" + pos.top);
}
function SelectMedia(fname) {
	var pos = GetWinPos(800,600);
	window.open("./dialog/select_media.php?f=" + fname, "popUpFlashWin", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left=" + pos.left + ", top=" + pos.top);
}
function SelectSoft(fname) {
	var pos = GetWinPos(800,600);
	window.open("./dialog/select_soft.php?f=" + fname, "popUpImagesWin", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left=" + pos.left + ", top=" + pos.top);
}
function SelectImage(fname, stype, imgsel) {
	var pos = GetWinPos(800,600);
	if (!fname) fname = 'form1.picname';
	if (imgsel) imgsel = '&noeditor=yes';
	if (!stype) stype = '';
	window.open("./dialog/select_images.php?f=" + fname + "&noeditor=yes&imgstick=" + stype + imgsel, "popUpImagesWin", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left=" + pos.left + ", top=" + pos.top);
}
function SelectImageN(fname, stype, vname) {
	var pos = GetWinPos(800,600);
	if (!fname) fname = 'form1.picname';
	if (!stype) stype = '';
	window.open("./dialog/select_images.php?f=" + fname + "&imgstick=" + stype + "&v=" + vname, "popUpImagesWin", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left=" + pos.left + ", top=" + pos.top);
}
function SelectKeywords(f) {
	var pos = GetWinPos(800,600);
	window.open("article_keywords_select.php?f=" + f, "popUpkwWin", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left=" + pos.left + ", top=" + pos.top);
}
function InitPage() {
	var selsource = $Obj('selsource');
	var selwriter = $Obj('selwriter');
	var colorbt = $Obj('color');
	if (selsource) { selsource.onmousedown = function (e) { SelectSource(e); } }
	if (selwriter) { selwriter.onmousedown = function (e) { SelectWriter(e); } }
}
function OpenMyWin(surl) {
	var pos = GetWinPos(800,600);
	window.open(surl, "popUpMyWin", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left="+pos.left+", top="+pos.top);
}
function OpenMyWinCoOne(surl) {
	var pos = GetWinPos(800,600);
	window.open(surl, "popUpMyWin2", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left="+pos.left+",top="+pos.top);
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
//增加选择投票文档
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
		} else {
			newobj.style.display = 'block';
		}
	} else {
		if (newobj) newobj.style.display = 'none';
	}
}
function SelectSource(e) {
	LoadNewDiv(e, 'article_select_sw.php?t=source&k=8&rnd=' + Math.random(), 'mysource');
}
function SelectWriter(e) {
	LoadNewDiv(e, 'article_select_sw.php?t=writer&k=8&rnd=' + Math.random(), 'mywriter');
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
	} else {
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
	} else {
		newobj.style.display = "block";
	}
	if (newobj.innerHTML.length < 10) {
		fetch(surl).then(resp => resp.text()).then((d) => { newobj.innerHTML = d });
	}
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
	fetch(surl).then(resp => resp.text()).then((d) => {
		newobj.innerHTML = d;
	});
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
		} else {
			var posLeft = e.clientX - 20;
			var posTop = e.clientY + 30;
		}
	} else {
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
	newobj.style.minWidth = "480px";
	newobj.style.top = posTop + "px";
	newobj.style.left = posLeft + "px";
	newobj.innerHTML = '<img src="../../static/web/img/loadinglit.gif">';
	newobj.style.display = 'block';
	fetch(surl).then(resp => resp.text()).then((d) => {
		newobj.innerHTML = d;
	});
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
如果对象文档固定，用onmousedown=DropStart去除底下的DropStop
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
	} else {
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
	} else {
		var posLeft = event.pageX - 20;
		var posTop = event.pageY - 20;
	}
	obj.style.top = posTop + "px";
	obj.style.left = posLeft - mleftLeaning + "px";
}
//复制文档到剪切板
function copyToClipboard(txt) {
	if (txt == null || txt == '') {
		alert("没有选择任何文档");
		return;
	}
	if (window.clipboardData) {
		window.clipboardData.clearData();
		window.clipboardData.setData("Text", txt);
	} else if (navigator.userAgent.indexOf('Opera') != -1) {
		window.location = txt;
	} else {
		try {
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		} catch (e) {
			alert("被浏览器拒绝，请在浏览器地址栏输入about:config并回车\n然后将signed.applets.codebase_principal_support设置为true");
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
	//副栏目多选
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
	//主栏目单选
	else {
		if (selBox) {
			for (var i = 0; i < selBox.length; i++) {
				if (selBox[i].checked) selvalue = selBox[i].value;
			}
		}
		if (selvalue == '') {
			alert('您没有选中任何项目');
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
//Copyright 2020 The MuEMS Authors. All rights reserved.
//license that can be found in the LICENSE file.
//-----msgbox-------------------------------------
//生成一个随机id
function guid() {
	function S4() {
		return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
	}
	return (S4() + S4() + "-" + S4() + "-" + S4() + "-" + S4() + "-" + S4() + S4() + S4());
}
//函数会返回一个modalID，通过这个id可自已定义一些方法，这里用到了一个展开语法https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Operators/Spread_syntax
function ShowMsg(content, ...args) {
	title = "系统提示";
	size = "";
	if (typeof content == "undefined") content = "";
	modalID = guid();
	var footer = `<button type="button" class="btn btn-primary" onClick="CloseModal(\'GKModal${modalID}\')">ok</button>`;
	var noClose = false;
	if (args.length == 1) {
		//存在args参数
		if (typeof args[0].title !== 'undefined' && args[0].title != "") {
			title = args[0].title;
		}
		if (typeof args[0].footer !== 'undefined' && args[0].footer != "") {
			footer = args[0].footer;
		}
		if (typeof args[0].size !== 'undefined' && args[0].size != "") {
			size = args[0].size;
		}
		if (typeof args[0].noClose !== 'undefined' && args[0].noClose == true) {
			noClose = true;
		}
	}
	footer = footer.replaceAll("~modalID~", modalID);
	content = content.replaceAll("~modalID~", modalID);
	var modal = `<div id="GKModal${modalID}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="GKModalLabel${modalID}">
<div class="modal-dialog ${size}" role="document"><div class="modal-content">
<div class="modal-header"><h5 class="modal-title" id="GKModalLabel${modalID}">${title}</h5>`;
	if (!noClose) {
		modal += `<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span>&times;</span>
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
//隐藏并销毁modal
function CloseModal(modalID) {
	$("#" + modalID).modal('hide');
	$("#" + modalID).on('hidden.bs.modal', function (e) {
		if ($("#" + modalID).length > 0) {
			$("#" + modalID).remove();
		}
	})
}
//获取缩略图
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
		var dataUrl = $(this).cropper("getCroppedCanvas").toDataURL();
		litpicImg = dataUrl;
		$("#litPic").attr("src", litpicImg);
	},
	aspectRatio: 4 / 3,
	//拖动截取缩略图后，截取的缩略图更新到imageItems中
	cropend: function (data) {
		//这里的id要单独取出来
		var dataUrl = $(this).cropper("getCroppedCanvas").toDataURL();
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
function okImage(modalID) {
	uploadImage(litpicImg);
	$("#litPic").attr("src", litpicImg);
	CloseModal('GKModal' + modalID);
}
function useDefault(modalID) {
	uploadImage(litpicImgSrc);
	$("#litPic").attr("src", litpicImgSrc);
	CloseModal('GKModal' + modalID);
}
function uploadImage(litpicImgSrc) {
	const formData = new FormData()
	formData.append('litpic_b64', litpicImgSrc);
	fetch('archives_do.php?dopost=upload_base64_image', {
		method: 'POST',
		body: formData
	})
	.then(r => {
		if (r.ok) {
			return r.json()
		}
		throw new Error(errMsg);
	})
	.then(d => {
		if (d.code == 200) {
			$("#picname").val(d.data.image_url);
		}
	}).catch((error) => {
		alert("上传缩略图错误");
	});
}
$(document).ready(function () {
	$("#btnClearAll").click(function (event) {
		litpicImgSrc = "";
		litpicImg = "";
		$("#picname").val(litpicImg);
		$("#litPic").attr("src", "../../static/web/img/thumbnail.jpg");
	})
	//添加图片
	$("#iptAddImages").change(function (event) {
		var files = event.target.files;
		for (var i = 0, f; f = files[i]; i++) {
			//如果不是图片忽略
			if (!f.type.match('image.*')) {
				continue;
			}
			//将图片渲染到浏览器
			var reader = new FileReader();
			reader.onload = (function (theFile) {
				return function (e) {
					litpicImgSrc = e.target.result;
					if (cfg_uplitpic_cut == 'Y') {
						SetThumb(litpicImgSrc);
					} else {
						uploadImage(litpicImgSrc);
						$("#litPic").attr("src", litpicImgSrc);
					}
				};
			})(f);
			reader.readAsDataURL(f);
		}
		$("#iptAddImages").val("");
	});
	//截取缩略图
	function SetThumb(srcURL) {
		var footer =
			"<p><a href='javascript:useDefault(\"~modalID~\");' class='btn btn-success btn-sm'>使用原图</a><a href='javascript:okImage(\"~modalID~\")' class='btn btn-success btn-sm'>确定</a></p>";
		var optButton = `<p>
			  <label for="aspectRatio">比例</label>
			  <select id="aspectRatio" onchange="setAspectRatio(this.selectedIndex)">
				<option>16:9</option>
				<option selected>4:3</option>
				<option>1:1</option>
				<option>2:3</option>
				<option>自定义</option>
			  </select>
			</p>`;
		mdlCropperID = ShowMsg(
			'<div class="float-left" style="width:320px"><p><img id="cropImg~modalID~" src="' + srcURL + '"></p><p>宽度：<span id="cropWidth"></span>px，高度：<span id="cropHeight"></span>px</p>' + optButton + '</div><div class="pv float-right" style="width:200px;height:100px;overflow:hidden"></div>', {
			footer: footer,
			noClose: false,
			title: '缩略图裁剪',
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
				monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
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