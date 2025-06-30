<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../login/index.html");
    exit();
}

// Get user info from session (with fallbacks)
$firstName = $_SESSION['first_name'] ?? 'User';
$lastName = $_SESSION['last_name'] ?? '';
$email = $_SESSION['email'] ?? 'Not Available';
$phone = $_SESSION['phone'] ?? 'Not Provided';
$dob = $_SESSION['dob'] ?? 'Not Provided';
$gender = $_SESSION['gender'] ?? 'Not Provided';
$location = $_SESSION['location'] ?? 'Not Provided';

$fullName = trim($firstName . ' ' . $lastName);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>User Profile - PickMySeat</title>
  <link rel="stylesheet" href="../fontawesome/css/all.min.css" />
  <link rel="stylesheet" href="home.css">
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      background-color: #f7f7f7;
      font-family: "Segoe UI", sans-serif;
      margin: 0;
      padding: 0;
    }
    .profile-container {
      max-width: 600px;
      margin: 50px auto;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
      padding: 30px;
    }
    .profile-header {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 20px;
    }
    .profile-header img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      border: 3px solid #ff4500;
      object-fit: cover;
    }
    .profile-header h2 {
      margin: 0 0 5px;
      font-size: 24px;
    }
    .profile-header p {
      margin: 0;
      font-size: 14px;
      color: #555;
    }
    .profile-section {
      margin-top: 25px;
    }
    .profile-section p {
      font-size: 16px;
      margin-bottom: 10px;
    }
    .label {
      font-weight: bold;
    }
    .btn-group {
      margin-top: 30px;
    }
    .btn-orange {
      background-color: #ff4500;
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 6px;
      text-decoration: none;
      display: inline-block;
      font-size: 14px;
      margin-right: 10px;
      margin-bottom: 10px;
      cursor: pointer;
      transition: background-color 0.2s ease-in-out;
    }
    .btn-orange:hover {
      background-color: #e03e00;
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

<div class="profile-container">
  <div class="profile-header">
    <img src="../images/profile.jpg" 
         alt="User Image" 
         onerror="this.onerror=null; this.src='../images/default_avatar.png';">
    <div>
      <h2><?php echo htmlspecialchars($fullName); ?></h2>
      <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($email); ?></p>
    </div>
  </div>

  <div class="profile-section">
    <p><span class="label">Phone:</span> <?php echo htmlspecialchars($phone); ?></p>
    <p><span class="label">Date of Birth:</span> <?= date("d M Y", strtotime($dob)) ?></p>
    <p><span class="label">Gender:</span> <?php echo ucfirst(htmlspecialchars($gender)); ?></p>
    <p><span class="label">Location:</span> <?php echo htmlspecialchars($location); ?></p>
  </div>

  <div class="btn-group">
    <a href="booking-history.php" class="btn-orange"><i class="fas fa-ticket-alt"></i> My Bookings</a>
    <a href="edit_profile.php" class="btn-orange"><i class="fas fa-user-edit"></i> Edit Profile</a>
    <a href="#" onclick="confirmLogout()" class="btn-orange"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</div>

<script src="home.js"></script>
</body>
</html>
