<?php
header('Content-Type: application/json');
header('Cache-Control: max-age=600000');
echo file_get_contents('../../../mk/maps.json');