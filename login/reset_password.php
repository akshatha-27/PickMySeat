<?php
$token = $_GET['token'] ?? '';

if (!$token) {
    echo "Invalid or missing token.";
    exit;
}

// Connect to DB
include '../db.php';

// Check if token exists in DB
$stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "This reset link is invalid or has already been used.";
    exit;
}

$user = $result->fetch_assoc();
$createdAt = strtotime($user['reset_token_created_at'] ?? '');
$currentTime = time();

?>

<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="../fontawesome/css/all.min.css" />
</head>
<body>
  <div class="wrapper">
    <div class="form-container">
      <form action="update_password.php" method="post">
        <h2>Reset Password</h2>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="form-group">
          <input type="password" name="new_password" required>
          <i class="fas fa-lock"></i>
          <label>New Password</label>
        </div>
        <div class="form-group">
          <input type="password" name="confirm_password" required>
          <i class="fas fa-lock"></i>
          <label>Confirm Password</label>
        </div>
        <button type="submit" class="btn">Update Password</button>
      </form>
    </div>
  </div>
</body>
</html>
