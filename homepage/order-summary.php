<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $showId = $_POST['show_id'] ?? 0;
    $seats = explode(',', $_POST['selected_seats'] ?? '');
    $seatTypes = explode(',', $_POST['seatTypes'] ?? '');
    $seatPrices = explode(',', $_POST['seatPrices'] ?? '');

    if ($showId <= 0 || empty($seats)) {
        echo "Invalid request.";
        exit;
    }

    // Fetch movie/show/theater details for display (NOT inserting yet)
    $query = "SELECT 
                m.title AS movie_title,
                m.poster_path,
                s.show_date,
                s.show_time,
                t.name AS theater,
                t.location AS theater_location
              FROM shows s
              JOIN movies m ON s.movie_id = m.id
              JOIN theaters t ON s.theater_id = t.theater_id
              WHERE s.show_id = $showId";

    $result = mysqli_query($conn, $query);
    $booking = mysqli_fetch_assoc($result);
} else {
    echo "Invalid request.";
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Booking Summary</title>
    <link rel="stylesheet" href="../fontawesome/css/all.min.css" />
    <link rel="stylesheet" href="home.css">
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
      }

      body {
        background-color: #ffffff;
        color: #333;
        line-height: 1.6;
      }

      .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        animation: fadeIn 0.6s ease-out;
      }

      .payment-container {
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-between;
        gap: 20px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 40px;
        max-width: 1200px;
        margin: auto;
        align-items: flex-start;
        width: 100;
        
      }

     .payment-form {
  flex: 0 0 50%; 
  background: #fafafa;
  padding: 20px;
  border-radius: 10px;
  border: 1px solid #ccc;
}

.order-summary {
  flex: 0 0 50%;
  background: #fafafa;
  padding: 20px;
  border-radius: 10px;
  border: 1px solid #ccc;
}
      h3 {
        color: #ff4500; /* Your color theme */
        margin-bottom: 25px;
        font-size: 1.6rem;
        font-weight: 600;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
        position: relative;
        text-align: center;
      }

      /* Movie Info */
      .movie-info {
        display: flex;
        gap: 25px;
        margin-bottom: 30px;
        align-items: center;
      }

      .movie-info img {
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        width: 120px;
        height: 180px;
        object-fit: cover;
        transition: transform 0.3s;
      }

      .movie-info h4 {
        font-size: 1.2rem;
        margin-bottom: 12px;
        color: #444;
        font-weight: 600;
      }

      .movie-info p {
        color: #666;
        margin-bottom: 10px;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
      }

      .movie-info i {
        color: #ff4500; /* Your color theme */
        width: 20px;
        text-align: center;
        margin-right: 8px;
        font-size: 1.1rem;
      }

      /* Ticket Details */
      .ticket-details {
        margin-top: 25px;
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #ddd;
      }

      .ticket-type,
      .total {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #ddd;
        font-size: 0.95rem;
      }

      .ticket-type:last-child {
        border-bottom: none;
      }

      /* .promo-code label {
        font-size: 0.95rem;
        font-weight: 500;
        display: block;
        margin-bottom: 8px;
        color: #444;
      } */

      .promo-input {
        display: flex;
        gap: 10px;
        margin-top: 5px;
      }

      .promo-input input {
        flex: 1;
        padding: 12px 15px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
        margin-bottom: 16px;
        background-color: #fff;
        /* box-sizing: border-box; */
      }

      .promo-input input:focus {
        border-color: #ff4500;
        outline: none;
        box-shadow: 0 0 0 2px rgba(255, 69, 0, 0.2);
      }

      .promo-input button {
        background-color: #ff4500; /* Your color theme */
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 0.95rem;
        cursor: pointer;
        transition: background 0.3s;
        height: 100%;
      }

      .promo-input button:hover {
        background-color: #e63a00; /* Your color theme */
      }

      .total {
        font-weight: bold;
        font-size: 1.1rem;
        border-bottom: none;
        margin-top: 15px;
        color: #ff4500; /* Your color theme */
        padding-top: 15px;
        border-top: 1px solid #ddd;
      }

      .payment-form .form-group input[type="text"],
      .payment-form .form-group input[type="email"],
      .payment-form .form-group input[type="tel"],
      .payment-form .form-group input[type="password"] {
        width: 100%;
        padding: 12px 15px;
        margin-top: 6px;
        margin-bottom: 16px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
        background-color: #fff;
        box-sizing: border-box;
      }

      .payment-form .form-group input:focus {
        border-color: #ff4500;
        outline: none;
        box-shadow: 0 0 0 2px rgba(255, 69, 0, 0.2);
      }

      .payment-form button.pay-now-btn {
        background-color: #ff4500;
        color: #fff;
        border: none;
        padding: 12px 20px;
        width: 100%;
        border-radius: 6px;
        font-size: 18px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin: 15px 0 15px 0;
      }

      .pay-now-btn {
        background-color: #ff4500;
        color: #fff;
        border: none;
        padding: 12px 20px;
        width: 100%;
        border-radius: 6px;
        font-size: 18px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin: 15px 0 15px 0;
      }

      .pay-now-btn:hover {
        background-color: #e03e00;
      }
      .pay-now-btn:active {
        background-color:rgb(149, 48, 8);
      }

      /* Mobile devices: max-width 600px */
