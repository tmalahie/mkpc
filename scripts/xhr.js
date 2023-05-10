function xhr(page, send, onload, backoff) {
	if (!backoff)
		backoff = 1000;
	var xhr_object;
	if (window.XMLHttpRequest || window.ActiveXObject) {
		if (window.ActiveXObject) {
			try {
				xhr_object = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch(e) {
				xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
			}
		}
		else
			xhr_object = new XMLHttpRequest(); 
	}
	xhr_object.open("POST", "api/"+page, true);
	xhr_object.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr_object.setRequestHeader("If-Modified-Since", "Wed, 15 Nov 1995 00:00:00 GMT");
	try {
		xhr_object.onload = function () {
			if (xhr_object.status == 200) {
				if (!onload(xhr_object.responseText)) {
					setTimeout(function() {
						xhr(page,send,onload,backoff*2);
					}, backoff);
				}
			}
			else
				xhr_object.onerror();
		};
		xhr_object.onerror = function () {
			setTimeout(function() {
				xhr(page,send,onload, backoff*2);
			}, backoff);
		};
	}
	catch (e) {
		xhr_object.onreadystatechange = function () {
			if ((xhr_object.readyState == 4) && !onload(xhr_object.responseText)) {
				setTimeout(function() {
					xhr(page,send,onload,backoff*2);
				}, backoff);
			}
		};
	}
	xhr_object.send(send);
}