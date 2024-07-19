<?php

function getQuotation() {

    $amount = getAmount();
    $currency = getCurrency();

    switch ($currency) {
        case "ARS":
            $oQuotation = getArsQuotation();
            showArsQuotation($amount, $oQuotation);
            break;
        case "MXN":
            $oQuotation = getMxnQuotation();
            showMxnQuotation($amount, $oQuotation);
            break;
    }

    return $oQuotation;
}

function checkHelp() {

    $aParams = $_SERVER['argv'];

    if (!empty($aParams[1]) && strtolower($aParams[1]) == '-h') {
        showHelp();
        exit(0);
    }
}

function showHelp() {

    showInfo("Usage: dollar.php [amount] [currency]");
    showInfo("  amount:   integer or float (2 decimals precision) ('.' decimal separator)");
    showInfo("  currency: ('ARS' or 'MXN')");
}

function getAmount() {

    $aParams = $_SERVER['argv'];

    if (empty($aParams[1])) {
        throwError("* ERROR. First parameter must be a valid amount.");
    }

    $amount = trim($aParams[1]);

    if (validateAmount($amount) === FALSE) {
        throwError("* ERROR. First parameter must be a valid amount.");
    }

    return round($amount, 2);
}

function getCurrency() {

    $aParams = $_SERVER['argv'];
    $aValidCurrencies = ['ARS', 'MXN']; //allowed currencies

    if (empty($aParams[2])) {
        throwError("* ERROR. Second parameter must be a valid currency [ARS or MXN].");
    }

    $currency = strtoupper(trim($aParams[2]));

    if (in_array($currency, $aValidCurrencies) === FALSE) {
        throwError("* ERROR. Second parameter must be a valid currency [ARS or MXN].");
    }

    return $currency;
}

function getArsQuotation() {

    $oOfficial = connectToWebService(ARS_OFFICIAL_ENDPOINT);
    $oBlue = connectToWebService(ARS_BLUE_ENDPOINT);

    if (empty($oOfficial->venta) || empty($oBlue->venta)) {
        throwError("* ERROR. Invalid service response.");
    }

    if (validateAmount($oOfficial->venta) === FALSE || validateAmount($oBlue->venta) === FALSE) {
        throwError("* ERROR. Invalid service response.");
    }

    return (object) array('official' => $oOfficial->venta, 'blue' => $oBlue->venta);
}

function getMxnQuotation() {

    $oOfficial = connectToWebService(MXN_ENDPOINT);

    if (empty($oOfficial->venta)) {
        throwError("* ERROR. Invalid service response.");
    }

    if (validateAmount($oOfficial->venta) === FALSE) {
        throwError("* ERROR. Invalid service response.");
    }

    return (object) array('official' => $oOfficial->venta);
}

function showArsQuotation($amount, $oQuotation) {

    $officialValue = round($oQuotation->official, 2);
    $blueValue = round($oQuotation->blue, 2);
    
    showInfo("  QUOTATION:");
    showInfo("      OFFICIAL: 1 USD = " . number_format($officialValue, 2) . " ARS");
    showInfo("      BLUE:     1 USD = " . number_format($blueValue, 2) . " ARS");
    showInfo("  BUDGET:");
    showInfo("      OFFICIAL: " . number_format($amount, 2) . " ARS = " . number_format(round($amount / $officialValue, 2), 2) . " USD");
    showInfo("      BLUE:     " . number_format($amount, 2) . " ARS = " . number_format(round($amount / $blueValue, 2), 2) . " USD");
}

function showMxnQuotation($amount, $oQuotation) {

    $officialValue = round($oQuotation->official, 2);

    showInfo("  QUOTATION:");
    showInfo("      OFFICIAL: 1 USD = " . number_format($officialValue, 2) . " MXN");
    showInfo("  BUDGET:");
    showInfo("      OFFICIAL: " . number_format($amount, 2) . " MXN = " . number_format(round($amount / $officialValue, 2), 2) . " USD");
}

function validateAmount($amount) {

    //is_numeric => Check whether a variable is a number or a numeric string.
    //regex => Prevent hex string (allowed by is_numeric).

    if (is_numeric($amount) === FALSE || $amount <= 0 || preg_match('/[a-zA-Z]/', $amount) == 1) {
        return FALSE;
    }

    return TRUE;
}

function connectToWebService($endpoint, $aHeaders = []) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeaders);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $curlResponse = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($info['http_code'] < 200 || $info['http_code'] > 206) {
        throwError("* ERROR. " . $endpoint . " STATUS: " . $info['http_code'] . " RESPONSE: " . $curlResponse);
    }

    $oCurlResponse = jsonToObject($curlResponse);

    return $oCurlResponse;
}

function jsonToObject($data) {

    //$data = utf8_encode($data);
    $oData = json_decode(trim($data));

    if (json_last_error()) {
        throwError("* ERROR. JSON DECODE ERROR CODE [ " . json_last_error() . " ] ERROR MSG: " . json_last_error_msg());
    }

    return $oData;
}

function throwError($string) {

    //echo $string . PHP_EOL;   //uncomment if your terminal not suports colors
    echo "\033[31m $string \033[0m" . PHP_EOL;  //comment if your terminal not suports colors
    exit(1);
}

function showInfo($string) {

    echo $string . PHP_EOL;
}
