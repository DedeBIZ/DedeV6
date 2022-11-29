function selAll()
{
	var celements = document.getElementsByName('aids[]');
	for (i=0;i<celements.length;i++) {
		if (!celements[i].checked) celements[i].checked = true;
		else celements[i].checked = false;
	}
}
function noselAll()
{
	var celements = document.getElementsByName('aids[]');
	for (i=0;i<celements.length;i++) {
		if (celements[i].checked = true)  {
			celements[i].checked = false;
		}
	}
}
//批量删除搜多关键字
function delall()
{
	DedeConfirm("您确定要删除选定的关键字吗？").then((v)=>{
		document.form3.dopost.value = 'delall';
		document.form3.submit();
	}).catch((e)=>{
		console.log(e)
	});
}