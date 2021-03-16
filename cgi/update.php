<?php

$token = file_get_contents ( "/var/www/token");
if ($token != "" && $_GET["token"] == $token) {
    echo "[+] starting pull";
    if ($_SERVER['SERVER_NAME'] == "cfd.acc.wiro.fr") {
        passthru("cd /var/www/cfd_acc && git pull 2>&1; ");
    }
    else if ($_SERVER['SERVER_NAME'] == "cfd.wiro.fr") {
        passthru("cd /var/www/cfd_explorer && git pull 2>&1; ");
    }
    echo "[+] Might be done";
}

?>