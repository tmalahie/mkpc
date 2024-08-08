var $grlpSelector = 'div[style^="color-scheme: initial !important;"]';
var $grlp = document.querySelector($grlpSelector);
if ($grlp)
	$grlp.remove();
else if (window.adsbygoogle) {
	function observeGrlp() {
		var observer = new MutationObserver(() => {
			$grlp = document.querySelector($grlpSelector);
			if (!$grlp) return;
			$grlp.remove();
			observer.disconnect();
			window.removeEventListener("scroll", handleScrollGrlp);
		});

		observer.observe(document.body, {
			childList: true,
		});

		setTimeout(function() {
			observer.disconnect();
		}, 5000);
	}
	function handleScrollGrlp() {
		$grlp = document.querySelector($grlpSelector);
		if ($grlp)
			$grlp.remove();
		else
			observeGrlp();
		window.removeEventListener("scroll", handleScrollGrlp);
	}
	observeGrlp();
	window.addEventListener("scroll", handleScrollGrlp);
}