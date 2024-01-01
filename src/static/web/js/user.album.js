var startNum = 1;
function MakeUpload(mnum) {
	var endNum = 0;
	var upfield = document.getElementById("uploadfield");
	var pnumObj = document.getElementById("picnum");
	var fhtml = '';
	var dsel = ' checked="checked" ';
	var dplay = 'display:none';
	if (mnum == 0) endNum = startNum + Number(pnumObj.value);
	else endNum = mnum;
	if (endNum > 120) endNum = 120;
	for (startNum;startNum < endNum;startNum++)
	{
		if (startNum == 1) {
			dsel = ' checked="checked" ';
			dplay = 'display:block';
		} else {
			dsel = ' ';
			dplay = 'display:none';
		}
		fhtml = '';
		fhtml += "<div class='mb-3'><label class='mb-0'><input type='checkbox' name='isokcheck" + startNum + "' id='isokcheck" + startNum + "' value='1' "+dsel+" onClick='CheckSelTable(" + startNum + ")'> 显示图片" + startNum + "上传框</label></div>";
		fhtml += "<div id=\"seltb" + startNum + "\" class='form-group' style=\"" + dplay + "\"><span>图片" + startNum + "上传：</span><div class='input-group mb-3'><input type='text' name='imgfile" + startNum + "' class='form-control' placeholder='请选择图片上传或填写图片地址'><div class='input-group-append'><span class='btn btn-success btn-sm btn-send' onClick=\"SelectImage('addcontent.imgfile" + startNum + "', 'big')\">选择</span></div></div><span>图片" + startNum + "简介：</span><textarea name='imgmsg" + startNum + "' class='form-control'></textarea></div>";
		upfield.innerHTML += fhtml;
	}
}
function checkMuList(psid, cmid) {
	if (document.getElementById("pagestyle3").checked) {
		document.getElementById("spagelist").style.display = "none";
	} else if (document.getElementById("pagestyle1").checked) {
		document.getElementById("spagelist").style.display = "block";
	} else {
		document.getElementById("spagelist").style.display = "none";
	}
}
function CheckSelTable(nnum) {
	var cbox = document.getElementById("isokcheck" + nnum);
	var seltb = document.getElementById("seltb" + nnum);
	if (!cbox.checked) seltb.style.display = "none";
	else seltb.style.display = "block";
}
function checkSubmit() {
	if (document.form1.title.value == '') {
		alert("文档标题不能为空");
		document.form1.title.focus();
		return false;
	}
	if (document.form1.typeid.value == 0) {
		alert("请您选择文档所属栏目");
		return false;
	}
}