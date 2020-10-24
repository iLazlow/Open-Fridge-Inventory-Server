<?php
include("inc/config.php");
include('lib/phpqr/qrlib.php');
$url = str_replace("qr.php", "", url());
$apiKey = $row = $con->query("SELECT * FROM api_keys ORDER BY id DESC")->fetch_assoc()["api_key"];

echo QRcode::svg("$url|$apiKey", false, QR_ECLEVEL_L, 3, 4, false);
?>