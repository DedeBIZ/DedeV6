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
		newobj = document.createElement("div");
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
function SelectMedia(fname) {
	var pos = GetWinPos(800,600);
	window.open("./dialog/select_media.php?f=" + fname + "&noeditor=yes", "popUpFlashWin", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left=" + pos.left + ", top=" + pos.top);
}
function SelectSoft(fname) {
	var pos = GetWinPos(800,600);
	window.open("./dialog/select_soft.php?f=" + fname+ "&noeditor=yes", "popUpImagesWin", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left=" + pos.left + ", top=" + pos.top);
}
function SelectImage(fname, stype, imgsel="") {
	var pos = GetWinPos(800,600);
	if (!fname) fname = 'form1.picname';
	if (imgsel) imgsel = '&noeditor=yes';
	if (!stype) stype = 'small';
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
function OpenMyWin(surl) {
	var pos = GetWinPos(800,600);
	window.open(surl, "popUpMyWin", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left=" + pos.left + ", top=" + pos.top);
}
function InitPage() {
	var selsource = $Obj('selsource');
	var selwriter = $Obj('selwriter');
	var colorbt = $Obj('color');
	if (selsource) { selsource.onmousedown = function(e) { SelectSource(e); } }
	if (selwriter) { selwriter.onmousedown = function(e) { SelectWriter(e); } }
}
function $Nav() {
	if (window.navigator.userAgent.indexOf("Firefox") >= 1) return 'FF';
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
function ShowObj(objname) {
	var obj = $Obj(objname);
	if (obj == null) return false;
	obj.style.display = "table-row";
}
function ShowObjRow(objname) {
	var obj = $Obj(objname);
	obj.style.display = "table-row";
}
function AddTypeid2() {
	ShowObjRow('typeid2tr');
}
function HideObj(objname) {
	var obj = $Obj(objname);
	if (obj == null) return false;
	obj.style.display = "none";
}
function SeePic(img, f) {
	if (f.value != '') img.src = f.value;
}
function PutSource(str) {
	var osource = $Obj('source');
	if (osource) osource.value = str;
	$Obj("mysource").style.display = "none";
	ChangeFullDiv("hide");
}
function PutWriter(str) {
	var owriter = $Obj("writer");
	if (owriter) owriter.value = str;
	$Obj("mywriter").style.display = "none";
	ChangeFullDiv("hide");
}
function ClearDivCt(objname) {
	if (!$Obj(objname)) return;
	$Obj(objname).innerHTML = "";
	$Obj(objname).style.display = "none";
	ChangeFullDiv("hide");
}
function ChangeFullDiv(showhide, screenheigt) {
	var newobj = $Obj("fullpagediv");
	if (showhide == "show") {
		if (!newobj) {
			newobj = document.createElement("div");
			newobj.id = "fullpagediv";
			newobj.style.position = "absolute";
			newobj.className = "fullpagediv";
			newobj.style.height = document.body.clientHeight + 50 + "px";
			document.body.appendChild(newobj);
		} else {
			newobj.style.display = "block";
		}
	} else {
		if (newobj) newobj.style.display = "none";
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
	posLeft = posLeft - 100;
	var newobj = $Obj(oname);
	if (!newobj) {
		newobj = document.createElement("div");
		newobj.id = oname;
		newobj.style.position = "absolute";
		newobj.className = oname;
		newobj.className += " dlgws";
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
		newobj = document.createElement("div");
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
function LoadQuickDiv(e, surl, oname, w, h) {
	var newobj = $Obj(oname);
	if (!newobj) {
		newobj = document.createElement("div");
		newobj.id = oname;
		newobj.style.position = 'fixed';
		newobj.className = 'pubdlg';
		newobj.style.width = w;
		newobj.style.height = h + 30;
		document.body.appendChild(newobj);
	}
	newobj.style.top = "50%";
	newobj.style.left = "50%";
	newobj.style.display = 'block';
	newobj.style.transform = "translate(-50%, -201px)";
	newobj.innerHTML = '<img src="../../static/web/img/loadinglit.gif">';
	fetch(surl).then(resp => resp.text()).then((d) => {
		newobj.innerHTML = d;
	});
}
function ShowCatMap(e, obj, cid, targetId, oldvalue) {
	LoadQuickDiv(e, 'archives_do.php?dopost=getCatMap&targetid=' + targetId + '&channelid=' + cid + '&oldvalue=' + oldvalue + '&rnd=' + Math.random(), 'getCatMap', '700px', '500px');
	ChangeFullDiv('show');
}
function getSelCat(targetId) {
	var selBox = document.quicksel.seltypeid;
	var targetObj = $Obj(targetId);
	var selvalue = '';
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
	} else {
		if (selBox) {
			for (var i = 0; i < selBox.length; i++) {
				if (selBox[i].checked) selvalue = selBox[i].value;
			}
		}
		if (selvalue == '') {
			alert('您没有选中任何栏目');
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
//生成一个随机id
function guid() {
	function S4() {
		return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
	}
	return (S4() + S4() + "-" + S4() + "-" + S4() + "-" + S4() + "-" + S4() + S4() + S4());
}
var _DedeConfirmFuncs = {};
var _DedeConfirmFuncsClose = {};
function __DedeConfirmRun(modalID) {
    _DedeConfirmFuncs[modalID]();
}
function __DedeConfirmRunClose(modalID) {
    _DedeConfirmFuncsClose[modalID]();
}
function DedeConfirm(content = "", title = "确认提示") {
    let modalID = guid();
    return new Promise((resolve, reject) => {
        _DedeConfirmFuncs[modalID] = ()=>{
            resolve("success");
            CloseModal(`DedeModal${modalID}`);
        }
        _DedeConfirmFuncsClose[modalID] = ()=>{
            reject("cancel");
            CloseModal(`DedeModal${modalID}`);
        }
        let footer = `<button type="button" class="btn btn-outline-success btn-sm" onclick="__DedeConfirmRunClose(\'${modalID}\')">取消</button><button type="button" class="btn btn-success btn-sm" onclick="__DedeConfirmRun(\'${modalID}\')">确定</button>`;
        let modal = `<div id="DedeModal${modalID}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="DedeModalLabel${modalID}"><div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header"><h6 class="modal-title" id="DedeModalLabel${modalID}">${title}</h6>`;
        modal += `<button type="button" class="update-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>`;
        modal += `</div><div class="modal-body">${content}</div><div class="modal-footer">${footer}</div></div></div></div>`;
        $("body").append(modal)
        $("#DedeModal" + modalID).modal({
            backdrop: 'static',
            show: true
        });
        $("#DedeModal" + modalID).on('hidden.bs.modal', function(e) {
            $("#DedeModal" + modalID).remove();
        })
    })
}
//函数会返回一个modalID，通过这个id可自已定义一些方法，这里用到了一个展开语法：https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Operators/Spread_syntax
function ShowMsg(content, ...args) {
	title = "系统提示";
	size = "";
	if (typeof content == "undefined") content = "";
	modalID = guid();
	var footer = `<button type="button" class="btn btn-primary btn-sm" onclick="CloseModal(\'GKModal${modalID}\')">确定</button>`;
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
	var modal = `<div id="GKModal${modalID}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="GKModalLabel${modalID}"><div class="modal-dialog ${size}" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="GKModalLabel${modalID}">${title}</h5>`;
	if (!noClose) {
		modal += `<button type="button" class="update-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>`;
	}
	modal += `</div><div class="modal-body">${content}</div><div class="modal-footer">${footer}</div></div></div></div>`;
	$("body").append(modal)
	$("#GKModal" + modalID).modal({
		backdrop: 'static',
		show: true
	});
	$("#GKModal" + modalID).on('hidden.bs.modal', function(e) {
		$("#GKModal" + modalID).remove();
	})
	return modalID;
}
//隐藏并销毁modal
function CloseModal(modalID) {
	$("#" + modalID).modal('hide');
	$("#" + modalID).on('hidden.bs.modal', function(e) {
		if ($("#" + modalID).length > 0) {
			$("#" + modalID).remove();
		}
	})
}
//获取缩略图
var litpicImgSrc = "";
var litpicImg = "";
var mdlCropperID = "";
var optCropper = {
	preview: ".pv",
	crop: function(e) {
		$("#cropWidth").text(Math.round(e.detail.height));
		$("#cropHeight").text(Math.round(e.detail.width));
		if ($(this).cropper("getCroppedCanvas")) {
			var dataUrl = $(this).cropper("getCroppedCanvas").toDataURL();
			litpicImg = dataUrl;
			$("#litPic").attr("src", litpicImg);	
		}
	},
	aspectRatio: 4 / 3,
	cropend: function(data) {
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
function SetThumb(srcURL) {
	var footer = "<p><a href='javascript:useDefault(\"~modalID~\");' class='btn btn-success btn-sm'>使用原图</a><a href='javascript:okImage(\"~modalID~\")' class='btn btn-success btn-sm'>确定</a></p>";
	var optButton = `<p><label for="aspectRatio">比例</label><select id="aspectRatio" onchange="setAspectRatio(this.selectedIndex)"><option>16:9</option><option selected>4:3</option><option>1:1</option><option>2:3</option><option>自定义</option></select></p>`;
	mdlCropperID = ShowMsg('<div class="float-left" style="width:320px"><p><img id="cropImg~modalID~" src="' + srcURL + '"></p><p>宽度：<span id="cropWidth"></span>px，高度：<span id="cropHeight"></span>px</p>' + optButton + '</div><div class="pv float-right" style="width:200px;height:100px;overflow:hidden"></div>', {
		footer: footer,
		noClose: false,
		title: '图片裁剪',
	});
	setTimeout(function() {
		$("#cropImg" + mdlCropperID).cropper(optCropper);
	}, 500);
}
$(document).ready(function() {
	$("#togglemenu").click(function() {
		if ($("body").attr("class") == "showmenu") {
			$("body").attr("class", "hidemenu");
			$(this).html('<i class="fa fa-indent"></i>');
		} else {
			$("body").attr("class", "showmenu");
			$(this).html('<i class="fa fa-dedent"></i>');
		}
	});
	$("#btnClearAll").click(function(event) {
		litpicImgSrc = "";
		litpicImg = "";
		$("#picname").val(litpicImg);
		$("#litPic").attr("src", "/static/web/img/thumbnail.jpg");
	})
	$("#iptAddImages").change(function(event) {
		var files = event.target.files;
		for (var i = 0, f; f = files[i]; i++) {
			if (!f.type.match('image.*')) {
				continue;
			}
			var reader = new FileReader();
			reader.onload = (function(theFile) {
				return function(e) {
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
		}, function(start) {
			$(this).val(start.format("YYYY-MM-DD HH:mm:ss"));
		});
		$('.datepicker').on('show.daterangepicker', function(ev, picker) {
			if (picker.element.offset().top - $(window).scrollTop() + picker.container.outerHeight() > $(window).height()) {
				picker.drops = 'up';
			} else {
				picker.drops = 'down';
			}
			picker.move();
		})
	}
});