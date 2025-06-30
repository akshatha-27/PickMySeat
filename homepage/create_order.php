<?php
require('../vendor/autoload.php'); // Razorpay SDK
use Razorpay\Api\Api;

$api = new Api('rzp_test_YXSmAaz8w7PTl9', 'vtBXANJQ79EqQ8AaVs1PmEdA');

// You can get these via POST
$amount = $_POST['amount']; // in rupees
$offerId = $_POST['offer_id']; // offer_QTVoNViTZi6rw1

$order = $api->order->create([
    'amount' => $amount * 100, // in paise
    'currency' => 'INR',
    'receipt' => 'rcptid_' . time(),
    'offers' => [$offerId] // Attach offer ID here
]);

echo json_encode([
    'order_id' => $order['id']
]);     