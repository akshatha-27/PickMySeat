<?php
include '../db.php';

// Validate show ID
if (!isset($_GET['id'])) {
    echo "Show ID is missing.";
    exit;
}

$showId = $_GET['id'];

// Fetch show details
$sql = "SELECT * FROM shows WHERE show_id = $showId";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "Show not found.";
    exit;
}

$show = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $theater_id = $_POST['theater_id'];
    $movie_id = $_POST['movie_id'];
    $show_date = $_POST['show_date'];
    $show_time = $_POST['show_time'];
    $vip_price = $_POST['vip_price'];
    $gold_price = $_POST['gold_price'];
    $silver_price = $_POST['silver_price'];

    $updateSql = "UPDATE shows SET 
                    theater_id = ?, 
                    movie_id = ?, 
                    show_date = ?, 
                    show_time = ?, 
                    vip_price = ?, 
                    gold_price = ?, 
                    silver_price = ?
                  WHERE show_id = ?";

    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("iissdddi", $theater_id, $movie_id, $show_date, $show_time, $vip_price, $gold_price, $silver_price, $showId);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Show updated successfully.'); window.location.href='admin-dashboard.php?section=shows';</script>";
        exit;
    } else {
        echo "<script>alert('Error updating show: " . $stmt->error ."');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Show</title>
    <link rel="stylesheet" href="admin.css" />
</head>
<body>
    <div class="add-movie-form">
    <h2 style="text-align:center;font-size:30px;margin:30px;">Edit Show</h2>
    <form method="POST">
        <label>Select Theater:</label>
        <select name="theater_id" required>
            <option value="">-- Select --</option>
            <?php
            $theaters = mysqli_query($conn, "SELECT * FROM theaters");
            while ($theater = mysqli_fetch_assoc($theaters)) {
                $selected = ($theater['theater_id'] == $show['theater_id']) ? 'selected' : '';
                echo "<option value='{$theater['theater_id']}' $selected>{$theater['name']} ({$theater['location']})</option>";
            }
            ?>
        </select>

        <label>Select Movie:</label>
        <select name="movie_id" required>
            <option value="">-- Select --</option>
            <?php
            $movies = mysqli_query($conn, "SELECT * FROM movies");
            while ($movie = mysqli_fetch_assoc($movies)) {
                $selected = ($movie['id'] == $show['movie_id']) ? 'selected' : '';
                echo "<option value='{$movie['id']}' $selected>{$movie['title']}</option>";
            }
            ?>
        </select>

        <label>Show Date:</label>
        <input type="date" name="show_date" value="<?= $show['show_date'] ?>" required>

        <label>Show Time:</label>
        <input type="time" name="show_time" value="<?= $show['show_time'] ?>" required>

        <label>VIP Price:</label>
        <input type="number" name="vip_price" min="0" step="1" value="<?= $show['vip_price'] ?>" required>

        <label>Gold Price:</label>
        <input type="number" name="gold_price" min="0" step="1" value="<?= $show['gold_price'] ?>" required>

        <label>Silver Price:</label>
        <input type="number" name="silver_price" min="0" step="1" value="<?= $show['silver_price'] ?>" required>

        <button type="submit">Update Show</button>
    </form>
    </div>
</body>
</html>
