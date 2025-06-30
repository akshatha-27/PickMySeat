<?php
include '../db.php'; // Include database connection

// Check if the 'id' parameter is present in the URL
if (isset($_GET['id'])) {
    $offerId = $_GET['id'];

    // SQL query to delete the offer permanently
    $sql = "DELETE FROM offers WHERE id = $offerId";

    if (mysqli_query($conn, $sql)) {
        // Success: show alert and redirect
        echo "<script>
                alert('✅ Offer deleted successfully.');
                window.location.href='admin-dashboard.php?section=offers';
              </script>";
    } else {
        // Error during delete
        echo "<script>
                alert('❌ Failed to delete offer: " . mysqli_error($conn) . "');
                window.location.href='admin-dashboard.php?section=offers';
              </script>";
    }
} else {
    // If no 'id' parameter is provided, show an error
    echo "No Offer ID specified!";
}

mysqli_close($conn); // Close database connection
?>