@media (max-width: 600px) {
  .payment-container {
    flex-direction: column;
    padding: 20px;
  }

  .payment-form,
  .order-summary {
    flex: 1 1 100%;
    width: 100%;
    margin-bottom: 20px;
  }

  .movie-info {
    flex-direction: column;
    align-items: flex-start;
  }

  .movie-info img {
    width: 100%;
    max-width: 180px;
    height: auto;
  }

  .movie-info h4 {
    font-size: 1.1rem;
  }

  .movie-info p {
    font-size: 0.9rem;
  }

  h3 {
    font-size: 1.3rem;
    padding-bottom: 10px;
    margin-bottom: 20px;
  }

  .promo-input input,
  .promo-input button {
    font-size: 14px;
    padding: 10px;
  }

  .payment-form .form-group input[type="text"],
  .payment-form .form-group input[type="email"],
  .payment-form .form-group input[type="tel"],
  .payment-form .form-group input[type="password"] {
    font-size: 14px;
    padding: 10px;
  }

  .pay-now-btn {
    font-size: 16px;
    padding: 12px;
  }
}

/* Tablets: 601px - 900px */
@media (min-width: 601px) and (max-width: 900px) {
  .payment-container {
    flex-wrap: wrap;
    padding: 30px;
  }

  .payment-form,
  .order-summary {
    flex: 1 1 48%;
    width: 48%;
  }

  .movie-info img {
    width: 140px;
    height: 210px;
  }

  h3 {
    font-size: 1.5rem;
  }
}

/* Small desktops and up: 901px - 1200px */
@media (min-width: 901px) and (max-width: 1200px) {
  .payment-container {
    padding: 35px;
  }

  .payment-form,
  .order-summary {
    flex: 0 0 48%;
    width: 48%;
  }

  .movie-info img {
    width: 120px;
    height: 180px;
  }
}

    </style>
  </head>
  <body>
  <nav class="navbar">
        <div class="logo">
          PickMySeat
        </div>
        </nav>
   <div class="container">
  <div class="payment-container">
    <div class="payment-form">
      <h3>Contact Details</h3>
      <form id="paymentForm">
        <div class="form-group">
          <label for="name">Full Name</label>
          <input
  type="text"
  id="name"
  name="name"
  value="<?= isset($_SESSION['first_name'], $_SESSION['last_name']) ? htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) : '' ?>"
  placeholder="Enter your full name"
  required
/>

          <span class="error-message" id="name-error"></span>
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input
  type="email"
  id="email"
  name="email"
  value="<?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '' ?>"
  placeholder="Enter your email address"
  required
/>
          <span class="error-message" id="email-error"></span>
        </div>
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input
            type="tel"
            id="phone"
            name="phone"
            value="<?= isset($_SESSION['phone']) ? htmlspecialchars($_SESSION['phone']) : '' ?>"
            placeholder="Enter your phone number"
            required
            maxlength="10"
          />
          <span class="error-message" id="phone-error"></span>
        </div>
        
      </form>
    </div>

    <div class="order-summary">
    <h3>Order Summary</h3>
    <div class="movie-info">
        <img src="<?= htmlspecialchars($booking['poster_path']) ?>" alt="Movie Poster" />
        <div>
            <h4><?= htmlspecialchars($booking['movie_title']) ?></h4>
            <p><i class="far fa-calendar-alt"></i> <?= date("d M Y", strtotime($booking['show_date'])) ?></p>
            <p><i class="far fa-clock"></i> <?= date("h:i A", strtotime($booking['show_time'])) ?></p>
            <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($booking['theater']) ?> - <?= htmlspecialchars($booking['theater_location']) ?></p>

        </div>
    </div>

    <div class="ticket-details">
    <h4>Tickets</h4>

    <?php
    $seats = isset($_POST['selected_seats']) ? explode(',', $_POST['selected_seats']) : [];
    $seatTypes = isset($_POST['seatTypes']) ? explode(',', $_POST['seatTypes']) : [];
    $seatPrices = isset($_POST['seatPrices']) ? explode(',', $_POST['seatPrices']) : [];

    $totalAmount = 0;
    $seatDisplayList = [];

    $seatCount = count($seats);

    for ($i = 0; $i < $seatCount; $i++) {
        $seat = htmlspecialchars($seats[$i] ?? '');
        $type = htmlspecialchars($seatTypes[$i] ?? 'Unknown');
        $price = isset($seatPrices[$i]) ? floatval($seatPrices[$i]) : 0;

        $seatDisplayList[] = "{$seat} ({$type})";
        $totalAmount += $price;

        echo "<div class='ticket-type'>
                <span>{$seat} ({$type})</span>
                <span>₹" . number_format($price, 2) . "</span>
              </div>";
    }
    ?>

    <div class="total">
        <span>Total Amount</span>
        <span>₹<?= number_format($totalAmount, 2) ?></span>
    </div>
    </div>
    <div class="total" id="discount-box" style="display:none;">


