<?php

# convert gfs-2020080900-0-12.png in.tif && gdal_translate -of GTiff -a_ullr -61 80 39 25.5 -a_srs EPSG:4269 in.tif inter.tif && rm -f output.tif out.png && gdalwarp   -t_srs EPSG:3857 inter.tif output.tif  && convert output.tif out.png

# curl -s https://modeles.meteociel.fr/modeles/gfs/archives/gfs-2020082400-0-12.png | gdal_translate -of GTiff -a_ullr -61 80 39 25.5 -a_srs EPSG:4269 /vsistdin/ /vsistdout/ | gdalwarp -t_srs EPSG:3857 /vsistdin/ /vsistdout/ | convert - new_out.png

$dt = "";
$matches=array();

if (array_key_exists('dt', $_GET) && preg_match('#^(\d{4})(\d{2})(\d{2})$#', $_GET['dt'], $matches)) {
    $dt=$_GET['dt'];
}

if ($dt != "") {
    
    # Grab page to get image link (some logic apply to get the right url, i do not feel like reversing it
    # https://www.meteociel.fr/modeles/archives/archives.php?day=9&month=5&year=2016&hour=6&type=ncep&map=1&type=ncep&region=&mode=0
    # <img src='https://modeles3.meteociel.fr/modeles/reana/2016/archives-2016-5-9-6-0.png'>
    # <img src='https://modeles16.meteociel.fr/modeles/gfs/archives/2022050906/gfs-0-6.png'>

    header('Access-Control-Allow-Origin: *');
    header('Content-type: image/png');

    # Strangely time is not the same for hour=6 in 2021 and 2022
    # after 16/07/2021 > choose hour=6 to get 12:00
    # before, chose hour=12 to get 12:00

    $hour=12;
    if ($matches[1] >= 2022 || ($matches[1] == 2021 && $matches[2] >= 7 && $matches[3] >=17)) {
        $hour=6;
    }
    
    $page = '';
    $indirect_url = 'https://www.meteociel.fr/modeles/archives/archives.php?day='.$matches[3].'&month='.$matches[2].'&year='.$matches[1].'&hour='.$hour.'&type=ncep&map=1&type=ncep&region=&mode=0';
    #print $indirect_url;
    $fh = fopen($indirect_url,'r') or die($php_errormsg);
    while (! feof($fh)) {
        $page .= fread($fh,1048576);
    }
    fclose($fh);

    $page_match = array();
    if (preg_match('#\<img src\=\'(https://modeles[^\']+png)\'#s', $page, $page_match)) {
        $image = $page_match[1];
        #$cmd = "curl -s https://modeles.meteociel.fr/modeles/gfs/archives/gfs-".$dt."00-0-12.png | gdal_translate -of GTiff -a_ullr -60 80 39 25.5 -a_srs EPSG:4269 /vsistdin/ /vsistdout/ | gdalwarp -t_srs EPSG:3857 /vsistdin/ /vsistdout/ | convert - png:-";
        $cmd = "curl -s $image | gdal_translate -of GTiff -a_ullr -60 80 39 25.5 -a_srs EPSG:4269 /vsistdin/ /vsistdout/ | gdalwarp -t_srs EPSG:3857 /vsistdin/ /vsistdout/ | convert - png:-";
        # error handling ? not interesting.
        passthru($cmd);
    }
    else {
        print ("no match\n\n-------------\n");
    }
    }

?>
