<?php

function myconvert ($val) {
    $v = str_replace(",", ".", $val);
    $v = floatval($v);
    return $v;
}

function parse_html($matches, &$response, $surname, $name) {
    #var_dump($matches);
    $id = 0;
    $flights = $response["raw_flights"]; #array();
    $pilots  = $response["pilots"]; #array();
    #array_push($response['warnings'], $matches);
    foreach ($matches[0] as $v) {
        if (preg_match('#'.$surname.'[\-\w\s]*\s+'.$name.'#i', $v[0])) {
            $pilot = $matches[10][$id][0];
            $km    = $matches[5][$id][0];
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
            $flight = array(
                "date"   => utf8_encode ( $matches[4][$id][0]),
                "km"     => utf8_encode ( $km),
                "pilot"  => $pilot,
                "BD"     => utf8_encode ( $matches[11][$id][0] ),
                "lat BD" => myconvert($matches[12][$id][0]),
                "lon BD" => myconvert($matches[13][$id][0]),
                "B1"     => utf8_encode ( $matches[15][$id][0] ),
                "lat B1" => myconvert($matches[16][$id][0]),
                "lon B1" => myconvert($matches[17][$id][0]),
                "B2"     => utf8_encode ( $matches[19][$id][0] ),
                "lat B2" => myconvert($matches[20][$id][0]),
                "lon B2" => myconvert($matches[21][$id][0]),
                "B3"     => utf8_encode ( $matches[23][$id][0]),
                "lat B3" => myconvert($matches[24][$id][0]),
                "lon B3" => myconvert($matches[25][$id][0]),
                "BA"     => utf8_encode ( $matches[27][$id][0]),
                "lat BA" => myconvert($matches[28][$id][0]),
                "lon BA" => myconvert($matches[29][$id][0]),
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
    $response['raw_flights'] = $flights; #array_merge($flights, $response['raw_flights']);
    $response['pilots']      = $pilots;
}

function parse_csv($content, &$response, $surname, $name) {
    $filename = "/tmp/xls_file_".rand(1,10000);
    file_put_contents ( $filename, $content);
    passthru("ssconvert $filename $filename".".csv");
    $csv = fopen("$filename".".csv","r");
    $line = fgetcsv($csv);
    $flights = array();
    while ($line = fgetcsv($csv) ){
#        foreach( $line as $t) {
            if (preg_match('#'.$surname.'#i', $line[7])) {
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
                );
                array_push($flights, $flight);
            }
            #print $t;
    #}
    }
    $response["raw_flights"] = $flights;
    #echo json_encode($response);
    passthru("rm -f $filename $filename".".csv");
    return $response;
}

function get_empty_message() {
    $msg = array();
    $msg['status'] = "ok";
    $msg['errors'] = array();
    $msg['warnings'] = array();
    $msg['raw_flights'] = array();
    return $msg;  
}


$response = get_empty_message();
$season = "";
$biplace = 0;
$dept = "";
$name="";
$club="";
$club_id=0;
if ($_GET['club_id'] && preg_match('#^\d+$#', $_GET['club_id']))
    $club_id = $_GET['club_id']; # does not wrk. generated link frm CFD URI to long.
if ($_GET['name'] && preg_match('#^[\w\s\-]{3,20}$#', $_GET['name'])){
    $name    = $_GET["name"];
}
if ($_GET["club"] && preg_match('#^[\w\-\s]*$#', $club)) {
    $club = $_GET["club"];
    $club = preg_replace('#\'#', '%', $club);
}
if ($_GET["season"] && preg_match('/^\d{4}$/', $_GET["season"])) {
    $season = $_GET["season"];
    array_push($response['warnings'], "SEASON!");    
}
if ($_GET['bi'] == "1")
    $biplace = 1;
if ($_GET['dept'] && preg_match('/^([\d\w,]{2,4})*$/', $_GET['dept']))
    $dept = $_GET['dept'];
if ($_GET["surname"] && preg_match('#^\w*$#', $surname))
    $surname = $_GET["surname"];

if ( $name != "" || $club != "" || ($dept != "" && $season != "") || $biplace == "1" || $club_id != 0) {
    $dept_list = explode(',', $dept);
    #var_dump($dept_list);
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

function do_request($name, $club, $dept, $season, $biplace, $club_id, &$response) {
    #echo "doeing req";
    array_push($response["warnings"], "Ding req");
    $url = "https://parapente.ffvl.fr/cfd/selectionner-les-vols";
    # 1650-1-0=null&1650-1-1=&1650-1-2=&1650-1-3=&1650-1-4=null&1650-1-5=null&1650-1-6=&1650-1-7=null&1650-1-8=morlet&1650-1-9=&1650-1-10=&1650-1-11=&1650-1-12=&1650-1-13=&1650-1-17=&1650-1-18=null&1650-1-19=parapente&1650-1-20=&op=Filtrer&form_build_id=form-9cHJdyCrDyC94DCopQZXt-y4P1byHeWxsaOofF_-dGw&form_token=K_BASQKzh63xy276RyA2p0opN0Tpy79QMIcWilcNn4M&form_id=requete_filtre_form
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
        "1650-1-1"  => "",
        "1650-1-2"  => "",
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
        #<tr><td></td><td></td><td>2015-2016</td><td>06/08/2016</td><td>45,70</td><td>45,70</td><td>08</td><td>Dist 2 pts</td><td>-1</td><td>Romain WISNIEWSKI</td><td>st germain</td><td>Malavillers</td><td>0,52</td><td>18,34</td><td>26,81</td><td>0,00</td><td>st germain=>Malavillersst germain-Grand-Failly</td><td>B</td><td>Rush 4</td><td></td><td>25,5</td><td>1,8</td><td>6444</td>////<td>1457</td><td>st germain</td><td>49,4107</td><td>5,25222</td><td>1470488100</td><td>st germain</td><td>49,4062</td><td>5,25013</td><td>1470488220</td><td>Grand-Failly</td><td>49,4242</td><td>5,50232</td><td>1470491169</td><td></td><td></td><td></td><td></td><td>Malavillers</td><td>49,3558</td><td>5,85775</td><td>1470494544</td> </tr>#
        
        if (preg_match_all('#<tr><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td>.*?<td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td><td>([^\<]*)</td> </tr>#', $html_xls, $matches, PREG_OFFSET_CAPTURE)) {
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
/* else { */
/*     $response["warnings"] = "NO_RES";//array("no_res"); */
/* } */
/* } */
#var_dump($response);
#$response["warnings"] = "NO_RES";
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
echo json_encode($response, JSON_UNESCAPED_UNICODE);

?>