<?php
ob_start(); // Start output buffering to prevent accidental output

require '../vendor/autoload.php';
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

include '../db.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$paymentId = $data['razorpay_payment_id'];
$orderId = $data['razorpay_order_id'];
$signature = $data['razorpay_signature'];
$amount = $data['amount'];
$offerId = $data['offer_id'];
$showId = $data['show_id'];
$seats = implode(',', $data['selected_seats']);
$seatTypes = implode(',', $data['seat_types']);
$totalPrice = $data['total_price'];
$userId = $data['user_id'] ?? null;
$method = "Razorpay";
$status = "success";
$paymentTime = date('Y-m-d H:i:s');

$key_secret = 'vtBXANJQ79EqQ8AaVs1PmEdA';

$generated_signature = hash_hmac('sha256', $orderId . "|" . $paymentId, $key_secret);

if (hash_equals($generated_signature, $signature)) {
    $bookingStmt = $conn->prepare("INSERT INTO bookings (user_id, show_id, seats, seat_types, total_price, booking_time, payment_id)
                                   VALUES (?, ?, ?, ?, ?, NOW(), ?)");
    $bookingStmt->bind_param("iissds", $userId, $showId, $seats, $seatTypes, $totalPrice, $paymentId);

    if ($bookingStmt->execute()) {
        $bookingId = $conn->insert_id;

        $paymentStmt = $conn->prepare("INSERT INTO payments (payment_id, order_id, booking_id, amount_paid, offer_id, status, method, payment_time, razorpay_signature)
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $paymentStmt->bind_param("ssidsssss", $paymentId, $orderId,  $bookingId, $amount, $offerId, $status, $method, $paymentTime, $signature);

        if ($paymentStmt->execute()) {
            echo json_encode([
                "status" => "success",
                "booking_id" => $bookingId
            ]);
            exit();

        } else {
            error_log("Payment insertion failed: " . $paymentStmt->error);
            echo "Payment insertion failed.";
        }
    } else {
        error_log("Booking insertion failed: " . $bookingStmt->error);
        echo "Booking insertion failed.";
    }
} else {
    http_response_code(400);
    echo "Invalid payment signature!";
}

ob_end_flush(); // End output buffering
?>
