<?php
include '../db.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_show'])) {
    $theater_id = $_POST['theater_id'];
    $movie_id = $_POST['movie_id'];
    $show_date = $_POST['show_date'];
    $show_time = $_POST['show_time'];

    $vip_price = $_POST['vip_price'];
    $gold_price = $_POST['gold_price'];
    $silver_price = $_POST['silver_price'];

    // Insert into shows table with prices
    $sql = "INSERT INTO shows (theater_id, movie_id, show_date, show_time, vip_price, gold_price, silver_price)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissddd", $theater_id, $movie_id, $show_date, $show_time, $vip_price, $gold_price, $silver_price);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Show added successfully.'); window.location.href='admin-dashboard.php?section=shows';</script>";
        exit;
    } else {
        echo "<script>alert('❌ Error adding show: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}
?>


<h2 style="text-align:center;font-size:30px;">Add Show</h2>
        <form method="POST">
            <label>Select Theater:</label>
            <select name="theater_id" required>
                <option value="">-- Select --</option>
                <?php
                $theaters = mysqli_query($conn, "SELECT * FROM theaters");
                while ($theater = mysqli_fetch_assoc($theaters)) {
                    echo "<option value='{$theater['theater_id']}'>{$theater['name']} ({$theater['location']})</option>";
                }
                ?>
            </select>

            <label>Select Movie:</label>
            <select name="movie_id" required>
                <option value="">-- Select --</option>
                <?php
                $movies = mysqli_query($conn, "SELECT * FROM movies");
                while ($movie = mysqli_fetch_assoc($movies)) {
                    echo "<option value='{$movie['id']}'>{$movie['title']}</option>";
                }
                ?>
            </select>

            <label>Show Date:</label>
            <input type="date" name="show_date" required>

            <label>Show Time:</label>
            <input type="time" name="show_time" required>

            <label>VIP Price:</label>
            <input type="number" name="vip_price" min="0" step="1" required>

            <label>Gold Price:</label>
            <input type="number" name="gold_price" min="0" step="1" required>

            <label>Silver Price:</label>
            <input type="number" name="silver_price" min="0" step="1" required>

            <button type="submit" name="add_show">Add Show</button>
        </form>
    