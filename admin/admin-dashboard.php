<?php
include '../db.php';

// Fetch user count
$userResult = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$users = $userResult->fetch_assoc()['total_users'];

// Fetch movie count
$movieResult = $conn->query("SELECT COUNT(*) AS total_movies FROM movies");
$movies = $movieResult->fetch_assoc()['total_movies'];

// Fetch theater count
$theaterResult = $conn->query("SELECT COUNT(*) AS total_theaters FROM theaters");
$theaters = $theaterResult->fetch_assoc()['total_theaters'];

// Fetch total bookings
$bookingResult = $conn->query("SELECT COUNT(*) AS total_bookings FROM bookings");
$bookings = $bookingResult->fetch_assoc()['total_bookings'];

// Fetch total revenue
$revenueResult = $conn->query("SELECT SUM(total_price) AS total_revenue FROM bookings");
$revenue = $revenueResult->fetch_assoc()['total_revenue'] ?? 0;

// Fetch trending movie (movie with highest bookings)
$trendingResult = $conn->query("
    SELECT m.title,
    SUM(LENGTH(b.seats) - LENGTH(REPLACE(b.seats, ',', '')) + 1) AS total_tickets
    FROM bookings b
    JOIN shows s ON s.show_id = b.show_id
    JOIN movies m ON m.id = s.movie_id
    WHERE b.status = 'confirmed'
    GROUP BY s.movie_id
    ORDER BY total_tickets DESC
    LIMIT 1
");

$trending = $trendingResult->fetch_assoc();
$trending_movie = $trending ? $trending['title'] : "N/A";


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - PickMySeat</title>
    <link rel="stylesheet" href="admin.css" />
    <link rel="stylesheet" href="../fontawesome/css/all.min.css" />
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <h2>PickMySeat</h2>

            <div class="profile-container">
      <div class="profile-header">
        <img 
          src="../images/profile.jpg" 
          alt="User Image" 
          onerror="this.onerror=null; this.src='../images/default_avatar.png';"
          class="profile-img"
        >
        <h3>Admin</h3>
      </div>
    </div>
    <br>
            <nav>
                <a href="javascript:void(0);" onclick="loadSection('movies')"><i class="fas fa-film"></i> Movies</a>
                <a href="javascript:void(0);" onclick="loadSection('theaters')"><i class="fas fa-theater-masks"></i> Theaters</a>
                <a href="javascript:void(0);" onclick="loadSection('shows')"><i class="fas fa-clock"></i> Shows</a>
                <a href="javascript:void(0);" onclick="loadSection('offers')"><i class="fas fa-tags"></i> Offers</a>
                <a href="javascript:void(0);" onclick="loadSection('reports')"><i class="fas fa-chart-line"></i> Reports</a>
                <a href="../login/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <!-- <header>
                <h1>Admin Dashboard</h1>
            </header> -->
            <section class="overview"  id="overviewSection">
    <div class="card">👤 Users<br><strong><?= $users ?></strong></div>
    <div class="card">🎬 Movies<br><strong><?= $movies ?></strong></div>
    <div class="card">🏛️ Theaters<br><strong><?= $theaters ?></strong></div>
    <div class="card">🎫 Bookings<br><strong><?= number_format($bookings) ?></strong></div>
    <div class="card">💰 Revenue<br><strong>₹<?= number_format($revenue) ?></strong></div>
    <div class="card">🍿 Trending Movie<br><strong><?= $trending_movie ?></strong></div>
</section>


            <!-- Movie Management Section (Initially hidden) -->
            <div id="moviesSection" class="form-container hidden">
                <div id="movieHeaderActions">
                    <h2 style="text-align:center;font-size:30px;">Manage Movies</h2>
                    <a href="javascript:void(0);" class="add-movie" onclick="showAddMovieForm()">+ Add Movie</a>
                </div>


                <table id="moviesTable">
    <thead>
        <tr>
            <th>Sr no.</th>
            <th>Movie Title</th>
            <th>Release Date</th>
            <th>Duration</th>
            <th>Rating</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Fetch active movies from the database
        $sql = "SELECT * FROM movies";
        $result = mysqli_query($conn, $sql);
        $i = 1;
        while ($row = mysqli_fetch_assoc($result)) {
        ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['title']) ?> (<?= $row['languages'] ?>) (<?= $row['formats'] ?>)</td>
                <td><?= date("d M Y", strtotime($row['release_date'])) ?></td>
                <td><?= htmlspecialchars($row['duration']) ?></td>
                <td><?= htmlspecialchars($row['rating']) ?></td>
                <td class="action-btns">
                    <a href="view-movie.php?id=<?= $row['id'] ?>" class="btn-view" title="View"><i class="fas fa-eye"></i></a>
                    <a href="edit-movie.php?id=<?= $row['id'] ?>" class="btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="delete-movie.php?id=<?= $row['id'] ?>" class="btn-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this movie?')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

                <!-- Add Movie Form -->
            <div id="addMovieForm" class="add-movie-form hidden">
    <?php include 'add-movie.php'; ?>
</div>

</div>

            <!-- Theaters Section -->
<div id="theatersSection" class="form-container hidden">
    <div id="theaterHeaderActions">
        <h2 style="text-align:center;font-size:30px;">Manage Theaters</h2>
        <a href="javascript:void(0);" class="add-movie" onclick="showAddTheaterForm()">+ Add Theater</a>
    </div>

    <!-- Theater List Table -->
    <table id="theatersTable" class="theaters-table">
        <thead>
            <tr>
                <th>Sr no.</th>
                <th>Theater Name</th>
                <th>Location</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch theaters from the database
            $sql = "SELECT * FROM theaters";
            $result = mysqli_query($conn, $sql);
            $i = 1;
            while ($row = mysqli_fetch_assoc($result)) {
            ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td class="action-btns">
                        <a href="edit-theater.php?id=<?= $row['theater_id'] ?>" class="btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                        <a href="delete-theater.php?id=<?= $row['theater_id'] ?>" class="btn-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this theater?')">
    <i class="fas fa-trash"></i>
</a>

                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Add Theater Form -->
    <div id="addTheaterForm" class="add-movie-form hidden">
        <?php include 'add-theater.php'; ?>
    </div>
</div>
            <!-- Shows Section -->
<div id="showsSection" class="form-container hidden">
    <div id="showHeaderActions">
        <h2 style="text-align:center;font-size:30px;">Manage Shows</h2>
        <a href="javascript:void(0);" class="add-movie" onclick="showAddShowForm()">+ Add Show</a>
    </div>

    <!-- Shows List Table -->
    <table id="showsTable" class="shows-table">
    <thead>
        <tr>
            <th>Sr No.</th>
            <th>Show Date</th>
            <th>Show Time</th>
            <th>Movie</th>
            <th>Theater</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Fetch shows from the database
        $sql = "SELECT s.show_id, s.show_date, s.show_time,
               m.title AS movie_title, t.name AS theater_name
        FROM shows s
        JOIN movies m ON s.movie_id = m.id
        JOIN theaters t ON s.theater_id = t.theater_id
        ORDER BY s.show_date DESC, s.show_time DESC";

        $result = mysqli_query($conn, $sql);
        $i = 1;
        while ($row = mysqli_fetch_assoc($result)) {
        ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= date("d M Y", strtotime($row['show_date'])) ?></td>
                <td><?= date("g:i A", strtotime($row['show_time'])) ?></td>
                <td><?= htmlspecialchars($row['movie_title']) ?></td>
                <td><?= htmlspecialchars($row['theater_name']) ?></td>
                <td class="action-btns">
                    <a href="view-show.php?id=<?= $row['show_id'] ?>" class="btn-view" title="View"><i class="fas fa-eye"></i></a>
                    <a href="edit-show.php?id=<?= $row['show_id'] ?>" class="btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="delete-show.php?id=<?= $row['show_id'] ?>" class="btn-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this show?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>



    <!-- Add Show Form -->
    <div id="addShowForm" class="add-movie-form hidden">
        <?php include 'add-show.php'; ?>
    </div>
</div>

<div id="offersSection" class="form-container hidden">
    <div id="offerHeaderActions">
        <h2 style="text-align:center;font-size:30px;">Manage Offers</h2>
        <a href="javascript:void(0);" class="add-movie" onclick="showAddOfferForm()">+ Add Offer</a>
    </div>

    <!-- Offers List Table -->
   <table id="offersTable" class="offers-table">
    <thead>
        <tr>
            <th>Sr no.</th>
            <th>Description</th>
            <th>Valid Until</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Fetch offers from the database
        $sql = "SELECT * FROM offers ORDER BY valid_until DESC";
        $result = mysqli_query($conn, $sql);
        $i = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            $valid_until = !empty($row['valid_until']) ? date('d-M-Y', strtotime($row['valid_until'])) : '—';
            $status = ucfirst($row['status']);
        ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= $valid_until ?></td>

                <td class="action-btns">
                    <a href="edit-offer.php?id=<?= $row['id'] ?>" class="btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="delete-offer.php?id=<?= $row['id'] ?>" class="btn-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this offer?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

    <!-- Add Offer Form -->
    <div id="addOfferForm" class="add-movie-form hidden">
        <?php include 'add-offer.php'; ?>
    </div>
</div> 

<div id="reportsSection" class="form-container hidden">
<h2 style="text-align:center;font-size:30px;margin-bottom:50px;">Reports and Analytics</h2>

                <!-- Reports Table -->
    <table id="reportsTable" class="reports-table">
        <thead>
            <tr>
                <th>Sr no.</th>
                <th>Movie</th>
                <th>Theater</th>
                <th>Tickets Booked</th>
                <th>Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "
                SELECT 
    m.title AS movie_title,
    t.name AS theater_name,
    SUM(LENGTH(b.seats) - LENGTH(REPLACE(b.seats, ',', '')) + 1) AS tickets_booked,
    SUM(b.total_price) AS total_revenue
FROM bookings b
JOIN shows s ON b.show_id = s.show_id
JOIN movies m ON s.movie_id = m.id
JOIN theaters t ON s.theater_id = t.theater_id
WHERE b.status = 'confirmed'
GROUP BY s.movie_id, s.theater_id
ORDER BY total_revenue DESC
            ";
            $result = mysqli_query($conn, $sql);
            $i = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                $movie = htmlspecialchars($row['movie_title']);
                $theater = htmlspecialchars($row['theater_name']);
                $tickets = $row['tickets_booked'];
                $revenue = number_format($row['total_revenue']);
            ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= $movie ?></td>
                    <td><?= $theater ?></td>
                    <td><?= $tickets ?></td>
                    <td>₹<?= $revenue ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    </div>

</main>
    </div>

    <script>
function loadSection(section) {
    // Hide the overview section
    document.querySelector('.overview').classList.add('hidden');

    // Hide all sections initially
    var sections = document.querySelectorAll('.form-container');
    sections.forEach(function(sec) {
        sec.classList.add('hidden');
    });

    // Show the selected section
    if (section === 'movies') {
        document.getElementById('moviesSection').classList.remove('hidden');
    } else if (section === 'theaters') {
        document.getElementById('theatersSection').classList.remove('hidden');
    } else if (section === 'shows') {
        document.getElementById('showsSection').classList.remove('hidden');
    } else if (section === 'offers') {
    document.getElementById('offersSection').classList.remove('hidden');
    }else if (section === 'reports') {
        document.getElementById('reportsSection').classList.remove('hidden');
    }
}

// Check URL to determine which section to load initially
window.onload = function() {
    // Initially, don't load any section (this prevents the 'movies' section from loading initially)
};


function showAddMovieForm() {
    document.getElementById("moviesTable").classList.add('hidden'); // Hide the movie table
    document.getElementById("addMovieForm").classList.remove('hidden'); // Show the add form
    document.getElementById("movieHeaderActions").classList.add('hidden'); 
}

function showAddTheaterForm() {
    document.getElementById("theatersTable").classList.add('hidden'); // Hide the theater table
    document.getElementById("addTheaterForm").classList.remove('hidden'); // Show the add form
    document.getElementById("theaterHeaderActions").classList.add('hidden'); 
}

function showTheaterList() {
    document.getElementById("addTheaterForm").classList.add('hidden'); // Hide the add form
    document.getElementById("theatersTable").classList.remove('hidden'); // Show the theater list
}

function showAddShowForm() {
    document.getElementById("showsTable").classList.add('hidden'); // Hide the shows table
    document.getElementById("addShowForm").classList.remove('hidden'); // Show the add form
    document.getElementById("showHeaderActions").classList.add('hidden'); // Hide the header actions
}

function showAddOfferForm() {
    document.getElementById("offersTable").classList.add('hidden'); // Hide the offers table
    document.getElementById("addOfferForm").classList.remove('hidden'); // Show the add form
    document.getElementById("offerHeaderActions").classList.add('hidden'); // Hide the header actions
}
    </script>
</body>
</html>
