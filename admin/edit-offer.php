<?php
include '../db.php';

// Validate offer ID
if (!isset($_GET['id'])) {
    echo "Offer ID is missing.";
    exit;
}

$offerId = $_GET['id'];

// Fetch offer details
$sql = "SELECT * FROM offers WHERE id = $offerId";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "Offer not found.";
    exit;
}

$offer = mysqli_fetch_assoc($result);

// Handle update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $description = $_POST['description'];
    $valid_until = $_POST['valid_until'];
    $status = $_POST['status'];

    $updateSql = "UPDATE offers SET 
                    description = '$description',
                    valid_until = '$valid_until',
                    status = '$status'
                  WHERE id = $offerId";

    if (mysqli_query($conn, $updateSql)) {
        echo "<script>alert('✅ Offer updated successfully.'); window.location.href='admin-dashboard.php?section=offers';</script>";
    } else {
        echo "<script>alert('Error updating offer: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Offer</title>
    <link rel="stylesheet" href="admin.css" />
</head>
<body>
    <div class="add-movie-form">
        <h2 style="text-align:center;font-size:30px;margin:30px">Edit Offer</h2>
        <form method="POST" style="width:45%">

            <label>Description:</label>
            <textarea name="description" required><?= htmlspecialchars($offer['description']) ?></textarea>

            <label>Valid Until:</label>
            <input type="date" name="valid_until" value="<?= htmlspecialchars($offer['valid_until']) ?>" />

            <label>Status:</label>
            <select name="status" required>
                <option value="active" <?= $offer['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                <option value="expired" <?= $offer['status'] == 'expired' ? 'selected' : '' ?>>Expired</option>
                <option value="disabled" <?= $offer['status'] == 'disabled' ? 'selected' : '' ?>>Disabled</option>
            </select>

            <button type="submit">Update Offer</button>
        </form>
    </div>
</body>
</html>