<!-- Add a hidden field to store validated amount -->
<input type="hidden" id="final-amount" name="final_amount" value="<?= $totalAmount ?>">
</div>

<button type="button" class="pay-now-btn" id="pay-button">Pay Now</button>
<script src="home.js"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('pay-button').onclick = function (e) {
  e.preventDefault();

  const name = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const phone = document.getElementById('phone').value.trim();

  let errorMessages = [];

  // Name validation
  if (name === '') {
    errorMessages.push('Please enter your full name.');
  }

  // Email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (email === '') {
    errorMessages.push('Please enter your email address.');
  } else if (!emailRegex.test(email)) {
    errorMessages.push('Please enter a valid email address.');
  }

  // Phone validation
  if (phone === '') {
    errorMessages.push('Please enter your phone number.');
  } else if (!/^\d{10}$/.test(phone)) {
    errorMessages.push('Phone number must be exactly 10 digits.');
  }

  // Show alert if any error
  if (errorMessages.length > 0) {
    alert(errorMessages.join('\n'));
    return;
  }

  // Get the final amount from the hidden input
  const finalAmount = parseFloat(document.getElementById('final-amount').value);

  // Create the order on the server
  fetch('create_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `amount=${finalAmount}&offer_id=offer_QY0HXbJkMwXVBc`
  })
  .then(res => res.json())
  .then(data => {
    var options = {
      "key": "rzp_test_YXSmAaz8w7PTl9", // Your Razorpay key
      "amount": finalAmount * 100, // Amount in paise (final amount * 100)
      "currency": "INR",
      "name": "PickMySeat",
      "description": "Movie Ticket Booking",
      "image": "../images/logo.png",
      "order_id": data.order_id, // Order ID returned from the backend

      "handler": function (response) {
        // Send the payment details to the backend for verification
        const paymentData = {
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_order_id: response.razorpay_order_id,
          razorpay_signature: response.razorpay_signature,
          amount: finalAmount,
          offer_id: "offer_QY0HXbJkMwXVBc",
          show_id: <?= json_encode($showId) ?>,  
          selected_seats: <?= json_encode($seats) ?>, 
          seat_types: <?= json_encode($seatTypes) ?>,  
          total_price: <?= json_encode($totalAmount) ?>,  
          user_id: <?= json_encode($_SESSION['id'] ?? null) ?>  
        };

        // Send the payment data to 'payment-success.php'
        fetch('payment-success.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(paymentData)
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === "success") {
            alert("Payment successful.");
            // Redirect to the ticket page with the booking ID from the backend response
            window.location.href = `ticket.php?booking_id=${data.booking_id}`;
          } else {
            alert("Payment failed or something went wrong.");
          }
        })
        .catch(err => {
          console.error("Fetch error:", err);
          alert("Something went wrong.");
        });
      },

      "theme": {
        "color": "#ff4500" // Your theme color
      },

      "prefill": {
        "name": "<?= htmlspecialchars($_SESSION['first_name'] ?? '') . ' ' . htmlspecialchars($_SESSION['last_name'] ?? 'Test User') ?>",
        "email": "<?= htmlspecialchars($_SESSION['email'] ?? 'testuser@pickmyseat.com') ?>",
        "contact": "<?= htmlspecialchars($_SESSION['phone'] ?? '9876543210') ?>"
      },

      "method": {
        "card": true,
        "upi": true,
        "netbanking": true,
        "wallet": true,
        "paylater": true
      }
    };

    // Open the Razorpay payment gateway
    var rzp1 = new Razorpay(options);
    rzp1.open();
  });
};
</script>

  </body>
</html>

