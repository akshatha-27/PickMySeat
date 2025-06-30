<?php
session_start();
include '../db.php';

require '../vendor/autoload.php'; // Required for dompdf
use Dompdf\Dompdf;

$bookingId = $_GET['booking_id'] ?? null;
$download = $_GET['download'] ?? false; // Check if it's download mode

if (!$bookingId) {
    echo "Booking ID not provided.";
    exit;
}

$query = "
    SELECT 
        b.booking_id, b.seats, b.seat_types, b.total_price, b.booking_time,
        m.title AS movie_name, m.languages, m.formats, m.poster_path,
        s.show_time, s.show_date,
        t.name AS theater_name, t.location
    FROM bookings b
    JOIN shows s ON b.show_id = s.show_id
    JOIN movies m ON s.movie_id = m.id
    JOIN theaters t ON s.theater_id = t.theater_id
    WHERE b.booking_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Ticket not found.";
    exit;
}

$row = $result->fetch_assoc();

// Handle base64 image conversion for poster
$relativePosterPath = $row['poster_path']; // e.g. "../images/posters/1746354535_movie.jpg"

// Remove any leading ../ or ./ for safety (optional)
$relativePosterPath = preg_replace('#^(\.\./)+#', '', $relativePosterPath); 

$baseDir = realpath(__DIR__); // Path of current PHP file folder: C:\wamp64\www\PickMySeat

$posterFullPath = realpath(__DIR__ . '/../' . $relativePosterPath);


if ($posterFullPath && file_exists($posterFullPath)) {
    $imageInfo = getimagesize($posterFullPath);
    $imageData = base64_encode(file_get_contents($posterFullPath));
    $posterBase64 = 'data:' . $imageInfo['mime'] . ';base64,' . $imageData;
} else {
    echo "File does NOT exist or path is incorrect.\n";
}



// Buffer HTML content
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Ticket</title>
    <link rel="stylesheet" href="../fontawesome/css/all.min.css" />
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/fonts/DejaVuSans.ttf') format('truetype');
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }
        .ticket-container {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            max-width: 400px;
            margin: auto;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
        }
        .ticket-details {
            border-top: 2px dashed #bbb;
            padding-top: 5px;
            text-align: center;
        }
        .ticket-details h3 {
            margin: 5px 0;
            font-size: 17px;
        }
        .go-back {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #ff4500;
            font-weight: bold;
            font-size: 14px;
            border: 1px solid #ff4500;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s ease;
            margin-left:10%;
        }
        .go-back:hover {
            background-color: #ff4500;
            color: white;
        }
    </style>
</head>
<body>
<?php if (!$download): ?>
<a class="go-back" href="home.php"><i class="fa fa-arrow-left"></i> Back to Home</a>
<?php endif; ?>

<div class="ticket-container">
<table style="width: 100%; margin-bottom: 20px;">
    <tr>
        <td style="width: 110px;">
            <img src="<?= $posterBase64 ?>" alt="Movie Poster" style="width: 100px; height: 140px; border-radius: 8px; object-fit: cover;" />
        </td>
        <td style="padding-left: 15px; ">
            <h3 style="margin: 0;"><?= htmlspecialchars($row['movie_name']) ?></h3>
            <p style="margin: 5px 0;"><?= htmlspecialchars($row['languages']) ?>, <?= htmlspecialchars($row['formats']) ?></p>
            <p style="margin: 5px 0;"><?= date('D, d M', strtotime($row['show_date'])) ?> | <?= date('h:i A', strtotime($row['show_time'])) ?></p>
            <p style="margin: 5px 0;"><?= htmlspecialchars($row['theater_name']) ?>, <?= htmlspecialchars($row['location']) ?></p>
        </td>
    </tr>
</table>


    <div class="ticket-details">
        <?php
        $seatCount = count(explode(",", $row['seats']));
        echo "<p style='font-size:17px'>{$seatCount} Ticket(s)</p>";
        ?>

        <h3>SCREEN 1</h3>
        <?php
        $seatTypes = array_reverse(explode(",", $row['seat_types']));
        $seats = array_reverse(explode(",", $row['seats']));
        for ($i = 0; $i < count($seats); $i++) {
            echo "<p style='font-size:15px'>" . htmlspecialchars($seatTypes[$i]) . " - " . htmlspecialchars($seats[$i]) . "</p>";
        }
        ?>

        <p style='font-size:15px'><strong>BOOKING ID: <?= strtoupper(htmlspecialchars($row['booking_id'])) ?></strong></p>
    </div>

    <div style="background-color: #eee; padding: 10px; margin-top: 15px;">
        <p style="font-size: 12px; color: gray; text-align: center; margin: 0;">
            You can cancel your tickets up to 1 hour before the show starts.
        </p>
    </div>

    <table style="width: 100%; margin-top: 20px; font-weight: bold; background: #f0f0f0; padding: 10px;">
        <tr>
            <td>Total Amount:</td>
            <td style="text-align: right;">₹<?= number_format($row['total_price'], 2) ?></td>
        </tr>
    </table>
</div>
</body>
</html>

<?php
$html = ob_get_clean();

if ($download) {
    // Make sure there's no unexpected output
    ob_clean(); // Clear previous output buffers
    $dompdf = new Dompdf([
        'defaultFont' => 'DejaVu Sans',
    ]);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("ticket_{$bookingId}.pdf", ["Attachment" => true]);
    exit;
} else {
    echo $html;
}

?>
