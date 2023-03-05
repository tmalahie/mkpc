var $ads = document.querySelectorAll(".adsbymkpc a");
for (var i=0;i<$ads.length;i++) {
    $ads[i].addEventListener("click", function() {
        var payload = {
            event: "ad_click",
            metadata: {
                origin: document.location.href,
                target: this.href
            }
        };
        fetch('logAnalytics.php', {
            method: 'post',
            body: JSON.stringify(payload)
        });
    });
}