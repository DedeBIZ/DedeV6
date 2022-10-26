/**
 * 
 * @version        $Id: handlers.js 1 22:28 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
var albImg = 0;
function addImage(src, pid) {
	var newImgDiv = document.createElement("div");
	var delstr = '';
	var iptwidth = 160;
	albImg++;
	if (pid != 0) {
		albImg = 'ok' + pid;
		delstr = '<a href="javascript:delAlbPic(' + pid + ')" class="btn btn-danger btn-sm">删除</a>';
	} else {
		albImg = 'err' + albImg;
	}
	newImgDiv.className = 'albCt';
	newImgDiv.id = 'albCt' + albImg;
	document.getElementById("thumbnails").appendChild(newImgDiv);
	newImgDiv.innerHTML = '<img src="' + src + '">' + delstr;
	if (typeof arctype != 'undefined' && arctype == 'article') {
		iptwidth = 100;
		if (pid != 0) {
			newImgDiv.innerHTML = '<img src="' + src + '" onClick="addtoEdit(' + pid + ')">' + delstr;
		}
	}
	newImgDiv.innerHTML += '<div class="mt-1">名称：<input type="text" name="picinfo' + albImg+ '" value="" style="width:' + iptwidth + 'px"></div>';
}