<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PickMySeat - Movie Booking</title>
    <link rel="stylesheet" href="../fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="home.css"/>
  </head>

  <body>
    <div class="main-container">
      <nav class="navbar">
        <div class="logo">
          PickMySeat
        </div>

<div class="search-container">
  <div class="search-box">
    <input type="search" id="searchInput" class="search-input" placeholder="Search for movies and theatres" aria-label="Search Movies" autocomplete="off"/>
    <span class="search-icon"><i class="fas fa-search"></i></span>
  </div>
  <ul id="searchResults" class="results-list"></ul>
</div>


        <div class="nav-links">
          <a href="#movies">Movies</a>
          <a href="#theatres">Theatres</a>
          <a href="#offers">Offers</a>
          <a href="#about">About Us</a>

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
              <!-- Profile Dropdown Menu -->
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


<!-- Main Banner Carousel -->
 <div class="carousel-container">
                <div class="carousel">
                </div>
            </div>
          

            <?php
// DB connection
include '../db.php';

$sql = "SELECT id, title, genres, poster_path FROM movies ORDER BY release_date DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0): ?>
  <div id="movies" class="movies-section">
    <h2>Currently Screening</h2>
        <div class="movies-wrapper">
        <button class="scroll-btn left-btn" onclick="scrollMovies('left')">◀</button>
        <div class="movies-carousel">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="movie-card" data-title="<?php echo htmlspecialchars($row['title']); ?>">
          <img src="<?php echo htmlspecialchars($row['poster_path']); ?>" alt="Movie Poster" />
          <h3><?php echo htmlspecialchars($row['title']); ?></h3>
          <p><?php echo htmlspecialchars($row['genres']); ?></p>
          <div class="details">
            <button onclick="window.location.href='book-now.php?id=<?php echo $row['id']; ?>'">Book Now</button>
          </div>
        </div>
      <?php endwhile; ?>
      <!-- Right Scroll Button -->
      <button class="scroll-btn right-btn" onclick="scrollMovies('right')">▶</button>
    </div>
<?php else: ?>
  <p style="text-align:center; padding: 20px;">No movies available currently.</p>
<?php endif; ?>
</div>
</div>

<!-- Theaters Section -->
  <section class="theatres-section" id="theatres">
  <h2>Our Partner Theatres</h2>
  <div class="theatre-list">
    <?php
    // Fetch theaters from the database
    $sql = "SELECT * FROM theaters";
    $result = mysqli_query($conn, $sql);

    // Check if there are any theaters
    if (mysqli_num_rows($result) > 0) {
        // Loop through and display each theater
        while ($row = mysqli_fetch_assoc($result)) {
            ?>
            <div class="theatre-item">
                <div class="theatre-name">🎬 <?= htmlspecialchars($row['name']) ?></div>
                <div class="theatre-location"><?= htmlspecialchars($row['location']) ?></div>
                <a href="show-details.php?theater_id=<?= $row['theater_id'] ?>" class="view-shows-btn">View Shows</a>
            </div>
            <?php
        }
    } else {
        echo "<p>No theaters available at the moment.</p>";
    }
    ?>
  </div>
</section>

<!-- Offers Section -->
<section class="offers-section" id="offers">
  <h2>Exclusive Offers</h2>
  <div class="offers-wrapper">
    <?php
    // Fetch active offers from the database
    $sql = "SELECT * FROM offers WHERE status = 'active' ORDER BY created_at ASC";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <div class="offer-item">
          <div class="offer-details">
            <h3><?= htmlspecialchars($row['description']) ?></h3>
            <?php if (!empty($row['valid_until'])): ?>
              <p>Valid Until: <?= date("d M Y", strtotime($row['valid_until'])) ?></p>
            <?php endif; ?>
          </div>
        </div>
        <?php
      }
    } else {
      echo "<p>No active offers at the moment.</p>";
    }
    ?>
  </div>
</section>


</div>

<section id="about" class="about-section">
  <div class="container">
    <h2>About Us</h2>
    <p>
      <strong>PickMySeat</strong> is an innovative Online Movie Ticket Booking System designed to simplify and enhance the movie ticketing experience. With PickMySeat, users can effortlessly browse through a wide range of available movies, explore show timings, choose their preferred seats, and make secure online payments—all in just a few clicks. By eliminating the need for physical ticket counters and long queues, this platform ensures a seamless and hassle-free booking experience for moviegoers.
    </p>
    <p>
      Key features include user authentication, real-time seat availability updates, secure payment gateways, and instant booking confirmation. Users can also access movie details, view their booking history, download e-tickets, and even cancel bookings when necessary. For theater owners, the system offers a comprehensive management tool to oversee movie schedules, ticket pricing, and seat allocation, making the process efficient and streamlined. <strong>PickMySeat</strong> is dedicated to providing a convenient, easy-to-use, and secure platform for both customers and theater operators alike.
    </p>
    </div>
    <div class="contact-info">
      <p>For inquiries, please contact us at <a href="mailto:support@pickmyseat.com">support@pickmyseat.com</a>.</p>
    </div>
</section>


    <footer class="footer">
      <div class="social-icons">
        <i class="fab fa-facebook"></i>
        <i class="fab fa-twitter"></i>
        <i class="fab fa-instagram"></i>
        <i class="fab fa-youtube"></i>
        <i class="fab fa-linkedin"></i>
      </div>
      <p>Copyright 2025 © PickMySeat. All Rights Reserved.</p>
      <p>
        The content and images used on this site are copyright protected and
        belong to their respective owners.
      </p>
    </footer>

    <script src="../login/main.js"></script>
    <script src="home.js"></script>
   
  </body>
</html>
