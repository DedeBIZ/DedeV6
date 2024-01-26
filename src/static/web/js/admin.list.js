function getCheckboxItem() {
	var allSel = '';
	if (document.form2.arcID.value) return document.form2.arcID.value;
	for (i = 0; i < document.form2.arcID.length; i++) {
		if (document.form2.arcID[i].checked) {
			if (allSel == '')
				allSel = document.form2.arcID[i].value;
			else
				allSel = allSel + "`" + document.form2.arcID[i].value;
		}
	}
	return allSel;
}
function getOneItem() {
	var allSel = '';
	if (document.form2.arcID.value) return document.form2.arcID.value;
	for (i = 0; i < document.form2.arcID.length; i++) {
		if (document.form2.arcID[i].checked) {
			allSel = document.form2.arcID[i].value;
			break;
		}
	}
	return allSel;
}
function selAll() {
	if (typeof document.form2.arcID.length === "undefined") {
		document.form2.arcID.checked = true;
	}
	for (i = 0; i < document.form2.arcID.length; i++) {
		if (!document.form2.arcID[i].checked) {
			document.form2.arcID[i].checked = true;
		}
	}
}
function noSelAll() {
	if (typeof document.form2.arcID.length === "undefined") {
		document.form2.arcID.checked = false;
	}
	for (i = 0; i < document.form2.arcID.length; i++) {
		if (document.form2.arcID[i].checked) {
			document.form2.arcID[i].checked = false;
		}
	}
}
function viewArc(aid) {
	if (aid == 0) aid = getOneItem();
	window.open("archives_do.php?aid=" + aid + "&dopost=viewArchives");
}
function kwArc(aid) {
	var qstr = getCheckboxItem();
	if (aid == 0) aid = getOneItem();
	if (qstr == '') {
		ShowMsg('请选择一个或多个文档');
		return;
	}
	location="archives_do.php?aid=" + aid + "&dopost=makekw&qstr=" + qstr;
}
function editArc(aid) {
	if (aid == 0) aid = getOneItem();
	location="archives_do.php?aid=" + aid + "&dopost=editArchives";
}
function updateArc(aid) {
	var qstr = getCheckboxItem();
	if (aid ==  0) aid = getOneItem();
	location = "archives_do.php?aid=" + aid + "&dopost=makeArchives&qstr=" + qstr;
}
function checkArc(aid) {
	var qstr = getCheckboxItem();
	if (aid ==  0) aid = getOneItem();
	location = "archives_do.php?aid=" + aid + "&dopost=checkArchives&qstr=" + qstr;
}
function moveArc(e, obj, cid){
	var qstr = getCheckboxItem();
	if (qstr == '') {
		ShowMsg('请选择一个或多个文档');
		return;
	}
	LoadQuickDiv(e, 'archives_do.php?dopost=moveArchives&qstr=' + qstr + '&channelid=' + cid + '&rnd=' + Math.random(), 'moveArchives', 'auto', '300px');
	ChangeFullDiv('show');
}
function adArc(aid) {
	var qstr = getCheckboxItem();
	if (aid == 0) aid = getOneItem();
	location = "archives_do.php?aid=" + aid + "&dopost=commendArchives&qstr=" + qstr;
}
function cAtts(jname, e, obj) {
	var qstr = getCheckboxItem();
    var screeheight = document.body.clientHeight + 20;
	if (qstr == '') {
		ShowMsg('请选择一个或多个文档');
		return;
	}
	LoadQuickDiv(e, 'archives_do.php?dopost=attsDlg&qstr=' + qstr + '&dojob=' + jname + '&rnd=' + Math.random(), 'attsDlg', 'auto', '300px');
	ChangeFullDiv('show', screeheight);
}
function delArc(aid) {
	var qstr = getCheckboxItem();
	if (aid == 0) aid = getOneItem();
	location = "archives_do.php?qstr=" + qstr + "&aid=" + aid + "&dopost=delArchives";
}
function QuickEdit(aid, e, obj) {
	LoadQuickDiv(e, 'archives_do.php?dopost=quickEdit&aid=' + aid + '&rnd=' + Math.random(), 'quickEdit', 'auto', '300px');
	ChangeFullDiv('show');
}