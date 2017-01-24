<?php

class MoySkladApiClient
{
    private $apiSettings;

    private $curl;


    public function __construct($apiSettings)
    {
        $this->apiSettings = $apiSettings;
        $this->setupCurl();
    }

    public static function create()
    {
        $apiConfig = include('moysklad_curl_details.php');
        $apiClient = new self($apiConfig);

        return $apiClient;
    }


    public function getJuridicalPersonList()
    {
        $this->setCurlRequest(
            $this->apiSettings[MOYSKLAD_API_URL] . $this->apiSettings[MOYSKLAD_GET_JURIDICAL_PERSON],
            $this->apiSettings[MOYSKLAD_GET_JURIDICAL_PERSON_METHOD]
        );

        $response = $this->curlExec();
        $data = json_decode($response, true);
        $result = $data['rows'];
        return $result;
    }


    public function getCounterpartyList()
    {
        $this->setCurlRequest(
            $this->apiSettings[MOYSKLAD_API_URL] . $this->apiSettings[MOYSKLAD_GET_COUNTERPARTY],
            MOYSKLAD_GET_COUNTERPARTY_METHOD
        );

        $response = $this->curlExec();
        $data = json_decode($response, true);
        $result = $data['rows'];
        return $result;
    }


    public function getProductList()
    {
        $this->setCurlRequest(
            $this->apiSettings[MOYSKLAD_API_URL] . $this->apiSettings[MOYSKLAD_GET_NOMENCLATURE],
            MOYSKLAD_GET_NOMENCLATURE_METHOD
        );

        $response = $this->curlExec();
        $data = json_decode($response, true);
        $result = $data['rows'];
        return $result;
    }


    public function createCustomerOrder($orderPostData)
    {
        $this->setCurlRequest(
            $this->apiSettings[MOYSKLAD_API_URL] . $this->apiSettings[MOYSKLAD_ADD_CUSTOMER_ORDER],
            $this->apiSettings[MOYSKLAD_ADD_CUSTOMER_ORDER_METHOD]
        );

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $orderPostData);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($orderPostData)
            )
        );

        $response = $this->curlExec();
        $data = json_decode($response, true);
        $customerOrderId = $data['id'];
        return $customerOrderId;
    }


    public function createCustomerOrderPosition($customerOrderId, $orderPositionsPostData)
    {
        $this->setCurlRequest(
            $this->apiSettings[MOYSKLAD_API_URL]
                . $this->apiSettings[MOYSKLAD_ADD_ORDER_POSITION_PREFIX]
                . $customerOrderId
                . $this->apiSettings[MOYSKLAD_ADD_ORDER_POSITION_SUFFIX],
            $this->apiSettings[MOYSKLAD_ADD_ORDER_POSITION_METHOD]
        );

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $orderPositionsPostData);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($orderPositionsPostData)
            )
        );

        $response = $this->curlExec();
        $data = json_decode($response, true);
        return $data;
    }




    private function setupCurl()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);

        $userName = $this->apiSettings[MOYSKLAD_USERNAME];
        $userPassword = $this->apiSettings[MOYSKLAD_PASSWORD];
        curl_setopt($curl, CURLOPT_USERPWD, "$userName:$userPassword");
        curl_setopt($curl, CURLOPT_USERAGENT, $this->apiSettings[MOYSKLAD_USER_AGENT]);

        $this->curl = $curl;
    }


    private function setCurlRequest($uri, $method)
    {
        $curl = $this->curl;

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
    }


    private function curlExec()
    {
        $curl = $this->curl;

        $response = curl_exec($curl);

        $curlErrorNumber = curl_errno($curl);
        if ($curlErrorNumber) {
            throw new Exception(curl_error($curl));
        }

        return $response;
    }
}
