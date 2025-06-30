<?php
session_start();
include '../db.php';
date_default_timezone_set('Asia/Kolkata');


// Check login
$userId = $_SESSION['id'] ?? $_SESSION['google_id'] ?? null;
if (!$userId) {
    header("Location: ../login/index.html");
    exit;
}

// Fetch user's bookings
$sql = "SELECT b.booking_id, b.show_id, b.seats, b.total_price, b.status, b.booking_time,
               m.title AS movie_title, t.name AS theater_name,t.location, s.show_time, s.show_date
        FROM bookings b
        JOIN shows s ON b.show_id = s.show_id
        JOIN movies m ON s.movie_id = m.id
        JOIN theaters t ON s.theater_id = t.theater_id
        WHERE b.user_id = ?
        ORDER BY b.booking_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Bookings</title>
  <link rel="stylesheet" href="home.css">
  <link rel="stylesheet" href="../fontawesome/css/all.min.css" />
  <style>
    .booking-card {
      border: 1px solid #ccc;
      padding: 15px;
      margin: 10px 0;
      border-radius: 8px;
      background-color: #f8f8f8;
      width: 40%;
      margin-left:30%;
    }
    .booking-card h3 {
      margin-bottom: 10px;
      font-size: 20px;
    }
    .booking-card .info {
      margin-bottom: 5px;
    }
    .booking-card .btn {
      margin-right: 10px;
      padding: 8px 12px;
      background: #ff4500;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      /* margin-left: 40%; */
      margin-top: 10px;
    }
    .container h2{
        margin: 20px;
        text-align: center;
        font-size: 30px;
    }
    /* ---------- Mobile Devices (max-width: 768px) ---------- */
@media (max-width: 768px) {
  .booking-card {
    width: 90%;
    margin-left: auto;
    margin-right: auto;
    padding: 12px;
  }

  .booking-card h3 {
    font-size: 1rem;
  }

  .booking-card .btn {
    font-size: 0.9rem;
    padding: 6px 10px;
    display: block;
    width: 100%;
    margin: 10px 0 0 0;
  }

  .container h2 {
    font-size: 1.5rem;
    margin: 15px;
  }
}

/* ---------- Tablets (769px to 1024px) ---------- */
@media (min-width: 769px) and (max-width: 1024px) {
  .booking-card {
    width: 70%;
    margin-left: auto;
    margin-right: auto;
  }

  .booking-card h3 {
    font-size: 1.2rem;
  }

  .booking-card .btn {
    font-size: 1rem;
    padding: 8px 12px;
  }

  .container h2 {
    font-size: 1.8rem;
  }
}

  </style>
</head>
<body>
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

  <div class="container">
    <h2>My Bookings</h2>

    <?php if ($result->num_rows === 0): ?>
      <p>You haven't booked any tickets yet.</p>
    <?php else: ?>
        <?php while ($row = $result->fetch_assoc()):
  $showTimestamp = strtotime($row['show_date'] . ' ' . $row['show_time']);
  $canCancel = $showTimestamp - time() > 3600 && $row['status'] !== 'cancelled';
?>
  <div class="booking-card">
    <h3><?= htmlspecialchars($row['movie_title']) ?></h3>
    <div class="info">Theater : <?= htmlspecialchars($row['theater_name']) ?>, <?= htmlspecialchars($row['location']) ?></div>
    <div class="info">Date : <?= date("d M Y", strtotime($row['show_date'])) ?></div>
    <div class="info">Time : <?= date("g:i A", strtotime($row['show_time'])) ?></div>
    <div class="info">Seats : <?= htmlspecialchars($row['seats']) ?></div>
    <div class="info">Total Amount : ₹<?= $row['total_price'] ?></div>
    <div class="info">Status : <?= $row['status'] ?></div>
    

    <form method="GET" action="ticket.php" style="display:inline;">
      <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
      <button class="btn">View Ticket</button>
    </form>

    <form method="GET" action="ticket.php" target="_blank" style="display:inline;">
  <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
  <input type="hidden" name="download" value="1">
  <button class="btn" style="background-color: #ff4500;">Download Ticket</button>
</form>


    <?php if ($canCancel): ?>
      <form method="GET" action="cancel-booking.php" style="display:inline;">
        <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
        <button class="btn" style="background-color: #dc3545;" onclick="return confirm('Are you sure you want to cancel this ticket?');">Cancel Ticket</button>
      </form>
    <?php endif; ?>
  </div>
<?php endwhile; ?>

    <?php endif; ?>
  </div>
  <script src="home.js"></script>
</body>
</html>
