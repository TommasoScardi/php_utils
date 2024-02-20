<?php

define("HTTP_METHOD_GET", "GET");
define("HTTP_METHOD_POST", "POST");
define("HTTP_METHOD_PUT", "PUT");
define("HTTP_METHOD_DELETE", "DELETE");

define("HTTP_NO_BODY", 0);
define("HTTP_BODY_FORM_URLENCODED", 1);
define("HTTP_BODY_JSON", 2);

/**
 * perform an http request with given method, url, header, query string and body
 *
 * @param string $URL url where send the request
 * @param string $method http method whitch the request will be executed, use `HTTP_METHOD_xxxx` const
 * @param array|null $header header of the request, null for default one
 * @param array|null $queryStringParams query string of the request, null for none
 * @param array $body body of the request
 * @param integer $bodyType body type of the request, choose from `HTTP_BODY_FORM_URLENCODED`, `HTTP_BODY_JSON`
 * @return array ["`curl_error`" => `bool` (true in case the error comes from curl, false if is made by the http request), "`code`" => `int` (curl error code or http status code), "`response`" => `string` (the response in plain text)]
 */
function httpCurlRequest(string $URL, string $method, array $header = null, array $queryStringParams = null, $body = null, int $bodyType = HTTP_NO_BODY) {
    $curlRequest = curl_init();

    if ($queryStringParams !== null) {
        curl_setopt($curlRequest, CURLOPT_URL, $URL."?".http_build_query($queryStringParams));
    }
    else {
        curl_setopt($curlRequest, CURLOPT_URL, $URL);
    }

    curl_setopt($curlRequest, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlRequest, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curlRequest, CURLOPT_TIMEOUT, 60);

    //setto il metodo della richiesta a mio piacimento, per i metodi http GET, POST, PUT, DELETE
    curl_setopt($curlRequest, CURLOPT_CUSTOMREQUEST, strtoupper($method));

    $curlHeader = array();
    if ($header !== null) {
        $curlHeader = $header;
    }

    if ($body !== null && $bodyType !== HTTP_NO_BODY) {
        
        if ($bodyType === HTTP_BODY_JSON) {
            if (gettype($body) === "string") {
                $jsonData = json_decode($body, true);
                if ($jsonData !== null && json_last_error() === JSON_ERROR_NONE) {
                    //si tratta di un oggetto json gia serializzato
                    curl_setopt($curlRequest, CURLOPT_POSTFIELDS, $body);
                    array_push($curlHeader, "Content-Type: application/json");
                    array_push($curlHeader, "Content-Length: ".strlen($body));
                }
            }
            else if (gettype($body) === "array") {
                $jsonSerializedData = json_encode($body);
                if ($jsonSerializedData !== false && json_last_error() === JSON_ERROR_NONE) {
                    curl_setopt($curlRequest, CURLOPT_POSTFIELDS, $jsonSerializedData);
                    array_push($curlHeader, "Content-Type: application/json");
                    array_push($curlHeader, "Content-Length: ".strlen($jsonSerializedData));
                }
            }
        }
        else if ($bodyType === HTTP_BODY_FORM_URLENCODED && gettype($body) === "array") {
            $urlencodedData = http_build_query($body);
            curl_setopt($curlRequest, CURLOPT_POSTFIELDS, $urlencodedData);
            array_push($curlHeader, "Content-Type: application/x-www-form-urlencoded");
            array_push($curlHeader, "Content-Length: ".strlen($urlencodedData));
        }
    }
    
    if (count($curlHeader) > 0) {
        curl_setopt($curlRequest, CURLOPT_HTTPHEADER, $curlHeader);
    }
    return exec_curl_request($curlRequest);
}

function exec_curl_request(CurlHandle $curlRequest) {
    $response = curl_exec($curlRequest);
  
    if ($response === false) {
      $errorCode = curl_errno($curlRequest);
      $errorMsg = curl_error($curlRequest);
      curl_close($curlRequest);
      return array("code" => $errorCode+1000, "body" => '{"message":"'.$errorMsg.'"');
    }
  
    $http_code = intval(curl_getinfo($curlRequest, CURLINFO_HTTP_CODE));
    curl_close($curlRequest);
    return array("code" => $http_code, "body" => $response);
}