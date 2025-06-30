<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $pass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($pass) || empty($confirm)) {
        echo "All fields are required.";
        exit;
    }

    if ($pass !== $confirm) {
        echo "❌ Passwords do not match.";
        exit;
    }

    if (strlen($pass) < 6) {
        echo "❌ Password should be at least 6 characters.";
        exit;
    }

    // Connect to DB
    include '../db.php';

    // Validate token
    $stmt = $conn->prepare("SELECT email FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $hashed = password_hash($pass, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
        $update->bind_param("ss", $hashed, $token);
        $update->execute();

        echo "<p style='color: green;'>✅ Password updated successfully!</p>";
        echo "<p><a href='index.html'>Click here to login</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Invalid or expired token.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>
