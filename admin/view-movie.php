<?php
include '../db.php';

// Check if ID is present
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid movie ID.";
    exit;
}

$movieId = $_GET['id'];
$sql = "SELECT * FROM movies WHERE id = $movieId LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "Movie not found.";
    exit;
}

$movie = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Movie - <?= htmlspecialchars($movie['title']) ?></title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <style>
        .movie-details {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            word-wrap: break-word;
    overflow-wrap: break-word;
    overflow-x: hidden;
        }

        .movie-header {
            display: flex;
            gap: 20px;
        }
        .movie-header img {
            width: 200px;
            height: auto;
            border-radius: 10px;
            object-fit: cover;
        }
        .movie-header div {
            flex: 1;
        }
        .movie-header h1 {
            margin-bottom: 10px;
        }
        .movie-body {
            margin-top: 20px;
            padding-top: 1px;
        }
        .movie-body p{
            color: #444;
            word-break: break-word;
    overflow-wrap: break-word;
        }
        .movie-body h3 {
    margin: 30px 0 10px 0;
    font-size: 25px;
}

        .movie-body video {
            width: 100%;
            max-height: 400px;
            border-radius: 8px;
        }
        .movie-info {
    flex: 1;
    padding-left: 30px; /* Adds space between image and text */
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.movie-info h1 {
    margin-bottom: 25px;
    font-size: 30px;
    color: #222;
    margin-top: -5px;
}

.movie-info p {
    margin-bottom: 15px; /* Space between paragraphs */
    font-size: 16px;
    color: #444;
}

.movie-info strong {
    color: #000;
}

    </style>
</head>
<body>

<div class="movie-details">
    <div class="movie-header">

        <img src="<?= htmlspecialchars($movie['poster_path']) ?>" 
     alt="Poster" 
     onerror="this.src='../images/default_poster.jpg';">


        <div class="movie-info">
            <h1><?= htmlspecialchars($movie['title']) ?></h1>
            <p><strong>Genres:</strong> <?= htmlspecialchars($movie['genres']) ?></p>
            <p><strong>Languages:</strong> <?= htmlspecialchars($movie['languages']) ?></p>
            <p><strong>Formats:</strong> <?= htmlspecialchars($movie['formats']) ?></p>
            <p><strong>Rating:</strong> <?= htmlspecialchars($movie['rating']) ?></p>
            <p><strong>Duration:</strong> <?= htmlspecialchars($movie['duration']) ?></p>
            <p><strong>Release Date:</strong> <?= date('d M Y', strtotime($movie['release_date'])) ?></p>
        </div>
    </div>

    <div class="movie-body">
        <h3>About the Movie</h3>
        <p><?= nl2br(htmlspecialchars($movie['about'])) ?></p>

        <h3>Trailer</h3>
<?php if (!empty($movie['trailer_path'])): ?>
    <iframe width="100%" height="315" 
        src="https://www.youtube.com/embed/<?= htmlspecialchars($movie['trailer_path']) ?>" 
        title="YouTube video player" 
        frameborder="0" 
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
        allowfullscreen>
    </iframe>
<?php else: ?>
    <p><em>No trailer available.</em></p>
<?php endif; ?>

    </div>
</div>

</body>
</html>
