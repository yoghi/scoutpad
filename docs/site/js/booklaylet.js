/*
 * Booklaylet: Overlay Bookmarklet (iframe)
 * 
 * Copyright (C) 2008 by Davide 'Folletto' Casali
 * Creative Commons (CC) by-sa.
 *
 * Fx 2.0-3.0, Safari 2.0-3.0, Opera 9.0: tested
 * IE7: not working, bugged 
 * IE6: not working, too big (>488/508)
 */

function showLet(s, h) { 
	var d = document; var x = d.createElement("div");
	x.id = "__dd";
	x.innerHTML = '<div style="margin: 0 40px">'
		+ '<style>'
		+ '#__dd { position: fixed; bottom: 0; left: 0; width: 100%; }'
		+ '#__dd a { display: block; margin: 0 -2px 0 0; height: 6px; background: #ccc; }'
		+ '#__dd a:hover { background: #e00; }'
		+ '#__dd iframe { border: 1px solid #ccc; width: 100%; height: ' + h + 'px; }'
		+ '</style>'
		+ '<a href="javascript:void()" onclick="document.getElementById(\'__dd\').parentNode.removeChild(document.getElementById(\'__dd\'))"></a>'
		+ '<iframe src="' + s + '" frameborder="0"></iframe>'
	  + '</div>';
	d.body.appendChild(x);
}

/* ("http://www.google.com", "450") */
