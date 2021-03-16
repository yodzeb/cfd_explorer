<?php

include "regex.php";
include "functions.php";

function parse_html($matches, &$response, $surname, $name) {
    #var_dump($matches);
    error_log(print_r("mem: ".memory_get_usage() , TRUE));
    $id = 0;
    $flights = $response["raw_flights"]; #array();
    $pilots  = $response["pilots"]; #array();
    $dpt_stats = array();
    $all_max = 0;
    $all_max_name = "";
    $all_sum = 0;
    $count = 0;
    foreach ($matches[0] as $v) {
        if (preg_match('#'.$surname.'[\-\w\s]*\s+'.$name.'#i', $v)) {
            $count ++;
            $pilot = $matches[10][$id];
            $km    = floatval($matches[5][$id]);
            $dpt   = utf8_encode ( $matches[7][$id]);
            if (!array_key_exists($dpt, $dpt_stats))
                $dpt_stats[$dpt] = 0;    #$km; #array( "count" => 0, "sum" => 0);
            #$dpt_stats[$dpt]["count"]++;
            $dpt_stats[$dpt] += $km;
            update_pilot($pilots, $pilot, $km);
            $all_sum += $km;
            if ($km > $all_max) {
                $all_max = $km;
                $all_max_name = $pilot;
            }
            $flight = array(
                "date"   => utf8_encode ( $matches[4][$id]),
                "dpt"    => $dpt,
                "km"     => utf8_encode ( $km),
                "pilot"  => $pilot,
                "BD"     => utf8_encode ( $matches[11][$id] ),
                "lat BD" => myconvert($matches[12][$id]),
                "lon BD" => myconvert($matches[13][$id]),
                "B1"     => utf8_encode ( $matches[15][$id] ),
                "lat B1" => myconvert($matches[16][$id]),
                "lon B1" => myconvert($matches[17][$id]),
                "B2"     => utf8_encode ( $matches[19][$id] ),
                "lat B2" => myconvert($matches[20][$id]),
                "lon B2" => myconvert($matches[21][$id]),
                "B3"     => utf8_encode ( $matches[23][$id]),
                "lat B3" => myconvert($matches[24][$id]),
                "lon B3" => myconvert($matches[25][$id]),
                "BA"     => utf8_encode ( $matches[27][$id]),
                "lat BA" => myconvert($matches[28][$id]),
                "lon BA" => myconvert($matches[29][$id]),
            );
            array_push($flights, $flight);
        }
        $id = $id + 1;
    }
    foreach ($pilots as $p => $v) {
        #var_dump($p);
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

# seems not to work anymore.
function parse_csv($content, &$response, $surname, $name) {
    $filename = "/tmp/xls_file_".rand(1,10000);
    file_put_contents ( $filename, $content);
    passthru("ssconvert $filename $filename".".csv");
    $csv = fopen("$filename".".csv","r");
    $line = fgetcsv($csv);
    #$flights = array();

    $flights = $response["raw_flights"]; #array();
    $pilots  = $response["pilots"]; #array();

    while ($line = fgetcsv($csv) ){
        if (preg_match('#'.$surname.'#i', $line[7])) {
            $pilot = $line[7];
            $km = $line[2];
            #echo $line[7] . $surname;
            $flight = array(
                "lat BD" => myconvert($line[23]),
                "lon BD" => myconvert($line[24]),
                "lat B1" => myconvert($line[27]),
                "lon B1" => myconvert($line[28]),
                "lat B2" => myconvert($line[31]),
                "lon B2" => myconvert($line[32]),
                "lat B3" => myconvert($line[35]),
                "lon B3" => myconvert($line[36]),
                "lat BA" => myconvert($line[39]),
                "lon BA" => myconvert($line[40]),
                "pilot"  => $line[7],
                "date"   => $line[1],
                "km"     => myconvert($km)
                );
            array_push($flights, $flight);
        }
    }
    foreach ($pilots as $p => $v) {
        #var_dump($p);
        $pilots[$p]["avg"] = 0;
        if ($v["flights"] != 0)
            $pilots[$p]["avg"] = round($v["sum"]/$v["flights"]);
    }
    $response['pilots']      = $pilots;
    $response["raw_flights"] = $flights;
    #echo json_encode($response);
    #passthru("rm -f $filename $filename".".csv");
    return $response;
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


$response   = get_empty_message();
$season     = "";
$biplace    = 0;
$dept       = "";
$name       = "";
$club       = "";
$club_id    = 0;
$date_start = "";
$date_end   = "";

read_params();
exec_request();

function read_params() {
    global $response, $season, $biplace, $dept, $name, $club, $club_id, $surname, $date_start, $date_end;
    if (array_key_exists('club_id', $_GET) && preg_match('#^\d+$#', $_GET['club_id']))
        $club_id = $_GET['club_id']; # does not wrk. generated link frm CFD URI to long.
    if (array_key_exists('name', $_GET) && preg_match('#^[\w\s\-]{3,20}$#', $_GET['name'])){
        $name    = $_GET["name"];
    }
    if (array_key_exists("club", $_GET) && preg_match('#^[\w\-\s]*$#', $club)) {
        $club = $_GET["club"];
        $club = preg_replace('#\'#', '%', $club); # smells like SQLi there
    }
    if (array_key_exists("season", $_GET) && preg_match('/^\d{4}$/', $_GET["season"])) {
        $season = $_GET["season"];
        array_push($response['warnings'], "SEASON!");    
    }
    if (array_key_exists('bi', $_GET) && $_GET['bi'] == "1")
        $biplace = 1;
    if (array_key_exists('dept', $_GET) && preg_match('/^([\d\w,]{2,4})*$/', $_GET['dept']))
        $dept = $_GET['dept'];
    if (array_key_exists("surname", $_GET) && preg_match('#^\w*$#', $surname))
        $surname = $_GET["surname"];
    if (array_key_exists("date_start", $_GET) && preg_match('#^[\d\/\-]+$#', $_GET["date_start"])) {
        $date_start = str_replace("-","/",$_GET["date_start"]);
    }
    if (array_key_exists("date_end", $_GET) && preg_match('#^[\d\/\-]+$#', $_GET["date_end"])) {
        $date_end = str_replace("-","/",$_GET["date_end"]);
    }
}


function exec_request() {
    global $response, $season, $biplace, $dept, $name, $club, $club_id, $surname, $date_start, $date_end;
    if ( $name != "" || $club != "" || ($dept != "" && $season != "") || $biplace == "1" || $club_id != 0 || ($date_start != "" && $date_end != "")) {
        $dept_list = explode(',', $dept);
        if(sizeof($dept_list) < 10) {
            foreach ($dept_list as $dept) {
                #echo $dept;
                do_request($name, $club, $dept, $season, $biplace, $club_id, $response) ;
            }
        }
        else {
            do_request($name, $club, $dept_list[0], $season, $biplace, $club_id, $response) ;
            array_push($response["warnings"], "Too many departements.");
        }
    }
    else {
        array_push($response["warnings"], "Too few params $date_start");
    }
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

function do_request($name, $club, $dept, $season, $biplace, $club_id, &$response) {
    #global $surname, $html_regex, $dod_regex, $date_st;
    global $surname, $html_regex, $date_start, $date_end;
    #echo "doeing req";
    array_push($response["warnings"], "Ding req $dept");
    $url = "https://parapente.ffvl.fr/cfd/selectionner-les-vols";
    $data = array(
        "1650-1-8"  => $name,
        "1650-1-9"  => "",
        "1650-1-10"  => "",
        "1650-1-11"  => "",
        "1650-1-12"  => "",
        "1650-1-13"  => "",
        "1650-1-17"  => "",
        "1650-1-20"  => "",
        "1650-1-0"  => $season, #NULL, # season
        "1650-1-1"  => $date_start,
        "1650-1-2"  => $date_end,
        "1650-1-3"  => $dept,
        "1650-1-4"  => NULL,
        "1650-1-5"  => NULL,
        "1650-1-6"  => $club,
        "1650-1-7"  => ($club_id?$club_id:NULL), # club id
        "1650-1-19" => "parapente",
        "1650-1-18" => NULL,
        "1650-1-14[1]"=> $biplace,
        "op" => "Filtrer",
        "form_id" => "requete_filtre_form");

// use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    #file_put_contents("/tmp/bla", $result);    

    if ($result === FALSE) { $response['errors'] = 'error'; /* Handle error */ }
    
    
    if (preg_match ('#a href=\"(/node/1650/[^\"]+)\"#s', $result, $matches, PREG_OFFSET_CAPTURE)) {
        #echo "https://parapente.ffvl.fr" . $matches[1][0];
        $url = "https://parapente.ffvl.fr" . $matches[1][0];
        array_push($response['warnings'], "Fecthing $url");
        $html_xls = file_get_contents($url, false);
        #echo $html_xls;
        error_log(print_r("mem_b: ".memory_get_usage() , TRUE));    
        if (preg_match_all($html_regex, $html_xls, $matches)){#, PREG_OFFSET_CAPTURE)) {
            #error_log(print_r("aaa".var_dump($matches), TRUE));
            error_log(print_r("mem_af: ".memory_get_usage() , TRUE));    
            $html_xls="";
            array_push($response['warnings'], "Parsing HTML");
            parse_html($matches, $response, $surname, $name);
            #file_put_contents ( "/tmp/bla_raw", $html_xls);

        }
        else {
            $response = parse_csv($html_xls, $response, $surname, $name);
            array_push($response['warnings'], "Parsing XLS");
        }
    }
    else {
        $response["warnings"] = "MISSING LINK";
    }
}

?>