<?php

$balises = array (
    "BD" => 11,
    "B1" => 15,
    "B2" => 19,
    "B3" => 23,
    "BA" => 27
);

function parse_html($matches, &$response, $surname, $name) {
    global $balises;
    $id           = 0;
    $flights      = $response["raw_flights"]; 
    $pilots       = $response["pilots"]; 
    $dpt_stats    = array();
    $all_max      = 0;
    $all_max_name = "";
    $all_sum      = 0;
    foreach ($matches[0] as $v) {
        if (preg_match('#'.$surname.'[\-\w\s]*\s+'.$name.'#i', $v)) {
            $km    = floatval($matches[5][$id]);
            $flight = array(
                "date"   => utf8_encode ( $matches[4][$id]),
                "dpt"    => utf8_encode ( $matches[7][$id]),
                "km"     => utf8_encode ( $km),
                "pilot"  => utf8_encode ( $matches[10][$id]),
            );
            foreach ($balises as $k => $index ) {
                $flight[$k]        = utf8_encode ( $matches[$index][$id] );
                $flight["lat ".$k] = myconvert   ( $matches[$index+1][$id]);
                $flight["lon ".$k] = myconvert   ( $matches[$index+2][$id]);
            }
            update_pilot($pilots, $flight["pilot"], $km);
            if (!array_key_exists($flight["dpt"], $dpt_stats))
                $dpt_stats[$flight["dpt"]] = 0; 
            $dpt_stats[$flight["dpt"]] += $km;
            $all_sum                   += $km;
            if ($km > $all_max) {
                $all_max      = $km;
                $all_max_name = $flight["pilot"];
            }
            array_push($flights, $flight);
        }
        $id++;
    }
    prepare_stats($response, $pilots, $flights, $all_max, $all_max_name, $dpt_stats, $all_sum);
}


?>