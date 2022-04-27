/**
 * 图集
 *
 * @version        $Id: album.js 1 22:28 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
function checkSubmitAlb() {
	if (document.form1.title.value == '') {
		ShowMsg("标题不能为空");
		return false;
	}
	if (document.form1.typeid.value == 0) {
		ShowMsg("请选择主类别");
		return false;
	}
	document.form1.imagebody.value = $Obj('copyhtml').innerHTML;
	if ($("#thumbnails .albCt").length > 0) {
		//这里从thumbnails中取出图片元素信息
		$("#thumbnails .albCt").each(function () {
			albums.push({
				"img": $(this).find("img").attr("src"),
				"txt": $(this).find("input").val()
			})
		})
	}
	$("#albums").val(JSON.stringify(albums));
	return true;
}
function testGet() {
	LoadTestDiv();
}
function checkMuList(psid, cmid) {
	if ($Obj('pagestyle3').checked) {
		$Obj('cfgmulist').style.display = 'block';
		$Obj('spagelist').style.display = 'none';
	} else if ($Obj('pagestyle1').checked) {
		$Obj('cfgmulist').style.display = 'none';
		$Obj('spagelist').style.display = 'block';
	} else {
		$Obj('cfgmulist').style.display = 'none';
		$Obj('spagelist').style.display = 'none';
	}
}
//图集，显示与隐藏zip文件选项
function showZipField(formitem, zipid, upid) {
	if (formitem.checked) {
		$Obj(zipid).style.display = 'block';
		$Obj(upid).style.display = 'none';
		$Obj('copyhtml').innerHTML = '';
	} else {
		$Obj(zipid).style.display = 'none';
	}
}
//图集，显示与隐藏Html编辑框
function showHtmlField(formitem, htmlid, upid) {
	if ($Nav() != "IE") {
		alert("该方法不适用于非IE浏览器");
		return;
	}
	if (formitem.checked) {
		$Obj(htmlid).style.display = 'block';
		$Obj(upid).style.display = 'none';
		$Obj('formzip').checked = false;
	} else {
		$Obj(htmlid).style.display = 'none';
		$Obj('copyhtml').innerHTML = '';
	}
}
function seePicNewAlb(f, imgdid, frname, hpos, acname) {
	var newobj = null;
	if (f.value == '') return;
	vImg = $Obj(imgdid);
	picnameObj = document.getElementById('picname');
	nFrame = $Nav() == 'IE' ? eval('document.frames.' + frname) : $Obj(frname);
	nForm = f.form;
	//修改form的action等参数
	if (nForm.detachEvent) nForm.detachEvent("onsubmit", checkSubmitAlb);
	else nForm.removeEventListener("submit", checkSubmitAlb, false);
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
		document.body.appendChild(newobj);
		newobj.innerHTML = '<img src="../../static/web/img/loadinglit.gif" alit="" />上传中...';
	}
	newobj.style.display = 'block';
	//提交后还原form的action等参数
	nForm.action = acname;
	nForm.dopost.value = 'save';
	nForm.target = '';
	nForm.litpic.disabled = true;
}
//删除已经上传的图片
function delAlbPic(pid) {
	var tgobj = $Obj('albCtok' + pid);
	fetch('swfupload.php?dopost=del&id=' + pid).then(resp=>resp.text()).then((d)=>{
		tgobj.innerHTML = d;
		$Obj('thumbnails').removeChild(tgobj);
	});
}
//删除已经上传的图片，编辑时用
function delAlbPicOld(picfile, pid) {
	var tgobj = $Obj('albold' + pid);
	fetch('swfupload.php?dopost=delold&picfile=' + picfile).then(resp=>resp.text()).then((d)=>{
		tgobj.innerHTML = d;
		$Obj('thumbnailsEdit').removeChild(tgobj);
	});
}