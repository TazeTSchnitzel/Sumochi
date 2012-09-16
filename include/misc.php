<?php

// generates a URL for this script
function where_am_i() {
    $url = 'http://' . $_SERVER['SERVER_NAME'];
    if ($_SERVER['SERVER_PORT'] != 80) {
        $url .= ':' . $_SERVER['SERVER_PORT'];
    }
    $url .= $_SERVER['SCRIPT_NAME'];
    return $url;
}
