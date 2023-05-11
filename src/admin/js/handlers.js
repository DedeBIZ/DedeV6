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
			newImgDiv.innerHTML = '<div class="atlas-head"><img src="' + src + '" onClick="addtoEdit(' + pid + ')"></div>' + delstr;
		}
	}
	newImgDiv.innerHTML += '<div class="atlas-foot"><input type="text" name="picinfo' + atlasimg+ '" class="atlas-input" placeholder="请输入图片注释"></div>';
}