function closeOfficialMessage(key) {
    document.getElementById("official_message").parentNode.style.display = "none";
    o_xhr("closeOfficialMessage.php", "key="+key, function(res) {
        return (res == 1);
    });
}