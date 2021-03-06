<?php

if (!function_exists('array_key_first')) {
    function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }
}

function myconvert ($val) {
    $v = str_replace(",", ".", $val);
    $v = floatval($v);
    return $v;
}

        
function get_empty_message() {
    $msg = array();
    $msg['status'] = "ok";
    $msg['errors'] = array();
    $msg['warnings'] = array();
    $msg['raw_flights'] = array();
    $msg['pilots']      = array();
    return $msg;  
}


?>