function checkMuList(psid, cmid) {
	if ($Obj("pagestyle3").checked) {
		$Obj("cfgmulist").style.display = "table-row";
		$Obj("spagelist").style.display = "none";
	} else if ($Obj("pagestyle1").checked) {
		$Obj("cfgmulist").style.display = "none";
		$Obj("spagelist").style.display = "table-row";
	} else {
		$Obj("cfgmulist").style.display = "none";
		$Obj("spagelist").style.display = "none";
	}
}
function checkSubmitAlb() {
	if ($("#gallery .atlas").length > 0) {
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
function delAlbPic(pid) {
	var tgobj = $Obj("atlasok" + pid);
	fetch("swfupload.php?dopost=del&id=" + pid).then(resp => resp.text()).then((d) => {
		tgobj.innerHTML = d;
		$Obj("gallery").removeChild(tgobj);
	});
}
function delAlbPicOld(picfile, pid) {
	var tgobj = $Obj("albold" + pid);
	fetch("swfupload.php?dopost=delold&picfile=" + picfile).then(resp => resp.text()).then((d) => {
		tgobj.innerHTML = d;
		$Obj("galleryedit").removeChild(tgobj);
	});
}
function seePicNewAlb(f, imgdid, frname, hpos, acname) {
	var newobj = null;
	if (f.value == '') return;
	vImg = $Obj(imgdid);
	picnameObj = document.getElementById("picname");
	nFrame = $Nav() == $Obj(frname);
	nForm = f.form;
	if (nForm.detachEvent) nForm.detachEvent("onsubmit", checkSubmitAlb);
	else nForm.removeEventListener("submit", checkSubmitAlb, false);
	nForm.action = "archives_do.php";
	nForm.target = frname;
	nForm.dopost.value = "uploadLitpic";
	nForm.submit();
	picnameObj.value = '';
	newobj = $Obj("uploadwait");
	if (!newobj) {
		newobj = document.createElement("div");
		newobj.id = "uploadwait";
		newobj.style.position = "absolute";
		newobj.className = "uploadwait";
		newobj.style.width = 120;
		newobj.style.height = 20;
		newobj.style.top = hpos;
		newobj.style.left = 100;
		document.body.appendChild(newobj);
		newobj.innerHTML = '<img src="../../static/web/img/loadinglit.gif">';
	}
	newobj.style.display = "block";
	nForm.action = acname;
	nForm.dopost.value = "save";
	nForm.target = '';
	nForm.litpic.disabled = true;
}
var atlasimg = 0;
function addImage(src, pid) {
	var newImgDiv = document.createElement("div");
	var delstr = '';
	atlasimg++;
	if (pid != 0) {
		atlasimg = 'ok' + pid;
		delstr = '<div class="atlas-box"><a href="javascript:delAlbPic(' + pid + ')" class="btn btn-danger btn-sm">删除</a></p>';
	} else {
		atlasimg = 'err' + atlasimg;
	}
	newImgDiv.className = 'atlas';
	newImgDiv.id = 'atlas' + atlasimg;
	document.getElementById("gallery").appendChild(newImgDiv);
	newImgDiv.innerHTML = '<div class="atlas-head"><img src="' + src + '"></div>' + delstr;
	if (typeof arctype != 'undefined' && arctype == 'article') {
		if (pid != 0) {
			newImgDiv.innerHTML = '<div class="atlas-head"><img src="' + src + '" onclick="addtoEdit(' + pid + ')"></div>' + delstr;
		}
	}
	newImgDiv.innerHTML += '<div class="atlas-foot"><input type="text" name="picinfo' + atlasimg+ '" class="atlas-input" placeholder="请输入图片注释"></div>';
}