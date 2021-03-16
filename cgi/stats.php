<?php

function prepare_stats(&$response, &$pilots, &$flights, $all_max, $all_max_name, &$dpt_stats, $all_sum) {
    $count = count ($flights);
    foreach ($pilots as $p => $v) {
        $pilots[$p]["avg"] = 0;
        if ($v["flights"] != 0)
            $pilots[$p]["avg"] = round($v["sum"]/$v["flights"]);
    }
    $response['raw_flights'] = $flights;
    $response['pilots']      = $pilots;
    $response['stats']       = array();
    asort($dpt_stats, SORT_NUMERIC );
    $dpt_stats = array_reverse($dpt_stats, 1);
    if ($count > 0) {
        $response['stats']['all'] = array(
            "sum" => $all_sum,
            "count"   => $count,
            "avg"     => floor($all_sum / $count)
        );
    }
    $response['stats']['top_dpt'] = "";
    $response['stats']['max'] = $all_max;
    $response['stats']['max_name'] = $all_max_name;
    
    $i=0;
    foreach ($dpt_stats as $d => $v) {
        $response['stats']['top_dpt'] .= "$d (".$v."kms), ";
        if ($i++ == 2) {
            break ;
        }
    }
    if ($i>0)
        $response['stats']['top_dpt']=substr($response['stats']['top_dpt'], 0, -2);
}

function update_pilot(&$pilots, $pilot, $km) {
    if (!array_key_exists($pilot, $pilots)) {
        $pilots[$pilot] = array();
        $pilots[$pilot]["flights"] = 0;
        $pilots[$pilot]["max"] = 0;
        $pilots[$pilot]["sum"] = 0;
    }
    if ($pilots[$pilot]["flights"])
        $pilots[$pilot]["flights"] = $pilots[$pilot]["flights"]+1;
    else
        $pilots[$pilot]["flights"] = 1;
    if ($pilots[$pilot]["max"]){
        if ( $km > $pilots[$pilot]["max"]){
            $pilots[$pilot]["max"] = (float) $km;
        }
    }
    else
        $pilots[$pilot]["max"] = (float)$km;
    if ($pilots[$pilot]["sum"])
                $pilots[$pilot]["sum"] += $km;
    else
        $pilots[$pilot]["sum"] = (float) $km;
    
}

?>