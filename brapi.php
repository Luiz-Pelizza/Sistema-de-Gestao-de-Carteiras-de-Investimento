<?php
if (!function_exists('get_stock_price')) {
    function get_stock_price($symbol)
    {
        global $api_key;
        $api_url = "https://brapi.dev/api/quote/$symbol?token=$api_key";

        $response = @file_get_contents($api_url);

        if ($response === FALSE) {
            return null;
        }

        $data = json_decode($response, true);

        if (isset($data['results'][0]['regularMarketPrice'])) {
            return $data['results'][0]['regularMarketPrice'];
        } else {
            return null;
        }
    }
}
?>
