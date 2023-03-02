function checkSubmit()
{
	if (document.form1.title.value=='') {
		alert("文档标题不能为空");
		document.form1.title.focus();
		return false;
	}
	if (document.form1.typeid.value==0) {
		alert("请您选择文档所属栏目");
		return false;
	}
	document.form1.imagebody.value = document.getElementById('copyhtml').innerHTML;
	document.getElementById('postloader').style.display = 'block';
}
function CheckSelTable(nnum){
	var cbox = document.getElementById('isokcheck'+nnum);
	var seltb = document.getElementById('seltb'+nnum);
	if (!cbox.checked) seltb.style.display = 'none';
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
	if (mnum==0) endNum = startNum + Number(pnumObj.value);
	else endNum = mnum;
	if (endNum>120) endNum = 120;
	for (startNum;startNum < endNum;startNum++)
	{
		if (startNum==1) {
			dsel = " checked='checked' ";
			dplay = "block";
		} else {
			dsel = " ";
			dplay = "display:none";
		}
		fhtml = '';
		fhtml += "<div><label><input type='checkbox' name='isokcheck"+startNum+"' id='isokcheck"+startNum+"' value='1' "+dsel+" onClick='CheckSelTable("+startNum+")'> 显示图片"+startNum+"上传框</label></div>";
		fhtml += "<div id=\"seltb"+startNum+"\" style=\""+dplay+"\">";
		fhtml += "<p>图片"+startNum+"：<input type='text' name='imgfile"+startNum+"' class='form-control w-50 mr-2' placeholder='请输入网址'></p>";
		fhtml += "<p>图片简介：<textarea name='imgmsg"+startNum+"' class='form-control'></textarea></p>";
		fhtml += "</div>";
		upfield.innerHTML += fhtml;
	}
}
function TestGet()
{
	LoadTestDiv();
}
var vcc = 0;
function LoadTestDiv()
{
	var posLeft = 100; var posTop = 100;
	var newobj = document.getElementById('_myhtml');
	document.getElementById('imagebody').value = document.getElementById('copyhtml').innerHTML;
	var dfstr = '粘贴到这里...';
	if (document.getElementById('imagebody').value.length <= dfstr.length)
	{
		alert('您还没有粘贴任何东西在修改框');
		return;
	}
	if (!newobj){
		newobj = document.createElement("DIV");
		newobj.id = '_myhtml';
		newobj.style.position='absolute';
		newobj.className = "dlg2";
		newobj.style.top = posTop;
		newobj.style.left = posLeft;
		document.body.appendChild(newobj);
	} else{
		newobj.style.display = "block";
	}
	const formData = new FormData()
	formData.append('myhtml', v);
	formData.append('vcc', vcc);
	fetch('album_testhtml.php', {
		method: 'POST',
		body: formData
	})
	.then(r => r.text())
	.then(d => {
		newobj.innerHTML = d;
		vcc++;
	})
}
function checkMuList(psid,cmid)
{
	if (document.getElementById('pagestyle3').checked)
	{
		document.getElementById('spagelist').style.display = 'none';
	}
	else if (document.getElementById('pagestyle1').checked)
	{
		document.getElementById('spagelist').style.display = 'block';
	} else {
		document.getElementById('spagelist').style.display = 'none';
	}
}
//图片显示与隐藏zip文件选项
function ShowZipField(formitem,zipid,upid)
{
	if (formitem.checked){
		document.getElementById(zipid).style.display = 'block';
		document.getElementById(upid).style.display = 'none';
		document.getElementById('formhtml').checked = false;
		document.getElementById('copyhtml').innerHTML = '';
	} else {
		document.getElementById(zipid).style.display = 'none';
	}
}
//图片显示与隐藏修改框
function ShowHtmlField(formitem,htmlid,upid)
{
	if ($Nav()!="IE"){
		alert("该方法不适用于非IE浏览器");
		return ;
	}
	if (formitem.checked){
		document.getElementById(htmlid).style.display = 'block';
	} else {
		document.getElementById(htmlid).style.display = 'none';
		document.getElementById('copyhtml').innerHTML = '';
	}
}