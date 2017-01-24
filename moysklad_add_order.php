<?php

error_reporting(E_ALL);

include('moysklad_routine_library.php');

$data = $_POST;

if (! (isset($data['positions'], $data['counterparty_id'], $data['organization_id']))) {
    echo 'wrong request';
    return;
}

$positions = $data['positions'];
$counterpartyId = $data['counterparty_id'];
$organizationId = $data['organization_id'];

$newCustomerOrder = [
    'name' => (string)time(),
    'organization' => [
        'meta' => [
            'href' => 'https://online.moysklad.ru/api/remap/1.1/entity/organization/' . $organizationId,
            'type' => 'organization',
            'mediaType' => 'application/json'
        ],
    ],
    'agent' => [
        'meta' => [
            'href' => 'https://online.moysklad.ru/api/remap/1.1/entity/counterparty/' . $counterpartyId,
            'type' => 'counterparty',
            'mediaType' => 'application/json'
        ],
    ],
];

$apiSettings = getApiSettings();
$curl = setupCurl($apiSettings);

$curl = setCurlRequest(
    $curl,
    $apiSettings[MOYSKLAD_API_URL] . $apiSettings[MOYSKLAD_ADD_CUSTOMER_ORDER],
    $apiSettings[MOYSKLAD_ADD_CUSTOMER_ORDER_METHOD]
);

$orderPostData = json_encode($newCustomerOrder);

curl_setopt($curl, CURLOPT_POSTFIELDS, $orderPostData);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($orderPostData))
);

$customerOrderId = createCustomerOrder($curl);

$orderPositions = array();
$isPositionArray = is_array($positions);
if ($isPositionArray) {
    foreach ($positions as $id => $quantity) {

        $positionQuantity = (int)$quantity;
        if ($positionQuantity <= 0) {
            continue;
        }

        $orderPositions[] = [
            "quantity" => $positionQuantity,
            "price" => 0,
            "discount" => 0,
            "vat" => 0,
            "assortment" => [
                "meta" => [
                    "href" => "https://online.moysklad.ru/api/remap/1.1/entity/product/$id",
                    "type" => "product",
                    "mediaType" => "application/json"
                ]
            ],
            "reserve" => $positionQuantity,
        ];
    }
}

$jsonResponse = 'empty';
$isContainPosition = (count($orderPositions) > 0);
if ($isContainPosition) {
    $jsonOrderPositions = json_encode($orderPositions);

    $curl = setupCurl($apiSettings);

    $curl = setCurlRequest(
        $curl,
        $apiSettings[MOYSKLAD_API_URL]
            . $apiSettings[MOYSKLAD_ADD_ORDER_POSITION_PREFIX]
            . $customerOrderId
            . $apiSettings[MOYSKLAD_ADD_ORDER_POSITION_SUFFIX],
        $apiSettings[MOYSKLAD_ADD_ORDER_POSITION_METHOD]
    );

    curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonOrderPositions);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonOrderPositions))
    );

    $jsonResponse = createCustomerOrderPosition($curl);
}

var_export($jsonResponse);


