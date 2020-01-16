<?php
    $req = curl_init('http://100.22.11.172/api/v1/pronet/process_cron_checkstatus');
    curl_setopt($req, CURLOPT_POST, 0);
    curl_setopt($req, CURLOPT_HTTPGET, 1);
    curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
    $headers = array(
        'authorization: Basic UFJPTkVUOkFEVTM4MU5VWUFIUFBMOTI4MVNE',
        'content-type: application/json',
        'x-api-key: 7T1S9KEIKYQBCO30SHJSW',
    );
    curl_setopt($req, CURLOPT_HTTPHEADER, $headers);
    $resp =curl_exec($req);
    curl_close($req);
    echo $resp;

