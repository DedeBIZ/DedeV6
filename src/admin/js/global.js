var BROWSER = {};
var USERAGENT = navigator.userAgent.toLowerCase();
browserVersion({'firefox':'','chrome':'','opera':'','safari':'','maxthon':'','mozilla':'','webkit':''});
if (BROWSER.safari) {
	BROWSER.firefox = true;
}
BROWSER.opera = BROWSER.opera ? opera.version() : 0;
function browserVersion(types) {
	var other = 1;
	for (i in types) {
		var v = types[i] ? types[i] : i;
		if (USERAGENT.indexOf(v) != -1) {
			var re = new RegExp(v + '(\\/|\\s)([\\d\\.]+)', 'ig');
			var matches = re.exec(USERAGENT);
			var ver = matches != null ? matches[2] : 0;
			other = ver !== 0 ? 0 : other;
		} else {
			var ver = 0;
		}
		eval('BROWSER.' + i + '= ver');
	}
	BROWSER.other = other;
}
function LoadSuns(ctid, tid) {
	if ($DE(ctid).innerHTML.length < 10) {
		$DE('img' + tid).className = 'fa fa-minus-square';
		fetch('catalog_do.php?dopost=GetSunLists&cid=' + tid).then(resp => resp.text()).then((d) => {
			$DE(ctid).innerHTML = d;
		});
	} else {
		showHide(ctid, tid);
	}
}
function showHide(objname, tid) {
	if ($DE(objname).style.display == "none") {
		$DE('img' + tid).className = 'fa fa-minus-square';
		$DE(objname).style.display = BROWSER.firefox ? "" : "block";
	} else {
		$DE('img' + tid).className = 'fa fa-plus-square';
		$DE(objname).style.display = "none";
	}
}