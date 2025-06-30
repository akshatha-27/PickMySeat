<?php

session_start();

// Redirect if user not logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../login/index.html");
    exit();
}

// DB connection
include '../db.php';

// Sanitize inputs
function sanitize($data)
{
    return htmlspecialchars(trim($data));
}

$email      = $_SESSION['email']; // Use email as unique identifier
$firstName  = sanitize($_POST['first_name'] ?? '');
$lastName   = sanitize($_POST['last_name'] ?? '');
$phone      = sanitize($_POST['phone'] ?? '');
$dob        = sanitize($_POST['dob'] ?? '');
$gender     = sanitize($_POST['gender'] ?? '');
$location   = sanitize($_POST['location'] ?? '');

// Update in DB
$sql = "UPDATE users SET 
            first_name = ?, 
            last_name = ?, 
            phone = ?, 
            dob = ?, 
            gender = ?, 
            location = ? 
        WHERE email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $firstName, $lastName, $phone, $dob, $gender, $location, $email);

if ($stmt->execute()) {
    // Update session
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name']  = $lastName;
    $_SESSION['phone']      = $phone;
    $_SESSION['dob']        = $dob;
    $_SESSION['gender']     = $gender;
    $_SESSION['location']   = $location;

    // $_SESSION['user'] = $firstName . ' ' . $lastName;
    $_SESSION['user'] = $firstName;


    header("Location: edit_profile.php?updated=1");
    exit();
} else {
    echo "Error updating record: " . $stmt->error;
}

$stmt->close();
$conn->close();
