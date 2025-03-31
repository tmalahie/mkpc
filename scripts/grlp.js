(function() {
    const $grlpSelector = 'div[style^="color-scheme: initial !important;"]';
    let $grlp = document.querySelector($grlpSelector);
    if ($grlp)
        $grlp.remove();
    else if (window.adsbygoogle) {
        function observeGrlp() {
            const observer = new MutationObserver(() => {
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
})();