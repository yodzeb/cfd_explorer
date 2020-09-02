<?php

# convert gfs-2020080900-0-12.png in.tif && gdal_translate -of GTiff -a_ullr -61 80 39 25.5 -a_srs EPSG:4269 in.tif inter.tif && rm -f output.tif out.png && gdalwarp   -t_srs EPSG:3857 inter.tif output.tif  && convert output.tif out.png

# curl -s https://modeles.meteociel.fr/modeles/gfs/archives/gfs-2020082400-0-12.png | gdal_translate -of GTiff -a_ullr -61 80 39 25.5 -a_srs EPSG:4269 /vsistdin/ /vsistdout/ | gdalwarp -t_srs EPSG:3857 /vsistdin/ /vsistdout/ | convert - new_out.png

$dt = "";
if (array_key_exists('dt', $_GET) && preg_match('#^\d{8}$#', $_GET['dt'])) {
    $dt=$_GET['dt'];
}

if ($dt != "") {
    #echo "aaa";
    #echo "curl -s https://modeles.meteociel.fr/modeles/gfs/archives/gfs-".$dt."00-0-12.png | gdal_translate -of GTiff -a_ullr -61 80 39 25.5 -a_srs EPSG:4269 /vsistdin/ $file_imed";
    header('Content-Type: image/png');
    header('Access-Control-Allow-Origin: *');
    #exit();

    $file_in = "/tmp/pres_in_file_".rand(1,10000).".tif";
    $file_imed = "/tmp/pres_imed_file_".rand(1,10000).".tif";
    $file_out  = "/tmp/pres_out_file_".rand(1,10000).".tif";
    exec("curl -s https://modeles.meteociel.fr/modeles/gfs/archives/gfs-".$dt."00-0-12.png | convert - tif:$file_in ; gdal_translate -of GTiff -a_ullr -61 80 39 25.5 -a_srs EPSG:4269 $file_in $file_imed 2> /dev/null");
    exec("gdalwarp -t_srs EPSG:3857 $file_imed $file_out ");
    passthru("convert tif:$file_out png:-");
    exec ("rm -f $file_in $file_imed $file_out");
    exit();

    
    $cmd = "curl -s https://modeles.meteociel.fr/modeles/gfs/archives/gfs-".$dt."00-0-12.png | gdal_translate -of GTiff -a_ullr -61 80 39 25.5 -a_srs EPSG:4269 /vsistdin/ /vsistdout/ | gdalwarp -t_srs EPSG:3857 /vsistdin/ /vsistdout/ | convert - png:-";
    # error handling ? not interesting.
    passthru($cmd);
}

?>