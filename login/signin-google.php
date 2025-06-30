<?php

session_start();
require '../vendor/autoload.php';

// DB connection
include '../db.php';

// Google OAuth setup
$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/../client_secret.json');
$client->setRedirectUri('http://localhost:8081/PickMySeat/login/signin-google.php');
$client->addScope("email");
$client->addScope("profile");

// When Google redirects back with a code
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $client->setAccessToken($token);

        // Get user profile info
        $oauth2 = new Google_Service_Oauth2($client);
        $userInfo = $oauth2->userinfo->get();

        $google_id = $userInfo->id;
        $first_name = $userInfo->givenName;
        $last_name = $userInfo->familyName;
        $email = $userInfo->email;

        // Check if user already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE google_id = ?");
        $stmt->bind_param("s", $google_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Insert user if not exists
            $insert = $conn->prepare("INSERT INTO users (first_name, last_name, email, google_id) VALUES (?, ?, ?, ?)");
            $insert->bind_param("ssss", $first_name, $last_name, $email, $google_id);
            $insert->execute();
        }

        // Store session info
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        // $_SESSION['user_name'] = $first_name . ' ' . $last_name; // full name
        $_SESSION['user'] = $first_name;
        $_SESSION['email'] = $email;
        $_SESSION['google_id'] = $google_id;
        $_SESSION['role'] = 'user';

        header('Location: ../homepage/home.php');
        exit();
    } else {
        echo "Error fetching token: " . $token['error'];
    }
} else {
    // If not redirected from Google
    $login_url = $client->createAuthUrl();
    echo "<a href='$login_url'>Sign in with Google</a>";
}
