<?php
if ($_GET && $_GET["success"]) :
    $success = 1;
    $successText = "Your payment paid successfully";
endif;

if ($_GET && $_GET["cancel"]) :
    $error = 1;
    $errorText = "Your payment cancelled successfully";
endif;
 //body of uniquepaybd start --

 elseif ($method_id == 49) :
    $apikey = $extra['api_key']; //Your Api Key
    $secretkey = $extra['secret_key']; //Your Secret Key

$start_amount = $extra['currency_rate']*$amount;
$total_amount = $start_amount;

$total_amount = number_format((float) $total_amount, 2, ".", "");

$amounts = $total_amount;

$txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);

$hostname = $extra['host_name'];

$success_url = site_url('payment/Babypay?uid='.$user['client_id'].'&txnid='.$txnid);
$cancel_url = site_url('addfunds?canncel=true');

$data   = array(
"cus_name"          => $cus_name,
"cus_email"         => $cus_email,
"amount"            => $amounts ,
"success_url"       => $success_url,
"cancel_url"        => $cancel_url,
);

$header   = array(
"api"               => $apikey,
"secret"            => $secretkey,
"position"          => $hostname,
"url"               => 'https://pay.babypay.site/request/payment/payment_url',
);
$headers = array(
'Content-Type: application/x-www-form-urlencoded',
'app-key: ' . $header['api'],
'secret-key: ' . $header['secret'],
'host-name: ' . $header['position'],
);
$url = $header['url'];
$curl = curl_init();
$data = http_build_query($data);

curl_setopt_array($curl, array(
CURLOPT_URL => $url,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => '',
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 0,
CURLOPT_FOLLOWLOCATION => true,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => 'POST',
CURLOPT_POSTFIELDS => $data,
CURLOPT_HTTPHEADER => $headers,
CURLOPT_VERBOSE =>true
));

$response = curl_exec($curl);
curl_close($curl);
$result = json_decode($response, true);
if ($result['status']) {
$order_id = $txnid;
$insert = $conn->prepare("INSERT INTO payments SET client_id=:c_id, payment_amount=:amount, payment_privatecode=:code, payment_method=:method, payment_create_date=:date, payment_ip=:ip, payment_extra=:extra");
$insert->execute(array("c_id" => $user['client_id'], "amount" => $amount, "code" => $paymentCode, "method" => $method_id, "date" => date("Y.m.d H:i:s"), "ip" => GetIP(), "extra" => $order_id));

if ($insert) {
    $payment_url = $result['payment_url'];
}
} else {
echo $result['message'];
exit();
}        
// Redirects to doniapay
echo '<div class="dimmer active" style="min-height: 400px;">
<div class="loader"></div>
<div class="dimmer-content">
    <center>
        <h2>Please do not refresh this page</h2>
    </center>
    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin:auto;background:#fff;display:block;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
        <circle cx="50" cy="50" r="32" stroke-width="8" stroke="#e15b64" stroke-dasharray="50.26548245743669 50.26548245743669" fill="none" stroke-linecap="round">
            <animateTransform attributeName="transform" type="rotate" dur="1s" repeatCount="indefinite" keyTimes="0;1" values="0 50 50;360 50 50"></animateTransform>
        </circle>
        <circle cx="50" cy="50" r="23" stroke-width="8" stroke="#f8b26a" stroke-dasharray="36.12831551628262 36.12831551628262" stroke-dashoffset="36.12831551628262" fill="none" stroke-linecap="round">
            <animateTransform attributeName="transform" type="rotate" dur="1s" repeatCount="indefinite" keyTimes="0;1" values="0 50 50;-360 50 50"></animateTransform>
        </circle>
    </svg>
    <form action="' . $payment_url . '" method="get" name="BabypayForm" id="BabypayForm">
        <script type="text/javascript">
            document.getElementById("BabypayForm").submit();
        </script>
    </form>
</div>
</div>';

            //end babypay body
