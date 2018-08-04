<?php

/**
 * A proxy for api.telegram.org
 * By NimaH79
 * NimaH79.ir
 */

$scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' || !empty($headers['X-Forwarded-Proto']) && $headers['X-Forwarded-Proto'] == 'https' ? 'https' : 'http';
$uri = parse_url((preg_match('/^\w+:\/\//', $_SERVER['REQUEST_URI']) ? '' : $scheme.'://'.$_SERVER['SERVER_NAME']).$_SERVER['REQUEST_URI']);
$_SERVER['REQUEST_URI'] = $uri['path'].(isset($uri['query']) ? '?'.$uri['query'] : '').(isset($uri['fragment']) ? '#'.$uri['fragment'] : '');
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])));
$uri = parse_url((preg_match('/^\w+:\/\//', $_SERVER['REQUEST_URI']) ? '' : $scheme.'://'.$_SERVER['SERVER_NAME']).$_SERVER['REQUEST_URI']);
$query = preg_replace('/^'.preg_quote($base, '/').'/', '', $uri['path']);

if(empty($query) || $query == '/') {
    header('Content-Type: application/json');
    echo json_encode(array('ok' => false, 'error_code' => 404, 'description' => 'request query should not be empty'));
}
else {
    if(substr($query, 0, 4) == '/') {
        $ch = curl_init('https://api.telegram.org'.$query);
    }
    else {
        $ch = curl_init('https://api.telegram.org/'.$query);
    }
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    curl_close($ch);
    list($headers, $body) = explode("\r\n\r\n", $response, 2);
    $headers = explode("\r\n", $headers);
    preg_match('/HTTP.*? ([0-9]+)/', $headers[0], $http_code);
    $http_code = $http_code[1];
    http_response_code($http_code);
    header('Content-Type: application/json');
    echo $body;
}
