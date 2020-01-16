<?php
    $req = curl_init('http://100.22.11.172/api/v1/customer/check_billpay_reminder');
    curl_setopt($req, CURLOPT_POST, 1);
    curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($req, CURLOPT_SSL_VERIFYHOST, false);
    $headers = array(
        'authorization: Basic UFJPTkVUOkFEVTM4MU5VWUFIUFBMOTI4MVNE',
        'content-type: application/json',
        'x-api-key: 7T1S9KEIKYQBCO30SHJSW',
    );
    curl_setopt($req, CURLOPT_HTTPHEADER, $headers);
    $resp =curl_exec($req);
    curl_close($req);
    echo $resp;
