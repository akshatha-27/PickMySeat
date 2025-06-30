<?php
session_start();
include '../db.php'; // DB connection
date_default_timezone_set('Asia/Kolkata');


if (!isset($_GET['id'])) {
    echo "Movie ID is missing.";
    exit;
}

$movieId = $_GET['id'];

// Fetch movie details
$movieQuery = "SELECT * FROM movies WHERE id = $movieId";
$movieResult = mysqli_query($conn, $movieQuery);
$movie = mysqli_fetch_assoc($movieResult);

if (!$movie) {
    echo "Movie not found.";
    exit;
}

// Get the current date and time
$currentDateTime = new DateTime();
$selectedDate = isset($_GET['date']) ? $_GET['date'] : $currentDateTime->format('Y-m-d');


// Fetch showtimes that are after the current date and time
$showQuery = "
    SELECT s.show_id, s.show_time, s.show_date, t.name AS theater_name, t.location, t.theater_id
    FROM shows s
    JOIN theaters t ON s.theater_id = t.theater_id
    WHERE s.movie_id = $movieId
    AND (
        s.show_date > '" . $currentDateTime->format('Y-m-d') . "' OR
        (s.show_date = '" . $currentDateTime->format('Y-m-d') . "' AND s.show_time >= '" . $currentDateTime->format('H:i:s') . "')
    )
    AND s.show_date = '$selectedDate'
    ORDER BY t.name, s.show_time
";


$showResult = mysqli_query($conn, $showQuery);

$theaters = [];

while ($row = mysqli_fetch_assoc($showResult)) {
    $theaters[$row['theater_id']]['name'] = $row['theater_name'];
    $theaters[$row['theater_id']]['location'] = $row['location'];
    $theaters[$row['theater_id']]['shows'][] = $row;
}

?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($movie['title']); ?></title>
    <link rel="stylesheet" href="home.css">
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

  .top-bar {
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

.date-strip .date.disabled {
background-color: #ddd !important;
color: #999 !important;
cursor: not-allowed !important;
pointer-events: none;
}
.showtime-section {
padding: 20px 30px;
}

  .cinema {
    background-color: #fff;
    padding: 16px;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
  }

  .cinema h3 {
    margin: 0 0 10px;
    font-size: 18px;
  }

  .cinema-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
  }

  .showtimes {
    display: flex;
    gap: 10px;
    margin-top: 10px;
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
    .top-bar {
      flex-direction: column;
      align-items: flex-start;
    }
    .filter-strip {
      width: 100%;
      justify-content: flex-start;
      flex-wrap: wrap;
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


<header><?php echo htmlspecialchars($movie['title']); ?></header>

<section class="top-bar">
    <div class="date-strip">
        <?php
        $availableDatesQuery = "SELECT DISTINCT show_date FROM shows WHERE movie_id = $movieId";
        $availableDatesResult = mysqli_query($conn, $availableDatesQuery);


    $availableDates = [];
    while ($row = mysqli_fetch_assoc($availableDatesResult)) {
        $availableDates[] = $row['show_date'];
    }

    for ($i = 0; $i < 6; $i++) {
        $day = strtoupper(date("D", strtotime("+$i days")));
        $date = date("d", strtotime("+$i days"));
        $month = strtoupper(date("M", strtotime("+$i days")));
        $fullDate = date("Y-m-d", strtotime("+$i days"));

        $isAvailable = in_array($fullDate, $availableDates);
        $isActive = ($fullDate === $selectedDate && $isAvailable) ? " active" : "";
        $disabledClass = $isAvailable ? "" : "disabled";

        echo "<button class='date$isActive $disabledClass' data-date='$fullDate'>
                <div class='day'>$day</div>
                <div class='date-number'>$date</div>
                <div class='month'>$month</div>
              </button>";
    }
    ?>
</div>

</section>

<section class="showtime-section">
    <?php if (!empty($theaters)): ?>
      <?php foreach ($theaters as $theater): ?>
          <div class="cinema">
              <h3><?= htmlspecialchars($theater['name']) ?>: <?= htmlspecialchars($theater['location']) ?></h3>
              <div class="cinema-info">
                  <div class="showtimes">
                      <?php foreach ($theater['shows'] as $show): ?>
                          <?php $formattedTime = date("h:i A", strtotime($show['show_time'])); ?>
                          <button class="showtime-btn" 
                                  data-show-id="<?= $show['show_id'] ?>" 
                                  data-show-date="<?= $show['show_date'] ?>" 
                                  data-show-time="<?= $formattedTime ?>" 
                                  onclick="goToSeatSelection(this)">
                              <?= htmlspecialchars($formattedTime) ?>
                          </button>
                      <?php endforeach; ?>
                  </div>
              </div>
          </div>
      <?php endforeach; ?>
  <?php else: ?>
      <p>No shows available for the selected date.</p>
  <?php endif; ?>
</section>

<script>
    let selectedDate = new Date().toISOString().split('T')[0]; // default to today

    function selectDate(date, button) {
        selectedDate = date;

        document.querySelectorAll('.date-strip .date').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
    }

    function goToSeatSelection(button) {
        const showId = button.getAttribute('data-show-id');
        const date = button.getAttribute('data-show-date');
        window.location.href = `seat-selection.php?show_id=${showId}&date=${date}`;
    }

    const dateButtons = document.querySelectorAll(".date-strip .date");
    const showtimeButtons = document.querySelectorAll(".showtime-btn");

    dateButtons.forEach((btn) => {
  btn.addEventListener("click", () => {
    const selectedDate = btn.getAttribute("data-date");
    window.location.href = `show-timings.php?id=<?php echo $movieId; ?>&date=${selectedDate}`;
  });
});



    function updateShowtimesVisibility(selectedDate) {
    const currentTime = new Date();

    document.querySelectorAll(".showtime-btn").forEach(button => {
      const showDate = button.getAttribute("data-show-date"); // e.g. 2025-05-09
      const showTime = button.getAttribute("data-show-time"); // e.g. 9:00 AM

      const [time, period] = showTime.split(" ");
      let [hour, minute] = time.split(":").map(Number);

      // Convert to 24-hour format
      if (period === "PM" && hour !== 12) hour += 12;
      if (period === "AM" && hour === 12) hour = 0;

      const showDateTime = new Date(`${showDate}T${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}:00`);

      // Show if future
      if (showDate === selectedDate && showDateTime > currentTime) {
        button.style.display = "inline-block";
      } else {
        button.style.display = "none";
      }
    });
  }

  function hideEmptyCinemas() {
  document.querySelectorAll('.cinema').forEach(cinema => {
    const showtimeButtons = cinema.querySelectorAll('.showtime-btn');
    let hasVisibleShow = false;

    showtimeButtons.forEach(btn => {
      if (btn.style.display !== 'none') {
        hasVisibleShow = true;
      }
    });

    cinema.style.display = hasVisibleShow ? 'block' : 'none';
  });
}

     // Default: use today
     window.addEventListener("DOMContentLoaded", () => {
  const selectedDate = "<?php echo $selectedDate; ?>";
  updateShowtimesVisibility(selectedDate);
  hideEmptyCinemas();
});


</script>

<script src="home.js"></script>

</body>
</html>      