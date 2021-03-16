<?php

$token = file_get_contents ( "/var/www/token");

if (!strcmp ($token, "") && $_GET["token"] == $token) {
    passthru("cd /var/www/cfd_acc && git pull");
}

?>