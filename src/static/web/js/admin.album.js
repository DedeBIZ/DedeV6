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
				"txt": $(this).find("input").val(),
			});
		});
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
var atlasimg = 0;
function addImage(src, pid) {
	var newImgDiv = document.createElement("div");
	var delstr = '';
	atlasimg++;
	if (pid != 0) {
		atlasimg = 'ok' + pid;
		delstr = '<div class="atlas-body"><a href="javascript:delAlbPic(' + pid + ');" class="btn btn-danger btn-sm">删除</a></p>';
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
	newImgDiv.innerHTML += '<div class="atlas-body"><input type="text" name="picinfo' + atlasimg+ '" class="atlas-input" placeholder="请输入图片注释"></div>';
}