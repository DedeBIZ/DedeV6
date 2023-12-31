var BROWSER = {};
if (BROWSER.safari) {
	BROWSER.firefox = true;
}
function LoadSuns(ctid, tid) {
	if ($DE(ctid).innerHTML.length < 10) {
		$DE('icon' + tid).className = 'fa fa-minus-square';
		fetch('catalog_do.php?dopost=GetSunLists&cid=' + tid).then(resp => resp.text()).then((d) => {
			$DE(ctid).innerHTML = d;
		});
	} else {
		showHide(ctid, tid);
	}
}
function showHide(objname, tid) {
	if ($DE(objname).style.display == "none") {
		$DE('icon' + tid).className = 'fa fa-minus-square';
		$DE(objname).style.display = BROWSER.firefox ? "" : "block";
	} else {
		$DE('icon' + tid).className = 'fa fa-plus-square';
		$DE(objname).style.display = "none";
	}
}