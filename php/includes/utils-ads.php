<?php
function showRegularAdSection() {
    ?><p class="pub"><?php
    showRegularAd();
    ?></p><?php
}
function showSmallAdSection() {
    ?><p class="pub"><?php
    showSmallAd();
    ?></p><?php
}
function showGameAdSection() {
    ?><div id="vPub"><?php
    showSmallAd();
    ?></div><?php
}
function showRegularAd() {
    ?><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <!-- Forum MKPC -->
    <ins class="adsbygoogle"
        style="display:inline-block;width:728px;height:90px"
        data-ad-client="ca-pub-1340724283777764"
        data-ad-slot="4919860724"></ins>
    <script>
    (adsbygoogle = window.adsbygoogle || []).push({});
    </script><?php
    require_once('grlp.php');
}
function showSmallAd() {
    ?><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <!-- Mario Kart PC -->
    <ins class="adsbygoogle"
        style="display:inline-block;width:468px;height:60px"
        data-ad-client="ca-pub-1340724283777764"
        data-ad-slot="6691323567"></ins>
    <script>
    (adsbygoogle = window.adsbygoogle || []).push({});
    </script><?php
    require_once('grlp.php');
}