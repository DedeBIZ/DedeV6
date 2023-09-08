function checkSubmitAlb() {
	if ($("#gallery .atlas").length > 0) {
		//这里从gallery中取出图片元素信息
		$("#gallery .atlas").each(function() {
			albums.push({
				"img": $(this).find("img").attr("src"),
				"txt": $(this).find("input").val()
			})
		})
	}
	$("#albums").val(JSON.stringify(albums));
	return true;
}
function checkMuList(psid, cmid) {
	if ($Obj('pagestyle3').checked) {
		$Obj('cfgmulist').style.display = 'table-row';
		$Obj('spagelist').style.display = 'none';
	} else if ($Obj('pagestyle1').checked) {
		$Obj('cfgmulist').style.display = 'none';
		$Obj('spagelist').style.display = 'table-row';
	} else {
		$Obj('cfgmulist').style.display = 'none';
		$Obj('spagelist').style.display = 'none';
	}
}
//删除已经上传的图片
function delAlbPic(pid) {
	var tgobj = $Obj('atlasok' + pid);
	fetch('swfupload.php?dopost=del&id=' + pid).then(resp => resp.text()).then((d) => {
		tgobj.innerHTML = d;
		$Obj('gallery').removeChild(tgobj);
	});
}
//删除已经上传的图片修改时用
function delAlbPicOld(picfile, pid) {
	var tgobj = $Obj('albold' + pid);
	fetch('swfupload.php?dopost=delold&picfile=' + picfile).then(resp => resp.text()).then((d) => {
		tgobj.innerHTML = d;
		$Obj('galleryedit').removeChild(tgobj);
	});
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
		newobj = document.createElement("div");
		newobj.id = 'uploadwait';
		newobj.style.position = 'absolute';
		newobj.className = 'uploadwait';
		newobj.style.width = 120;
		newobj.style.height = 20;
		newobj.style.top = hpos;
		newobj.style.left = 100;
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