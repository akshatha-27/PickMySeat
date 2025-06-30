<?php
include '../db.php';

// Validate theater ID
if (!isset($_GET['id'])) {
    echo "Theater ID is missing.";
    exit;
}

$theaterId = $_GET['id'];

// Fetch theater details
$sql = "SELECT * FROM theaters WHERE theater_id = $theaterId";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "Theater not found.";
    exit;
}

$theater = mysqli_fetch_assoc($result);

// Handle update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $location = $_POST['location'];

    $updateSql = "UPDATE theaters SET 
                    name = '$name',
                    location = '$location'
                  WHERE theater_id = $theaterId";

    if (mysqli_query($conn, $updateSql)) {
        echo "<script>alert('✅ Theater updated successfully.'); window.location.href='admin-dashboard.php?section=theaters';</script>";
    } else {
        echo "<script>alert('Error updating theater: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Theater</title>
    <link rel="stylesheet" href="admin.css" />
</head>
<body>
    <div class="add-movie-form">
        <h2 style="text-align:center;font-size:30px;margin:30px">Edit Theater</h2>
        <form method="POST" style="width:45%">
            <label>Theater Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($theater['name']) ?>" required />

            <label>Location:</label>
            <input type="text" name="location" value="<?= htmlspecialchars($theater['location']) ?>" required />

            <button type="submit">Update Theater</button>
        </form>
    </div>
</body>
</html>
