<?php
session_start();
include '../db.php';

$movieId = $_GET['id'] ?? null;

if (!$movieId) {
    die("Movie ID is missing.");
}

// Fetch movie details
$sql = "SELECT * FROM movies WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $movieId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Movie not found.");
}

$movie = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo $movie['title']; ?> - Movie Details | PickMySeat</title>
  <link rel="stylesheet" href="home.css">
  <link rel="stylesheet" href="../fontawesome/css/all.min.css" />
  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", sans-serif;
      background-color: #f5f5f5;
    }

    .container {
      max-width: 1100px;
      margin: 32px auto;
      padding: 0 16px;
      display: flex;
      gap: 45px;
      align-items: stretch;
      flex-wrap: wrap;
    }

    .trailer {
      flex: 1 1 300px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .trailer video {
      width: 100%;
      height: 100%;
      min-height: 300px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .details {
      flex: 2;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 12px 0;
    }

    .title {
      font-size: 2rem;
      font-weight: bold;
      margin-bottom: 8px;
    }

    .tags {
      margin-bottom: 12px;
    }

    .tag {
      display: inline-block;
      background-color: rgb(255, 160, 125);
      color: white;
      padding: 4px 12px;
      border-radius: 5px;
      margin: 4px 6px 0 0;
      font-size: 0.85em;
    }

    .info {
      margin: 15px 0;
      font-size: 0.95em;
      color: #444;
    }

    .rating {
      font-weight: bold;
      font-size: 1.1em;
      color: #ff4500;
    }

    .book-btn {
      margin-top: 24px;
      padding: 13px 26px!important;
      background-color: #ff4500;
      color: white;
      border: none;
      font-size: 1.1rem!important;
      font-weight: bold;
      border-radius: 8px;
      cursor: pointer;
      width: fit-content;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .book-btn:hover {
      background-color: #e64000;
      transform: scale(1.05);
    }

    .about {
      max-width: 1100px;
      margin: 0 auto 40px auto;
      padding: 0 16px;
    }

    .about h2 {
      font-size: 1.6em;
      color: #ff4500;
      margin-bottom: 8px;
    }

    .about p {
      font-size: 1em;
      color: #333;
      line-height: 1.6;
      word-break: break-word;
    overflow-wrap: break-word;
    white-space: normal;
    overflow-x: hidden;
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
        align-items: center;
      }

      .details {
        align-items: center;
        text-align: center;
      }

      .book-btn {
        width: 100%;
      }

      .trailer video {
        min-height: 200px;
      }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="logo">PickMySeat</div>

  <div class="search-container">
  <div class="search-box">
    <input type="search" id="searchInput" class="search-input" placeholder="Search for movies and theatres" aria-label="Search Movies" autocomplete="off"/>
    <span class="search-icon"><i class="fas fa-search"></i></span>
  </div>
  <ul id="searchResults" class="results-list"></ul>
</div>

  <div class="nav-links">
    <a href="home.php">Home</a>
    <a href="home.php#movies">Movies</a>
    <a href="home.php#theatres">Theatres</a>
    <a href="home.php#offers">Offers</a>
    <a href="home.php#about">About Us</a>

    <?php if (isset($_SESSION['user']) && isset($_SESSION['role']) && $_SESSION['role'] !== 'admin'): ?>
          <div class="profile-toggle" onclick="toggleProfileMenu()">
            <div class="profile-info" style="display: flex; align-items: center; gap: 8px;">
            <div class="profile-icon">
              <i class="fa-solid fa-user"></i>
            </div>
            <span class="profile-name">
            <?php echo htmlspecialchars($_SESSION['user']); ?>
            <i class="fa-solid fa-angle-down" style="margin-left: 5px;color:#777"></i> 
            </span>
            </div> 
        <div id="profile-menu" class="profile-menu">
          <a href="view_profile.php">View Profile</a>
          <a href="edit_profile.php">Edit Profile</a>
          <a href="booking-history.php">My Bookings</a>
          <a href="#" onclick="confirmLogout()">Logout</a>
        </div>
      </div>
    <?php else: ?>
      <a href="../login/index.html" class="sign-in-btn">Sign In</a>
    <?php endif; ?>
  </div>
</nav>

<!-- Movie Details -->
<div class="container">
  <div class="trailer">
    <iframe width="100%" height="315" 
        src="https://www.youtube.com/embed/<?= htmlspecialchars($movie['trailer_path']) ?>" 
        title="YouTube video player" 
        frameborder="0" 
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
        allowfullscreen>
    </iframe>
  </div>

  <div class="details">
    <div>
      <div class="title"><?php echo htmlspecialchars($movie['title']); ?></div>
      <div class="tags">
        <?php
          $genres = explode(',', $movie['genres']);
          foreach ($genres as $genre) {
            echo '<span class="tag">' . htmlspecialchars(trim($genre)) . '</span>';
          }
        ?>
      </div>
      <div class="rating">⭐ <?php echo htmlspecialchars($movie['rating']); ?></div>
      <div class="info">Language: <?php echo htmlspecialchars($movie['languages']); ?></div>
      <div class="info">Duration: <?php echo htmlspecialchars($movie['duration']); ?></div>
      <div class="info">Release Date: <?= date("d M Y", strtotime($movie['release_date'])) ?></div>
      <div class="info">Format: <?php echo htmlspecialchars($movie['formats']); ?></div>
    </div>

    <button class="book-btn" onclick="window.location.href='show-timings.php?id=<?php echo $movie['id']; ?>'">Book Tickets</button>
  </div>
</div>

<div class="about">
  <h2>About the movie</h2>
  <p><?php echo nl2br(htmlspecialchars($movie['about'])); ?></p>
</div>

<script src="home.js"></script>
</body>
</html>