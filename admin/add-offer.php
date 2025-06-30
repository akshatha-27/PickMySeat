<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_offer'])) {
    $description = $_POST['description'] ?? '';
    $valid_until = $_POST['valid_until'] ?? null;
    $status = $_POST['status'] ?? 'active';

    // Validation
    if (!empty($description) && !empty($valid_until)) {

        // Prepare SQL
        $sql = "INSERT INTO offers (description,valid_until, status)
                VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            echo "<script>alert('Prepare failed: " . $conn->error ."');</script>";
            return;
        }

        $stmt->bind_param("sss", $description, $valid_until, $status);

        if ($stmt->execute()) {
            echo "<script>alert('Offer added successfully!'); window.location.href='admin-dashboard.php';</script>";
        } else {
            echo "<script>alert('Database Error: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Error: Please fill in all required fields.');</script>";
    }
}
?>


<!-- Add Offer Form -->
<h2 style="text-align:center;font-size:30px;">Add Offer</h2>
<form method="POST" action="">
    <label for="description">Description:</label>
    <textarea id="description" name="description" required></textarea>

    <label for="valid_until">Valid Until:</label>
    <input type="date" id="valid_until" name="valid_until">

    <label for="status">Status:</label>
    <select id="status" name="status">
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select>

    <button type="submit" name="add_offer">Add Offer</button>
</form>
