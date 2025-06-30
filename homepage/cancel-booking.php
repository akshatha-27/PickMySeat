<?php
session_start();
include '../db.php';

$bookingId = $_GET['booking_id'] ?? null;

if (!$bookingId) {
    echo "Invalid booking ID.";
    exit;
}

// Optionally: Check if user owns the booking (if login is implemented)

$updateQuery = "UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?";

$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("i", $bookingId);

if ($stmt->execute()) {
    echo "<script>alert('Booking cancelled successfully');window.location.href='home.php';</script>";
    // Optionally redirect:
    // header("Location: my-bookings.php?cancelled=1");
} else {
    echo "<script>alert('Error cancelling booking');window.location.href='home.php';</script>";
}
?>
