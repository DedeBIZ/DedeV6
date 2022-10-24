dedeReplace = function(s, d, str) {var p = new RegExp(s, 'g'); return str.replace(p, d);}
dedeLang = function(key, arr) {
	var r = dlang[key] ? dlang[key] : "lang["+key+"]";
	if(arr) {
        for (const k in arr) {
            if (Object.hasOwnProperty.call(arr, k)) {
                const v = arr[k];
                r = dedeReplace("{{"+k+"}}", v, r);
            }
        }	
	}
	return r;
};