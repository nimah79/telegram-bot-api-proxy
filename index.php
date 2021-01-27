<?php

/**
 * A proxy for api.telegram.org
 * By NimaH79
 * NimaH79.ir.
 */
$scheme = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" || !empty($headers["X-Forwarded-Proto"]) && $headers["X-Forwarded-Proto"] == "https" ? "https" : "http";
$uri = parse_url((preg_match("/^\w+:\/\//", $_SERVER["REQUEST_URI"]) ? "" : $scheme."://".$_SERVER["SERVER_NAME"]).$_SERVER["REQUEST_URI"]);
$_SERVER["REQUEST_URI"] = $uri["path"].(isset($uri["query"]) ? "?".$uri["query"] : "").(isset($uri["fragment"]) ? "#".$uri["fragment"] : "");
$base = rtrim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])));
$uri = parse_url((preg_match("/^\w+:\/\//", $_SERVER["REQUEST_URI"]) ? "" : $scheme."://".$_SERVER["SERVER_NAME"]).$_SERVER["REQUEST_URI"]);
$query = preg_replace("/^".preg_quote($base, "/")."/", "", $uri["path"]);

header("Content-Type: application/json");

if (empty($query) || $query == "/") {
    $response_code = 400;
    http_response_code($response_code);
    echo json_encode(["ok" => false, "error_code" => $response_code, "description" => "request query should not be empty"]);
    die;
}
$ch = curl_init("https://api.telegram.org".(substr($query, 0, 4) == "/" ? "" : "/").$query);
curl_setopt_array($ch, [
    CURLOPT_HEADER => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_POSTFIELDS => file_get_contents("php://input"),
]);
$response = curl_exec($ch);
curl_close($ch);
[$headers, $body] = explode("\r\n\r\n", $response, 2);
$headers = explode("\r\n", $headers);
preg_match("/HTTP.*? (\d+)/", $headers[0], $response_code);
$response_code = $response_code[1];
http_response_code($response_code);
echo $body;
