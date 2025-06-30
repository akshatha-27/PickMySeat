<?php
// add_movie.php

include '../db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["add_movie"])) {
    // Sanitize and collect form data
    $title = $_POST['title'] ?? '';
    $genres = $_POST['genres'] ?? '';
    $rating = $_POST['rating'] ?? '';
    $languages = $_POST['languages'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $release_date = $_POST['release_date'] ?? '';
    $formats = $_POST['formats'] ?? '';
    $about = $_POST['about'] ?? '';

    // Handle Poster Upload
$poster_path = '';
if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
    $poster_name = time() . '_' . basename($_FILES['poster']['name']);
    $target_dir = "../images/posters/";
    $poster_path = $target_dir . $poster_name;

    if (!move_uploaded_file($_FILES['poster']['tmp_name'], $poster_path)) {
        echo "<script>alert('Error: Failed to upload poster image');</script>";
        exit;
    }
}



    // Get the YouTube trailer link
   $trailer_link = $_POST['trailer'] ?? '';
$trailer_path = '';

if (!empty($trailer_link)) {
    // Parse the URL to get the query parameters
    $url_parts = parse_url($trailer_link);
    
    if (isset($url_parts['host']) && strpos($url_parts['host'], 'youtube.com') !== false && isset($url_parts['query'])) {
        parse_str($url_parts['query'], $query);
        $trailer_path = $query['v'] ?? '';
    } elseif (isset($url_parts['host']) && strpos($url_parts['host'], 'youtu.be') !== false) {
        // Handle shortened URL
        $trailer_path = ltrim($url_parts['path'], '/');
    }

    // Validate extracted ID
    if (empty($trailer_path)) {
        echo "<script>alert('Error: Invalid YouTube URL');</script>";
        exit;
    }
}
echo "Poster Path: $poster_path<br>";
echo "Trailer Path: $trailer_path<br>";


    // Check if the required fields are filled
    if (!empty($poster_path) && !empty($trailer_path)) {
        // Insert into DB
        $sql = "INSERT INTO movies (title, genres, rating, languages, duration, release_date, formats, about, poster_path, trailer_path)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $title, $genres, $rating, $languages, $duration, $release_date, $formats, $about, $poster_path, $trailer_path);

        if ($stmt->execute()) {
            echo "<script>alert('Movie added successfully!'); window.location.href='admin-dashboard.php';</script>";
        } else {
            echo "<script>alert('Database Error: " . addslashes($stmt->error) . "');</script>";

        }

        $stmt->close();
    } else {
        echo "<script>alert('Error: Missing required fields!!!!!!!!!')</script>";
    }
}
?>

<!-- Add Movie Form -->
<h2 style="text-align:center;font-size:30px;">Add Movie</h2>
<form method="POST" action="" enctype="multipart/form-data">
    <label>Title:</label>
    <input type="text" name="title" required />

    <label>Genres (comma separated):</label>
    <input type="text" name="genres" placeholder="Comedy, Drama" required />

    <label>Rating:</label>
    <input type="text" name="rating" placeholder="8.4/10 (1M+ votes)" required />

    <label>Languages:</label>
    <input type="text" name="languages" required />

    <label>Duration:</label>
    <input type="text" name="duration" required />

    <label>Release Date:</label>
    <input type="date" name="release_date" required />

    <label>Formats:</label>
    <input type="text" name="formats" required />

    <label>About the Movie:</label>
    <textarea name="about" required></textarea>

    <label>Poster Image:</label>
    <input type="file" name="poster" accept="image/*" required />

    <label>Movie Trailer (YouTube Link):</label>
    <input type="url" name="trailer" placeholder="https://www.youtube.com/watch?v=..." required />

    <button type="submit" name="add_movie">Add Movie</button>
</form>
