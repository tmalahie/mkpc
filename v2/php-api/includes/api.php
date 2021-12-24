<?php
function getJsonBody() {
    return json_decode(file_get_contents('php://input'));
}
function renderResponse($response, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($response);
}