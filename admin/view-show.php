<?php
include '../db.php';

if (!isset($_GET['id'])) {
    echo "Show ID is missing.";
    exit;
}

$show_id = intval($_GET['id']);

// Fetch show details
$sql = "SELECT s.*, 
               m.title AS movie_title, 
               t.name AS theater_name, 
               t.location AS theater_location 
        FROM shows s
        JOIN movies m ON s.movie_id = m.id
        JOIN theaters t ON s.theater_id = t.theater_id
        WHERE s.show_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $show_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Show not found.";
    exit;
}

$show = $result->fetch_assoc();
?>

<!DOCTYPE html>

<html>
<head>
    <title><?= htmlspecialchars($show['movie_title']) ?> - Show Details</title>
    <style>
        html, body {
    margin: 0;
    padding: 0;
    height: 100vh;
}

body {
display: flex;
justify-content: center;      
align-items: center;          
background: #f4f4f4;
font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.show-container {
width: 600px;
height: 300px;
background: #fff;
padding: 30px;
border-radius: 10px;
box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
text-align: center;
}

    .movie-title {
        font-size: 28px;
        font-weight: bold;
        color: #ff4500;
        margin-bottom: 25px;
    }
    .show-info-wrapper {
        display: flex;
        justify-content: center;
    }
    .show-info {
        display: grid;
        grid-template-columns: auto auto;
        gap: 15px 20px;
        text-align: left;
    }
    .label {
        font-weight: 600;
        color: #333;
    }
    .value {
        color: #444;
    }
</style>

</head>
<body>

<div class="show-container">
    <div class="movie-title"><?= htmlspecialchars($show['movie_title']) ?></div>

<div class="show-info-wrapper">
    <div class="show-info">
        <div class="label">Theater:</div>
        <div class="value"><?= htmlspecialchars($show['theater_name']) ?></div>

        <div class="label">Location:</div>
        <div class="value"><?= htmlspecialchars($show['theater_location']) ?></div>

        <div class="label">Show Date:</div>
        <div class="value"><?= htmlspecialchars($show['show_date']) ?></div>

        <div class="label">Show Time:</div>
        <div class="value"><?= date("g:i A", strtotime($show['show_time'])) ?></div>

        <div class="label">VIP Price:</div>
        <div class="value">₹<?= number_format($show['vip_price'], 2) ?></div>

        <div class="label">Gold Price:</div>
        <div class="value">₹<?= number_format($show['gold_price'], 2) ?></div>

        <div class="label">Silver Price:</div>
        <div class="value">₹<?= number_format($show['silver_price'], 2) ?></div>
    </div>
</div>

</div>

</body>
</html>                   
