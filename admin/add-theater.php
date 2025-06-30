<?php
// add_theater.php

include '../db.php';

// Display errors (optional for development only)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_theater"])) {
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');

    if (!empty($name) && !empty($location)) {
        $stmt = $conn->prepare("INSERT INTO theaters (name, location) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $location);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Theater added successfully!'); window.location.href='admin-dashboard.php';</script>";
        } else {
            echo "<script>alert('❌ Database Error: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('⚠️ Please fill in all fields.');</script>";
    }
}
?>

<!-- Add Theater Form -->
<h2 style="text-align:center;font-size:30px;">Add Theater</h2>
<form method="POST" action="">
    <label>Theater Name:</label>
    <input type="text" name="name" required>

    <label>Location:</label>
    <input type="text" name="location" required>

    <button type="submit" name="add_theater">Add Theater</button>
</form>
