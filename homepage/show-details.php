<?php
session_start();
require_once '../db.php';
date_default_timezone_set('Asia/Kolkata');


if (!isset($_GET['theater_id'])) {
    echo "Theater not specified!";
    exit;
}

$theaterId = $_GET['theater_id'];

// Fetch theater name
$theaterQuery = "SELECT name FROM theaters WHERE theater_id = ?";
$stmt = $conn->prepare($theaterQuery);
$stmt->bind_param("i", $theaterId);
$stmt->execute();
$stmt->bind_result($theaterName);
$stmt->fetch();
$stmt->close();

// Get current date and time
$currentDateTime = new DateTime();
$currentDateTimeStr = $currentDateTime->format('Y-m-d H:i:s');

// Fetch shows for this theater today and in the future
$showsQuery = "
    SELECT s.show_id, s.show_time, s.movie_id, m.title, m.release_date, s.show_date
    FROM shows s 
    JOIN movies m ON s.movie_id = m.id 
    WHERE s.theater_id = ? AND CONCAT(s.show_date, ' ', s.show_time) >= ?
    ORDER BY m.title, s.show_time
";
$stmt = $conn->prepare($showsQuery);
$stmt->bind_param("is", $theaterId, $currentDateTimeStr);
$stmt->execute();
$result = $stmt->get_result();

$shows = [];

// Loop through the results and filter out past shows
while ($row = $result->fetch_assoc()) {
    $movieKey = $row['title'];
    $showTime = new DateTime($row['show_time']);
    $showDateTime = new DateTime($row['show_date'] . ' ' . $row['show_time']); // Combine date and time

    if ($showDateTime >= $currentDateTime) {
      $shows[$movieKey][] = [
        'id' => $row['show_id'],
        'time' => $showDateTime->format('H:i:s'),  // e.g., "20:00:00"
        'formatted_time' => $showTime->format('h:i A'),  // e.g., "08:00 PM"
        'show_date' => $row['show_date']
    ];
    
    }
}
$stmt->close();

?>

<!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Show Details - PickMySeat</title>
  <link rel="stylesheet" href="home.css" />
  <link rel="stylesheet" href="../fontawesome/css/all.min.css" />
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
      margin: 0;
      padding: 0;
    }

header {
  background-color: #fff4f0;
  color: #333;
  padding: 12px 20px;
  font-size: 30px;
  font-weight: bold;
  text-align: center;
  border-bottom: 1px solid #ddd;
}

.show-topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  padding: 15px 30px;
  background-color: #fff;
  border-bottom: 1px solid #ddd;
  flex-wrap: wrap;
}

