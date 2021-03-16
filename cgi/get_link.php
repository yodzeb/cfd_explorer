<?php
$name_orig = $_GET["name"];
$name=preg_replace("/.*\s+([^\s]+)$/", "$1", $_GET["name"]);
$date    = $_GET["date"];

if (!preg_match('/\w{3,}/', $name) || !preg_match('/^\d+\/\d+\/\d+$/', $date)) {
    echo "exit";
    exit();
}

$url = "https://parapente.ffvl.fr/cfd/selectionner-les-vols";
# 1650-1-0=null&1650-1-1=&1650-1-2=&1650-1-3=&1650-1-4=null&1650-1-5=null&1650-1-6=&1650-1-7=null&1650-1-8=morlet&1650-1-9=&1650-1-10=&1650-1-11=&1650-1-12=&1650-1-13=&1650-1-17=&1650-1-18=null&1650-1-19=parapente&1650-1-20=&op=Filtrer&form_build_id=form-9cHJdyCrDyC94DCopQZXt-y4P1byHeWxsaOofF_-dGw&form_token=K_BASQKzh63xy276RyA2p0opN0Tpy79QMIcWilcNn4M&form_id=requete_filtre_form
$data = array(
    "1650-1-8"  => $name,
    "1650-1-0"  => "", #NULL, # season
    "1650-1-1"  => $date,
    "1650-1-2"  => $date,
    "1650-1-4"  => NULL,
    "1650-1-5"  => NULL,
    "1650-1-6"  => "", # club
    "1650-1-7"  => NULL,
    "1650-1-19" => "parapente",
    "1650-1-18" => NULL,

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

if( preg_match ('#a href="https:\/\/parapente.ffvl.fr\/cfd\/liste\/vol\/(\d+)[^\n]+'.$name_orig.'#s', $result, $matches, PREG_OFFSET_CAPTURE)) {
    header('Access-Control-Allow-Origin: *');

    if (array_key_exists('direct', $_GET) && $_GET['direct'] == 1) {
        # get igc file
        $flight_page=file_get_contents("https://parapente.ffvl.fr/cfd/liste/vol/".$matches[1][0]);
        if( preg_match ('#<a href="(/sites/parapente.ffvl.fr/files/igcfiles/[\d\-]+igcfile[\d\-]+.igc)">IGC \(fichier original\)</a>#s', $flight_page, $matches2, PREG_OFFSET_CAPTURE) ){
            echo $matches2[1][0];
        }
    }
    else {
        echo $matches[1][0]; #var_dump($matches);
    }
}
else {
    header('Access-Control-Allow-Origin: *');
    echo "no res";
    echo $result;
}
    
?>