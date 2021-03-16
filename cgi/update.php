<?php

$token = file_get_contents ( "/var/www/token");
if ($token != "" && $_GET["token"] == $token) {
    echo "[+] starting pull";
    passthru("cd /var/www/cfd_acc && git pull 2>&1; ");
    echo "[+] Might be done";
}

?>