<?php
include '../db.php'; // Include database connection

// Check if the 'id' parameter is present in the URL
if (isset($_GET['id'])) {
    $showId = $_GET['id'];

    // SQL query to delete the show permanently
    $sql = "DELETE FROM shows WHERE show_id = $showId";

    if (mysqli_query($conn, $sql)) {
        // Success: show alert and redirect
        echo "<script>
                alert('✅ Show deleted successfully.');
                window.location.href='admin-dashboard.php?section=shows';
              </script>";
    } else {
        // Error during delete
        echo "<script>
                alert('❌ Failed to delete show: " . mysqli_error($conn) . "');
                window.location.href='admin-dashboard.php?section=shows';
              </script>";
    }
} else {
    // If no 'id' parameter is provided, show an error
    echo "No Show ID specified!";
}

mysqli_close($conn); // Close database connection
?>
