<?php

# convert gfs-2020080900-0-12.png in.tif && gdal_translate -of GTiff -a_ullr -61 80 39 25.5 -a_srs EPSG:4269 in.tif inter.tif && rm -f output.tif out.png && gdalwarp   -t_srs EPSG:3857 inter.tif output.tif  && convert output.tif out.png

# curl -s https://modeles.meteociel.fr/modeles/gfs/archives/gfs-2020082400-0-12.png | gdal_translate -of GTiff -a_ullr -61 80 39 25.5 -a_srs EPSG:4269 /vsistdin/ /vsistdout/ | gdalwarp -t_srs EPSG:3857 /vsistdin/ /vsistdout/ | convert - new_out.png

$dt = "";
if (array_key_exists('dt', $_GET) && preg_match('#^\d{8}$#', $_GET['dt'])) {
    $dt=$_GET['dt'];
}

if ($dt != "") {
    header('Access-Control-Allow-Origin: *');
    header('Content-type: image/png');

    $url = "https://modeles.meteociel.fr";
    if (array_key_exists('param', $_GET) && preg_match('#^pressure$#', $_GET['param'])) {
        $url.="/modeles/gfs/archives/gfs-".$dt."00-0-12.png";
    }
    else if (array_key_exists('param', $_GET) && preg_match('#^wind10$#', $_GET['param'])) {
        # https://modeles.meteociel.fr/modeles/reana-era/2018/archives-2018-3-31-0-6.png
        $year = substr($dt, 0, 4);
        $month = (int)substr($dt, 4, 2);
        $day   = (int)substr($dt, 6, 2);
        $url .= "/modeles/reana-era/".$year."/archives-".$year."-".$month."-".$day."-18-6.png";
    }
    else {
        $url.="/modeles/gfs/archives/gfs-".$dt."00-0-12.png";
    }
    
    #$cmd = "curl -s https://modeles.meteociel.fr/modeles/gfs/archives/gfs-".$dt."00-0-12.png | gdal_translate -of GTiff -a_ullr -60 80 39 25.5 -a_srs EPSG:4269 /vsistdin/ /vsistdout/ | gdalwarp -t_srs EPSG:3857 /vsistdin/ /vsistdout/ | convert - png:-";
    $cmd = "curl -s $url | gdal_translate -of GTiff -a_ullr -60 80 39 25.5 -a_srs EPSG:4269 /vsistdin/ /vsistdout/ | gdalwarp -t_srs EPSG:3857 /vsistdin/ /vsistdout/ | convert - png:-";
    # error handling ? not interesting.
    passthru($cmd);
}

?>