.date-strip {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.date-strip .date {
  all: unset;
  padding: 8px 10px;
  background-color: #eee;
  border-radius: 5px;
  text-align: center;
  cursor: pointer;
  color: #333;
  line-height: 1.2;
  min-width: 40px;
  transition: background-color 0.3s ease, color 0.3s ease;
  text-transform: uppercase;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.date-strip .date.active {
  background-color: #ff4500;
  color: white;
  font-weight: bold;
}

.date-strip .date .day,
.date-strip .date .month {
  font-size: 12px;
  font-weight: normal;
}

.date-strip .date .date-number {
  font-size: 16px;
  font-weight: bold;
  margin: 2px 0;
}

.disabled-dates {
  background-color: #ddd !important;
  color: #999 !important;
  cursor: not-allowed !important;
  pointer-events: none;
}

.shows-container {
  padding: 20px 30px;
}

.show-card {
  background-color: #fff;
  padding: 16px;
  border-radius: 10px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 20px;
}

.show-card h3 {
  margin: 0 0 10px;
  font-size: 18px;
}

.show-buttons {
  display: flex;
  gap: 10px;
  margin-top: 10px;
  flex-wrap: wrap;
}

.showtime-btn {
  padding: 8px 12px;
  background-color: #e6fff3;
  border: 1px solid #34a853;
  color: #34a853;
  font-weight: bold;
  border-radius: 6px;
  cursor: pointer;
}

@media screen and (max-width: 768px) {
  .show-topbar {
    flex-direction: column;
    align-items: flex-start;
  }

  .show-filters {
    width: 100%;
    justify-content: flex-start;
    flex-wrap: wrap;
  }
}

  </style>
</head>
<body>
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

  <header><?php echo htmlspecialchars($theaterName); ?></header>

  <section class="show-topbar">
    <div class="date-strip">
      <?php
      $today = new DateTime();
      $selectedDate = $today->format('Y-m-d');
    for($i = 0; $i < 6; $i++) {
      $date = clone $today;
      $date->modify("+$i day");
      $day = strtoupper($date->format('D'));
      $dayNum = $date->format('d');
      $month = strtoupper($date->format('M'));
      $fullDate = $date->format('Y-m-d');

      $isAvailable = false;
      foreach ($shows as $movieShows) {
          foreach ($movieShows as $show) {
              if ($show['show_date'] == $fullDate) {
                  $isAvailable = true;
                  break 2;
              }
          }
      }

      $isToday = ($i === 0);

$activeClass = $isToday ? 'active' : '';
$disabledAttr = (!$isAvailable && !$isToday) ? 'disabled' : '';
$disabledClass = (!$isAvailable && !$isToday) ? 'disabled-dates' : '';


      echo "<button class='date $activeClass $disabledClass' data-date='$fullDate' $disabledAttr>
              <span class='day'>$day</span>
              <span class='date-number'>$dayNum</span>
              <span class='month'>$month</span>
            </button>";
  }
  ?>
</div>
  </section>

  <section class="shows-container">
  <p class="no-shows-message" style="display:none;">
  No shows available for the selected date.
</p>

  <?php if (!empty($shows)): ?>
    <?php foreach ($shows as $movieTitle => $movieShows): ?>
      <?php
        // Check if there are any upcoming shows for this movie
        $hasUpcomingShows = false;
        foreach ($movieShows as $show) {
            // Get current time
            $currentTime = time();
            // Combine the date and time fields to form a complete datetime string
            $showDateTime = $show['show_date'] . ' ' . $show['time'];

            // Convert the showtime to a Unix timestamp
            $showTime = strtotime($showDateTime);

            if ($showTime > $currentTime) {
                $hasUpcomingShows = true;
                break; // If one upcoming show is found, stop checking
            }
        }

        // If no upcoming shows, skip rendering this movie's card
        if (!$hasUpcomingShows) {
            continue;
        }
      ?>
  
      <div class="show-card">
        <h3><?php echo htmlspecialchars($movieTitle); ?></h3>
        <div class="show-details">
          <div class="show-buttons">
            <?php foreach ($movieShows as $show): ?>
              <?php 
                // Get current time
                $currentTime = time(); 

                // Combine the date and time fields to form a complete datetime string
                $showDateTime = $show['show_date'] . ' ' . $show['time'];

                // Convert the showtime to a Unix timestamp
                $showTime = strtotime($showDateTime); 
              ?>

              <?php if ($showTime > $currentTime): // Only show upcoming showtimes ?>
                <button class="showtime-btn" 
                        data-show-id="<?php echo $show['id']; ?>" 
                        data-show-date="<?php echo $show['show_date']; ?>"
                        data-show-time="<?php echo $show['time']; ?>"
                        onclick="goToSeatSelection(this)">
                  <?php echo htmlspecialchars($show['formatted_time']); ?>
                </button>
              <?php endif; ?>

            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>


  <script>
    let selectedDate = "<?php echo $currentDateTime->format('Y-m-d'); ?>";

document.querySelectorAll('.date-strip .date').forEach(button => {
  button.addEventListener('click', () => {
    if (button.disabled) return;

    selectedDate = button.getAttribute('data-date');

    document.querySelectorAll('.date-strip .date').forEach(b => b.classList.remove('active'));
    button.classList.add('active');

    updateShowtimesVisibility(selectedDate);
  });
});

function updateShowtimesVisibility(date) {
  const showCards = document.querySelectorAll(".show-card");
  const currentTime = new Date();
  let anyVisibleShow = false;

  showCards.forEach(card => {
    const showButtons = card.querySelectorAll(".showtime-btn");
    let hasVisibleShow = false;

    showButtons.forEach(showBtn => {
      const showDate = showBtn.getAttribute("data-show-date");
      const showTime = showBtn.innerText.trim();
      const [time, period] = showTime.split(' ');  // Split time and AM/PM
      let [hour, minute] = time.split(':').map(Number);

      if (period === "PM" && hour !== 12) hour += 12;
      if (period === "AM" && hour === 12) hour = 0;

      const [year, month, day] = showDate.split('-').map(Number);
      const showDateTime = new Date(year, month - 1, day, hour, minute, 0);

      if (showDate === date && showDateTime > currentTime) {
        showBtn.style.display = "inline-block";
        hasVisibleShow = true;
        anyVisibleShow = true;
      } else {
        showBtn.style.display = "none";
      }
    });

    card.style.display = hasVisibleShow ? "block" : "none";
  });

  // If no shows are available for the selected date, display the message
  const noShowsMessage = document.querySelector(".no-shows-message");
  if (!anyVisibleShow) {
    noShowsMessage.style.display = "block";
  } else {
    noShowsMessage.style.display = "none";
  }
}


function goToSeatSelection(button) {
  const showId = button.getAttribute('data-show-id');
  const date = button.getAttribute('data-show-date');
  window.location.href = `seat-selection.php?show_id=${showId}&date=${date}`;
}

window.addEventListener("DOMContentLoaded", () => {
  const defaultBtn = document.querySelector(".date-strip .date.active");
  if (defaultBtn) {
    defaultBtn.click();
  }
});
  </script>

<script src="home.js"></script>
</body>
</html>      