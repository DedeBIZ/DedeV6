function checkSubmit()
{
	if(document.addcontent.title.value=='') {
		ShowMsg(dedeLang("user_err_album_title_isempty"));
		document.addcontent.title.focus();
		return false;
	}
	if(document.addcontent.typeid.value==0) {
		ShowMsg(dedeLang("user_err_typeid_isempty"));
		return false;
	}
	if(document.addcontent.typeid.options[document.addcontent.typeid.selectedIndex].className!='option3')
	{
		ShowMsg(dedeLang("user_err_typeid_isnotsame"));
		return false;
	}
	return true;
}
function CheckSelTable(nnum){
	var cbox = document.getElementById('isokcheck'+nnum);
	var seltb = document.getElementById('seltb'+nnum);
	if(!cbox.checked) seltb.style.display = 'none';
	else seltb.style.display = 'block';
}
var startNum = 1;
function MakeUpload(mnum)
{
	var endNum = 0;
	var upfield = document.getElementById("uploadfield");
	var pnumObj = document.getElementById("picnum");
	var fhtml = "";
	var dsel = " checked='checked' ";
	var dplay = "display:none";
	if(mnum==0) endNum = startNum + Number(pnumObj.value);
	else endNum = mnum;
	if(endNum>120) endNum = 120;
	for(startNum;startNum < endNum;startNum++)
	{
		if(startNum==1){
			dsel = "checked='checked'";
			dplay = "block";
		} else {
			dsel = " ";
			dplay = " ";
		}
		fhtml = '';
		fhtml += "<div id=\"seltb"+startNum+"\" style=\"display:"+dplay+"\">";
		fhtml += "<label class='d-block mt-1 mb-1'><input type='checkbox' name='isokcheck"+startNum+"' value='1' id='isokcheck"+startNum+"' "+dsel+" onClick='CheckSelTable("+startNum+")'><span class='pl-1'>"+dedeLang('user_content_alumb_isokcheck',{startNum:startNum})+"</span></label>";
		fhtml += "<label class='d-block mt-1 mb-1'>"+dedeLang('user_imgfile')+"：</label><input type='text' name='imgfile"+startNum+"' class='form-control d-inline-block w-50' placeholder=\""+dedeLang('user_imgfile_tip')+"\"><button type='button' class='btn btn-success btn-sm ml-2'>选择</button>";
		fhtml += "<label class='d-block mt-1 mb-1'>"+dedeLang('user_image_desc')+"：</label><textarea name='imgmsg"+startNum+"' class='form-control'></textarea>";
		fhtml += "</div>";
		upfield.innerHTML += fhtml;
	}
}
function checkMuList(psid,cmid)
{
	if(document.getElementById('pagestyle3').checked)
	{
		document.getElementById('spagelist').style.display = 'none';
	}
	else if(document.getElementById('pagestyle1').checked)
	{
		document.getElementById('spagelist').style.display = 'block';
	} else {
		document.getElementById('spagelist').style.display = 'none';
	}
}
//图集，显示与隐藏zip文件选项
function ShowZipField(formitem,zipid,upid)
{
	if(formitem.checked){
		document.getElementById(zipid).style.display = 'block';
		document.getElementById(upid).style.display = 'none';
		document.getElementById('formhtml').checked = false;
		document.getElementById('copyhtml').innerHTML = '';
	} else {
		document.getElementById(zipid).style.display = 'none';
	}
}