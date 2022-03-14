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
    var myajax = new DedeAjax($DE('editTabBody'));
    myajax.SendGet('index_body.php?dopost=editshow');
}

function ShowWaitDiv(){
    $DE('loaddiv').style.display = 'block';
    return true;
}

window.onload = function()
{
    var myajax = new DedeAjax($DE('listCount'));
    myajax.SendGet('index_body.php?dopost=getRightSide');
};
