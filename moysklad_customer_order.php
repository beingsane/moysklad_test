<?php

/*
//Tell cURL that we want to carry out a POST request.
curl_setopt($curl, CURLOPT_POST, true);

//Set our post fields / date (from the array above).
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postValues));
*/

error_reporting(E_ALL);

include('moysklad_routine_library.php');

$apiSettings = getSettings();
$curl = setupCurl($apiSettings);

$curl = setCurl(
    $curl,
    $apiSettings[MOYSKLAD_API_URL] . $apiSettings[MOYSKLAD_GET_JURIDICAL_PERSON],
    $apiSettings[MOYSKLAD_GET_JURIDICAL_PERSON_METHOD]
);

$persons = getJuridicalPerson($curl);

$curl = setCurl(
    $curl,
    $apiSettings[MOYSKLAD_API_URL] . $apiSettings[MOYSKLAD_GET_COUNTERPARTY],
    MOYSKLAD_GET_COUNTERPARTY_METHOD
);
$counterparty = getCounterparty($curl);

$curl = setCurl(
    $curl,
    $apiSettings[MOYSKLAD_API_URL] . $apiSettings[MOYSKLAD_GET_NOMENCLATURE],
    MOYSKLAD_GET_NOMENCLATURE_METHOD
);
$nomenclature = getNomenclature($curl);
?>
<html>
    <body>
        <form action="#" onsubmit="return false;" id="orderForm">
            <p>
                Доступные юридические лица:<br />
                <?php foreach ($persons as $key => $person) { ?>
                    <?php $personId = $person['id']; ?>
                    <label for="<?= htmlspecialchars($personId) ?>"><?= htmlspecialchars($person['name']) ?></label>
                    <input type="radio" data-organization-type="1" id="<?= htmlspecialchars($personId) ?>" name="organization"/>
                    <br />
                <?php } ?>

                Доступные контрагенты:<br />
                <?php foreach ($counterparty as $key => $person) { ?>
                    <?php $personId = $person['id']; ?>
                    <label for="<?= htmlspecialchars($personId) ?>"><?= htmlspecialchars($person['name']) ?></label>
                    <input type="radio" data-counterparty-type="1" id="<?= htmlspecialchars($personId) ?>" name="counterparty"/>
                    <br />
                <?php } ?>

                Номенклатура товаров:<br />
                <?php foreach ($nomenclature as $key => $position) { ?>
                    <?php $positionId = $position['id']; ?>
                    <label for="<?= htmlspecialchars($positionId) ?>">
                        <?= htmlspecialchars($position['name']) ?>, количество для заказа =>
                    </label>
                    <input type="text" id="<?= htmlspecialchars($positionId) ?>" data-position-type="1"
                        name="position[<?= htmlspecialchars($positionId) ?>]"
                    />
                    <br />
                <?php } ?>

                <input type="submit" name="Сформировать заказ покупателя" onclick="sendOrder();">
                <br />
            </p>
        </form>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
        <script type="text/javascript">
            function sendOrder() {
                var $text_field = $('#orderForm :input:text');

                var position = {};
                $text_field.each(function() {
                    var this_val = $(this).val();
                    var is_it_position = $(this).data('position-type');

                    var may_assign = (this_val > 0 || this_val != '');

                    if (may_assign && is_it_position > 0){
                        position[this.id] = this_val;
                    }
                });

                var organization = $('#orderForm :input:radio:checked[name=organization]').attr('id');
                var counterparty = $('#orderForm :input:radio:checked[name=counterparty]').attr('id');

                var postData = JSON.stringify({position : position, counterparty : counterparty , organization : organization});
                $.ajax({
                    type: 'POST',
                    url: 'moysklad_add_order.php',
                    data: postData,
                    contentType: 'application/json; charset=utf-8',
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
