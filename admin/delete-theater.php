<?php
include '../db.php'; // Include database connection

// Check if the 'id' parameter is present in the URL
if (isset($_GET['id'])) {
    $theaterId = $_GET['id'];

    // SQL query to delete the theater permanently
    $sql = "DELETE FROM theaters WHERE theater_id = $theaterId";

    if (mysqli_query($conn, $sql)) {
        // Success: show alert and redirect
        echo "<script>
                alert('✅ Theater deleted successfully.');
                window.location.href='admin-dashboard.php?section=theaters';
              </script>";
    } else {
        // Error during delete
        echo "<script>
                alert('❌ Failed to delete theater: " . mysqli_error($conn) . "');
                window.location.href='admin-dashboard.php?section=theaters';
              </script>";
    }
}else {
    // If no 'id' parameter is provided, show an error
    echo "No Theater ID specified!";
}

mysqli_close($conn); // Close database connection
?>
