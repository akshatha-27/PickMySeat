<?php
include '../db.php';

if (!isset($_GET['id'])) {
    echo "Movie ID is missing.";
    exit;
}

$movieId = $_GET['id'];

// Fetch existing movie data
$sql = "SELECT * FROM movies WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movieId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Movie not found.";
    exit;
}

$movie = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $genres = $_POST['genres'] ?? '';
    $rating = $_POST['rating'] ?? '';
    $languages = $_POST['languages'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $release_date = $_POST['release_date'] ?? '';
    $formats = $_POST['formats'] ?? '';
    $about = $_POST['about'] ?? '';

    // Handle new poster upload (optional)
    $poster_path = $movie['poster_path'];
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === 0) {
        $poster_name = time() . '_' . basename($_FILES['poster']['name']);
        $target_dir = "../images/posters/";
        $poster_path = $target_dir . $poster_name;

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        move_uploaded_file($_FILES['poster']['tmp_name'], $poster_path);
    }

    // Process trailer link
    $trailer_link = $_POST['trailer'] ?? '';
    $trailer_path = '';

    if (!empty($trailer_link)) {
        $url_parts = parse_url($trailer_link);

        if (isset($url_parts['host']) && strpos($url_parts['host'], 'youtube.com') !== false && isset($url_parts['query'])) {
            parse_str($url_parts['query'], $query);
            $trailer_path = $query['v'] ?? '';
        } elseif (isset($url_parts['host']) && strpos($url_parts['host'], 'youtu.be') !== false) {
            $trailer_path = ltrim($url_parts['path'], '/');
        }

        if (empty($trailer_path)) {
            echo "Error: Invalid YouTube URL.";
            exit;
        }
    } else {
        $trailer_path = $movie['trailer_path']; // Retain existing if not changed
    }

    // Update DB
    $update_sql = "UPDATE movies SET 
                    title = ?, genres = ?, rating = ?, languages = ?, duration = ?, 
                    release_date = ?, formats = ?, about = ?, poster_path = ?, trailer_path = ?
                   WHERE id = ?";

    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssssssssi", $title, $genres, $rating, $languages, $duration, $release_date, $formats, $about, $poster_path, $trailer_path, $movieId);

    if ($stmt->execute()) {
        echo "<script>alert('Movie updated successfully!'); window.location.href='admin-dashboard.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error updating movie: " . $stmt->error . "');</script>";
    }

    $stmt->close();
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
<!-- Edit Movie Form -->
<h2 style="text-align:center;font-size:30px;">Edit Movie</h2>
<form method="POST" enctype="multipart/form-data">
    <label>Title:</label>
    <input type="text" name="title" value="<?= htmlspecialchars($movie['title']) ?>" required />

    <label>Genres (comma separated):</label>
    <input type="text" name="genres" value="<?= htmlspecialchars($movie['genres']) ?>" required />

    <label>Rating:</label>
    <input type="text" name="rating" value="<?= htmlspecialchars($movie['rating']) ?>" required />

    <label>Languages:</label>
    <input type="text" name="languages" value="<?= htmlspecialchars($movie['languages']) ?>" required />

    <label>Duration:</label>
    <input type="text" name="duration" value="<?= htmlspecialchars($movie['duration']) ?>" required />

    <label>Release Date:</label>
    <input type="date" name="release_date" value="<?= date('Y-m-d', strtotime($movie['release_date'])) ?>" required />

    <label>Formats:</label>
    <input type="text" name="formats" value="<?= htmlspecialchars($movie['formats']) ?>" required />

    <label>About the Movie:</label>
    <textarea name="about" required><?= htmlspecialchars($movie['about']) ?></textarea>

    <label>Poster Image:</label>
    <input type="file" name="poster" accept="image/*" />
    <?php if (!empty($movie['poster_path'])): ?>
        <div style="margin:10px 0;">
            <strong>Current Poster:</strong><br>
            <img src="<?= $movie['poster_path'] ?>" alt="Poster" style="max-width:100px;" />
        </div>
    <?php endif; ?>

    <label>Movie Trailer (YouTube Link):</label>
    <input type="url" name="trailer" placeholder="https://www.youtube.com/watch?v=..." value="https://www.youtube.com/watch?v=<?= htmlspecialchars($movie['trailer_path']) ?>" required />

    <button type="submit">Update Movie</button>
</form>
</div>
</body>
</html>