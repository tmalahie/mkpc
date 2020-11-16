<?php
if (!isset($_GET['id'])) exit;
include('initdb.php');
$baseSettings = array(
    'range' => 1000,
    'position' => 0.5,
    'distribution' => json_decode('[{"fauxobjet":4,"banane":7,"carapace":4},{"carapace":5,"bobomb":2,"carapacerouge":6,"bananeX3":2},{"carapace":3,"bobomb":2,"carapacerouge":5,"bananeX3":2,"poison":2,"carapaceX3":1},{"bobomb":1,"carapacerouge":6,"bananeX3":1,"poison":4,"carapaceX3":3},{"carapacebleue":3,"carapacerouge":3,"champi":5,"carapaceX3":4},{"carapacebleue":4,"carapaceX3":2,"megachampi":3,"champi":6},{"carapacebleue":1,"megachampi":4,"carapacerougeX3":2,"etoile":3,"champi":5},{"carapacerougeX3":1,"megachampi":7,"bloops":2,"champi":5,"etoile":5},{"carapacerougeX3":1,"megachampi":6,"etoile":6,"bloops":1,"champiX3":1,"champior":1,"champi":5},{"champior":1,"megachampi":4,"etoile":4,"billball":2,"champi":1,"champiX3":4},{"megachampi":2,"etoile":3,"billball":2,"champior":3,"champiX3":3},{"etoile":2,"billball":3,"champior":5,"champiX3":2,"eclair":3}]')
);
mysql_query('UPDATE metaitem SET settings="'. mysql_real_escape_string(json_encode($baseSettings)) .'" WHERE id="'. $_GET['id'] .'"');
mysql_close();
header('location: setMetaItem.php?id='. $_GET['id']);