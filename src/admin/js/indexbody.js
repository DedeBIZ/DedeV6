function AddNew()
{
    $DE('addTab').style.display = 'block';
}

function CloseTab(tb)
{
    $DE(tb).style.display = 'none';
}

function ListAll(){
    $DE('editTab').style.display = 'block';
	fetch('index_body.php?dopost=editshow').then(resp=>resp.text()).then((d)=>{
		$DE('editTabBody').innerHTML = d;
	});
}

function ShowWaitDiv(){
    $DE('loaddiv').style.display = 'block';
    return true;
}

window.onload = function()
{
	fetch('index_body.php?dopost=getRightSide').then(resp=>resp.text()).then((d)=>{
		$DE('listCount').innerHTML = d;
	});
};
