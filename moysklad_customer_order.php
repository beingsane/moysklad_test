<?php

/*
//Tell cURL that we want to carry out a POST request.
curl_setopt($curl, CURLOPT_POST, true);

//Set our post fields / date (from the array above).
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postValues));
*/

error_reporting(E_ALL);

include('moysklad_routine_library.php');

$apiSettings = getApiSettings();
$curl = setupCurl($apiSettings);

$curl = setCurlRequest(
    $curl,
    $apiSettings[MOYSKLAD_API_URL] . $apiSettings[MOYSKLAD_GET_JURIDICAL_PERSON],
    $apiSettings[MOYSKLAD_GET_JURIDICAL_PERSON_METHOD]
);

$personList = getJuridicalPersonList($curl);

$curl = setCurlRequest(
    $curl,
    $apiSettings[MOYSKLAD_API_URL] . $apiSettings[MOYSKLAD_GET_COUNTERPARTY],
    MOYSKLAD_GET_COUNTERPARTY_METHOD
);
$counterpartyList = getCounterpartyList($curl);

$curl = setCurlRequest(
    $curl,
    $apiSettings[MOYSKLAD_API_URL] . $apiSettings[MOYSKLAD_GET_NOMENCLATURE],
    MOYSKLAD_GET_NOMENCLATURE_METHOD
);
$productList = getProductList($curl);
?>
<html>
    <body>
        <form action="#" onsubmit="return false;" id="orderForm">
            <p>
                Доступные юридические лица:<br />
                <?php foreach ($personList as $key => $person) { ?>
                    <?php $personId = $person['id']; ?>
                    <label for="<?= htmlspecialchars($personId) ?>"><?= htmlspecialchars($person['name']) ?></label>
                    <input type="radio" value="<?= htmlspecialchars($personId) ?>" name="organization_id"/>
                    <br />
                <?php } ?>

                Доступные контрагенты:<br />
                <?php foreach ($counterpartyList as $key => $person) { ?>
                    <?php $personId = $person['id']; ?>
                    <label for="<?= htmlspecialchars($personId) ?>"><?= htmlspecialchars($person['name']) ?></label>
                    <input type="radio" value="<?= htmlspecialchars($personId) ?>" name="counterparty_id"/>
                    <br />
                <?php } ?>

                Номенклатура товаров:<br />
                <?php foreach ($productList as $key => $position) { ?>
                    <?php $positionId = $position['id']; ?>
                    <label for="<?= htmlspecialchars($positionId) ?>">
                        <?= htmlspecialchars($position['name']) ?>, количество для заказа =>
                    </label>
                    <input type="text" name="positions[<?= htmlspecialchars($positionId) ?>]"/>
                    <br />
                <?php } ?>

                <input type="submit" name="Сформировать заказ покупателя" onclick="sendOrder();">
                <br />
            </p>
        </form>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <script type="text/javascript">
            function sendOrder() {

                var postData = $('#orderForm').serialize();
                $.ajax({
                    type: 'POST',
                    url: 'moysklad_add_order.php',
                    data: postData,
                    dataType: 'text',
                    timeout: 10000,
                    error: function() {
                        alert("сбой добавления заказа");
                    },
                    success: function(data) {
                        alert(data);
                    },
                    failure: function(errMsg) {
                        alert(errMsg);
                    }
                });
            }
        </script>
    </body>
</html>
