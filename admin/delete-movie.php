<?php
include '../db.php'; // Include database connection

// Check if the 'id' parameter is present in the URL
if (isset($_GET['id'])) {
    $movieId = $_GET['id'];
    
    // SQL query to delete the movie permanently
    $sql = "DELETE FROM movies WHERE id = $movieId";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('✅ Movie deleted successfully.');
                window.location.href='admin-dashboard.php?section=theaters';
              </script>";
    } else {
        // Error during delete
        echo "<script>
                alert('❌ Failed to delete movie: " . mysqli_error($conn) . "');
                window.location.href='admin-dashboard.php?section=theaters';
              </script>";
    }
}
else {
    // If no 'id' parameter is provided, show an error
    echo "No movie ID specified!";
}

mysqli_close($conn); // Close database connection
?>
