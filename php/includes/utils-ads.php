<?php
function makeAd($width, $height, $adSlot) {
    if (!str_contains($_SERVER['HTTP_HOST'], 'malahieude.net')) {
        return; // no ads on selfhost
    }
    ?>
    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <ins class="adsbygoogle"
        style="display:inline-block;width:<?= $width ?>px;height:<?= $height ?>px"
        data-ad-client="ca-pub-1340724283777764"
        data-ad-slot="<?= $adSlot ?>"></ins>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
    <?php
    require_once('grlp.php');
}
function showRegularAd() {
    makeAd(728, 90, '4919860724');
}

function showSmallAd() {
    makeAd(468, 60, '6691323567');
}
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