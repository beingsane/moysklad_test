<?php

function getApiSettings()
{
    $apiConfig = include('moysklad_curl_details.php');
    return $apiConfig;
}

/**
 * @param $apiSettings
 * @return resource
 */
function setupCurl($apiSettings)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);

    $userName = $apiSettings[MOYSKLAD_USERNAME];
    $userPassword = $apiSettings[MOYSKLAD_PASSWORD];
    curl_setopt($curl, CURLOPT_USERPWD, "$userName:$userPassword");
    curl_setopt($curl, CURLOPT_USERAGENT, $apiSettings[MOYSKLAD_USER_AGENT]);
    return $curl;
}

function getJuridicalPersonList($apiSettings)
{
    $curl = setupCurl($apiSettings);

    $curl = setCurlRequest(
        $curl,
        $apiSettings[MOYSKLAD_API_URL] . $apiSettings[MOYSKLAD_GET_JURIDICAL_PERSON],
        $apiSettings[MOYSKLAD_GET_JURIDICAL_PERSON_METHOD]
    );

    $response = curlExec($curl);
    $data = json_decode($response, true);
    $result = $data['rows'];
    return $result;
}

function curlExec($curl)
{
    $response = curl_exec($curl);

    $curlErrorNumber = curl_errno($curl);
    if ($curlErrorNumber) {
        throw new Exception(curl_error($curl));
    }

    return $response;
}

function getCounterpartyList($apiSettings)
{
    $curl = setupCurl($apiSettings);

    $curl = setCurlRequest(
        $curl,
        $apiSettings[MOYSKLAD_API_URL] . $apiSettings[MOYSKLAD_GET_COUNTERPARTY],
        MOYSKLAD_GET_COUNTERPARTY_METHOD
    );

    $response = curlExec($curl);
    $data = json_decode($response, true);
    $result = $data['rows'];
    return $result;
}

function getProductList($apiSettings)
{
    $curl = setupCurl($apiSettings);

    $curl = setCurlRequest(
        $curl,
        $apiSettings[MOYSKLAD_API_URL] . $apiSettings[MOYSKLAD_GET_NOMENCLATURE],
        MOYSKLAD_GET_NOMENCLATURE_METHOD
    );

    $response = curlExec($curl);
    $data = json_decode($response, true);
    $result = $data['rows'];
    return $result;
}

function setCurlRequest(&$curl, $uri, $method)
{
    curl_setopt($curl, CURLOPT_URL, $uri);

    curl_setopt($curl, CURLOPT_HTTPGET, true);
    switch ($method) {
        case MOYSKLAD_METHOD_GET:
            break;
        case MOYSKLAD_METHOD_POST:
            curl_setopt($curl, CURLOPT_POST, true);
            break;
        case MOYSKLAD_METHOD_PUT:
            curl_setopt($curl, CURLOPT_PUT, true);
            break;
    }

    return $curl;
}

function createCustomerOrder($apiSettings, $orderPostData)
{
    $curl = setupCurl($apiSettings);

    $curl = setCurlRequest(
        $curl,
        $apiSettings[MOYSKLAD_API_URL] . $apiSettings[MOYSKLAD_ADD_CUSTOMER_ORDER],
        $apiSettings[MOYSKLAD_ADD_CUSTOMER_ORDER_METHOD]
    );

    curl_setopt($curl, CURLOPT_POSTFIELDS, $orderPostData);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($orderPostData)
        )
    );


    $response = curlExec($curl);
    $data = json_decode($response, true);
    $customerOrderId = $data['id'];
    return $customerOrderId;
}

function createCustomerOrderPosition($apiSettings, $customerOrderId, $orderPositionsPostData)
{
    $curl = setupCurl($apiSettings);

    $curl = setCurlRequest(
        $curl,
        $apiSettings[MOYSKLAD_API_URL]
            . $apiSettings[MOYSKLAD_ADD_ORDER_POSITION_PREFIX]
            . $customerOrderId
            . $apiSettings[MOYSKLAD_ADD_ORDER_POSITION_SUFFIX],
        $apiSettings[MOYSKLAD_ADD_ORDER_POSITION_METHOD]
    );

    curl_setopt($curl, CURLOPT_POSTFIELDS, $orderPositionsPostData);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($orderPositionsPostData)
        )
    );


    $response = curlExec($curl);
    $data = json_decode($response, true);
    return $data;
}
