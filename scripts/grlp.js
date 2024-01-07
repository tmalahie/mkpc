var $grlp = document.querySelector(".google-revocation-link-placeholder");
if ($grlp)
	$grlp.remove();
else if (window.adsbygoogle) {
	var observer = new MutationObserver(() => {
		$grlp = document.querySelector(".google-revocation-link-placeholder");
		if (!$grlp) return;
		$grlp.remove();
		observer.disconnect();
	});
	
	observer.observe(document.body, {
		childList: true,
	});
	setTimeout(function() {
		observer.disconnect();
	}, 5000);
}