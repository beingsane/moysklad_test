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

$apiClient = MoySkladApiClient::create();

$orderPostData = json_encode($newCustomerOrder);
$customerOrderId = $apiClient->createCustomerOrder($orderPostData);


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
    $jsonResponse = $apiClient->createCustomerOrderPosition($customerOrderId, $jsonOrderPositions);
}

var_export($jsonResponse);
