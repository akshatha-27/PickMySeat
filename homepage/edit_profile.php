<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../login/index.html");
    exit();
}

// Get user info from session
$firstName = $_SESSION['first_name'] ?? '';
$lastName = $_SESSION['last_name'] ?? '';
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone'] ?? '';
$dob = $_SESSION['dob'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$location = $_SESSION['location'] ?? '';
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Account Settings - PickMySeat</title>
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

    .settings-container {
      max-width: 550px;
      margin: 30px auto;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
      padding: 30px;
      position: relative;
    }

    .settings-header h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #ff4500;
      font-size: 30px;
    }

    .form-group {
      margin-bottom: 20px;
      display: flex;
      align-items: center;
    }

    label.form-label {
      width: 140px; 
      margin: 0;
      margin-right: 10px;
      font-weight: 500;
      color: #333;
    }

    input[type="text"],
    input[type="email"],
    input[type="date"],
    select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
      flex: 1;
    }

    input[readonly] {
      background-color: #f1f1f1;
      cursor: not-allowed;
    }

    .btn-orange {
      background-color: #ff4500;
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.2s ease-in-out;
      width: 50%;
      margin-top: 20px;  
    }

    .btn-orange:hover {
      background-color: #e03e00;
    }

    .text-center {
      text-align: center;
    }

    .alert {
  position: absolute;
  right: -350px; 
  top: 30px;
  width: 300px;
  padding: 12px;
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
  border-radius: 6px;
  font-size: 16px;
  text-align: center;
  box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
}

/* ---------- Mobile Devices (max-width: 768px) ---------- */
@media (max-width: 768px) {
  .settings-container {
    margin: 20px 10px;
    padding: 20px;
  }

  .settings-header h2 {
    font-size: 24px;
  }

  .form-group {
    flex-direction: column;
    align-items: flex-start;
  }

  label.form-label {
    width: 100%;
    margin-bottom: 6px;
  }

  input[type="text"],
  input[type="email"],
  input[type="date"],
  select {
    width: 100%;
    font-size: 15px;
  }

  .btn-orange {
    width: 100%;
    font-size: 15px;
    padding: 10px 20px;
  }

  .alert {
    position: static;
    margin-top: 15px;
    width: 100%;
  }
}

/* ---------- Tablets (769px to 1024px) ---------- */
@media (min-width: 769px) and (max-width: 1024px) {
  .settings-container {
    padding: 25px;
    margin: 25px auto;
  }

  .settings-header h2 {
    font-size: 28px;
  }

  .btn-orange {
    width: 70%;
    font-size: 16px;
  }

  .alert {
    right: 10px;
    width: 320px;
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

<div class="settings-container">

  <div class="settings-header">
    <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
  </div>

  <form method="POST" action="update_profile.php">
    <div class="form-group">
      <label class="form-label">First Name</label>
      <input type="text" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" required>
    </div>

    <div class="form-group">
      <label class="form-label">Last Name</label>
      <input type="text" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>">
    </div>

    <div class="form-group">
      <label class="form-label">Email (Readonly)</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
    </div>

    <div class="form-group">
      <label class="form-label">Phone</label>
      <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
    </div>

    <div class="form-group">
      <label class="form-label">Date of Birth</label>
      <input type="date" name="dob" value="<?php echo htmlspecialchars($dob); ?>">
    </div>

    <div class="form-group">
      <label class="form-label">Gender</label>
      <select name="gender">
        <option value="" <?php if (!isset($gender) || $gender == '') echo 'selected'; ?>>-- Select Gender --</option>
        <option value="male" <?php if ($gender == 'male') {
            echo 'selected';
        } ?>>Male</option>
        <option value="female" <?php if ($gender == 'female') {
            echo 'selected';
        } ?>>Female</option>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label">Location</label>
      <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>">
    </div>

    <div class="text-center">
      <button type="submit" class="btn-orange" id="submitBtn">Save Changes</button>
    </div>
  </form>
  <?php if (isset($_GET['updated'])): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle" style="margin-right: 10px;"></i>
    Profile details updated successfully.
  </div>
<?php endif; ?>
</div>

<script>
  // Allow form submit only when Save Changes button is clicked
  let allowSubmit = false;

  submitBtn.onclick = () => allowSubmit = true;

  document.querySelector("form").onsubmit = (e) => {
    if (!allowSubmit) e.preventDefault();
    allowSubmit = false;
  };

  document.querySelector("form").onkeydown = (e) => {
    if (e.key === "Enter") e.preventDefault();
  };


  setTimeout(() => {
    const alertBox = document.querySelector('.alert');
    if (alertBox) {
      alertBox.style.display = 'none';
    }
  }, 3000);

   // Remove ?updated=1 from URL without reloading
    if (window.history.replaceState) {
      const url = new URL(window.location);
      url.searchParams.delete('updated');
      window.history.replaceState({}, document.title, url.pathname);
    }
</script>

<script src="home.js"></script>
</body>
</html>
