<?php
session_start();
include '../db.php';

if (!isset($_GET['show_id'])) {
    echo "Show ID missing!";
    exit;
}

$showId = $_GET['show_id'];
$selectedDate = $_GET['date'] ?? date("Y-m-d");

// Fetch show details
$sql = "SELECT s.show_time, m.title AS movie_title, m.release_date, t.name AS theater_name, t.location,
               s.vip_price, s.gold_price, s.silver_price
        FROM shows s
        JOIN movies m ON s.movie_id = m.id
        JOIN theaters t ON s.theater_id = t.theater_id
        WHERE s.show_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $showId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || mysqli_num_rows($result) === 0) {
    echo "Show not found.";
    exit;
}

$show = $result->fetch_assoc();

// Format date and time
$time = date("g:i A", strtotime($show['show_time']));
$date = date("d F Y", strtotime($selectedDate));
$dayOfWeek = date("l", strtotime($selectedDate));

// Prices from database
$vipPrice = $show['vip_price'];
$goldPrice = $show['gold_price'];
$silverPrice = $show['silver_price'];

// Fetch already booked seats for this show
$bookedSeats = [];
$seatQuery = $conn->prepare("SELECT seats FROM bookings WHERE show_id = ? AND (status IS NULL OR status != 'cancelled')");
$seatQuery->bind_param("i", $showId);
$seatQuery->execute();
$seatResult = $seatQuery->get_result();

while ($row = $seatResult->fetch_assoc()) {
    $seatsArray = explode(',', $row['seats']);
    $bookedSeats = array_merge($bookedSeats, array_map('trim', $seatsArray));
}

$bookedSeatsJson = json_encode($bookedSeats);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Seat Selection</title>
  <link rel="stylesheet" href="home.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f4f4f4;
    }
    .movie-details {
      background-color: #ffece4;
      width: 100%;
      margin: 0 auto;
      padding: 15px;
      text-align: center;
    }
    .movie-details h2 {
      margin: 0;
      font-size: 25px;
      color: #ff4500;
    }
    .movie-meta {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 20px;
      margin-top: 15px;
    }
    .movie-meta p {
      margin: 0;
      font-size: 15px;
      color: #333;
      font-weight: 600;
    }
    .divider {
      height: 20px;
      width: 1px;
      background-color: black;
    }
    .screen {
      margin: 50px auto 10px;
      width: 40%;
      height: 40px;
      background: linear-gradient(to bottom, #ffffff, #cccccc);
      border-radius: 10px 10px 0 0;
      box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.2);
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .screen h3 {
      color: #585858;
      margin: 0;
    }
    .section-title {
      text-align: center;
      margin-top: 30px;
      font-size: 15px;
      font-weight: bold;
      color: #333;
    }
    .seating-section {
      margin-bottom: 40px;
    }
    .seating-chart {
      display: grid;
      grid-template-columns: auto repeat(13, 40px);
      gap: 10px;
      justify-content: center;
      margin-top: 10px;
      text-align: center;
    }
    .row-label {
      font-weight: bold;
      text-align: center;
      line-height: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .seat {
  width: 40px;
  height: 40px;
  background: transparent;
  border: 1px solid black;
  text-align: center;
  line-height: 40px;
  border-radius: 5px;
  cursor: pointer;
  transition: 0.2s;
}

/* This ensures hover only applies to unselected seats */
.seat:hover {
  background-color: #1ea83c;
  color: white;
}

/* Booked seat styling */
.seat.booked {
  background: #aaa;
  cursor: not-allowed;
  color: #777;
}

/* Selected seat styling */
.seat.selected {
  background: #1ea83c;
  color: white;
}

/* :active state when clicking unselected seats */
.seat:active {
  background-color: rgb(37, 135, 52);
  color: white;
}

    .seat-status {
      display: flex;
      justify-content: center;
      gap: 30px;
      margin-bottom: 50px;
      margin-top: 25px;
      font-size: 16px;
      font-weight: 500;
      color: #333;
    }
    .seat-status div {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .seat-status .seat {
      width: 20px;
      height: 20px;
      border-radius: 3px;
      border: 1px solid black;
    }
    .seat-status .available {
      background-color: transparent;
    }
    .seat-status .select {
      background-color: #1ea83c;
    }
    .seat-status .booked {
      background-color: #aaa;
    }
    .buttons {
      text-align: center;
      margin: 20px;
    }
    .buttons button {
      background-color: #ff4500;
      color: #fff;
      border: none;
      padding: 12px 24px;
      border-radius: 10px;
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.3s ease;
      width: 50%;
      margin-top: 10px;
    }
    .buttons button:hover {
      background-color: #e63a00;
    }
    #seatSections {
  display: none;
}
  </style>
</head>

<body>
<nav class="navbar">
        <div class="logo">
          PickMySeat
        </div>
        </nav>
<script>
  const isLoggedIn = <?= (isset($_SESSION['id']) || isset($_SESSION['google_id'])) ? 'true' : 'false' ?>;
</script>

  <div class="movie-details">
    <h2><?= htmlspecialchars($show['movie_title']) ?></h2>
    <div class="movie-meta">
      <p class="theatre"><?= htmlspecialchars($show['theater_name']) ?> - <?= htmlspecialchars($show['location']) ?></p>
      <div class="divider"></div>
      <p class="datetime"><?= $dayOfWeek ?>, <?= $date ?>, <?= $time ?></p>
    </div>
  </div>

  <div style="text-align:center; margin-top: 20px;">
  <label for="numSeats" style="font-size: 16px; font-weight: bold; margin-right: 10px;">Select Number of Seats:</label>
  <select id="numSeats" style="padding: 10px 15px 10px 10px; font-size: 16px; border-radius: 8px; border: 1px solid #ccc;">
    <option value="">-- Select --</option>
    <?php for ($i = 1; $i <= 10; $i++): ?>
      <option value="<?= $i ?>"><?= $i ?></option>
    <?php endfor; ?>
  </select>
</div>

<div id="seatSections">
  <div class="seat-status">
    <div><span class="seat available"></span> Available</div>
    <div><span class="seat select"></span> Selected</div>
    <div><span class="seat booked"></span> Sold</div>
  </div>

  <!-- VIP -->
  <div class="seating-section">
    <div class="section-title">VIP - ₹<span id="vipPrice"><?= $vipPrice ?></span></div>
    <div class="seating-chart" id="vipSection" data-seat-price="<?= $vipPrice ?>"></div>
  </div>

  <!-- GOLD -->
  <div class="seating-section">
    <div class="section-title">GOLD - ₹<span id="goldPrice"><?= $goldPrice ?></span></div>
    <div class="seating-chart" id="goldSection" data-seat-price="<?= $goldPrice ?>"></div>
  </div>

  <!-- SILVER -->
  <div class="seating-section">
    <div class="section-title">SILVER - ₹<span id="silverPrice"><?= $silverPrice ?></span></div>
    <div class="seating-chart" id="silverSection" data-seat-price="<?= $silverPrice ?>"></div>
  </div>

  <div class="screen"><h3>SCREEN</h3></div>

  <div class="buttons">
  <form action="order-summary.php" method="POST">
    <input type="hidden" name="show_id" value="<?= $showId ?>">
    <input type="hidden" name="selected_date" value="<?= $selectedDate ?>">
    <input type="hidden" name="total_amount" id="totalAmountInput" value="">
    <input type="hidden" name="selected_seats" id="selectedSeatsInput" value="">
    <input type="hidden" name="seatTypes" id="seatTypesInput">
    <input type="hidden" name="seatPrices" id="seatPricesInput">
    <input type="hidden" name="seatDetails" id="seatDetailsInput">
    <button type="submit">Confirm Booking</button><br>
  </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const numSeatsDropdown = document.getElementById("numSeats");
  const seatSections = document.getElementById("seatSections");

  let allowedSeats = 0;

  const bookedSeats = <?= $bookedSeatsJson ?>;

  const seatPrices = {
    VIP: parseInt(document.getElementById("vipPrice").textContent.trim()),
    GOLD: parseInt(document.getElementById("goldPrice").textContent.trim()),
    SILVER: parseInt(document.getElementById("silverPrice").textContent.trim()),
  };

  const sections = {
    VIP: { rows: ["I"], containerId: "vipSection", price: seatPrices.VIP },
    GOLD: { rows: ["H", "G", "F", "E"], containerId: "goldSection", price: seatPrices.GOLD },
    SILVER: { rows: ["D", "C", "B", "A"], containerId: "silverSection", price: seatPrices.SILVER },
  };

  const seatsPerRow = 13;

  numSeatsDropdown.addEventListener("change", () => {
    const val = parseInt(numSeatsDropdown.value);
    const selected = document.querySelectorAll(".seat.selected");

    // Deselect all previously selected seats
    selected.forEach(seat => seat.classList.remove("selected"));

    if (!isNaN(val) && val > 0) {
      allowedSeats = val;
      seatSections.style.display = "block";
    } else {
      allowedSeats = 0;
      seatSections.style.display = "none";
    }
  });

  Object.entries(sections).forEach(([category, section]) => {
    const container = document.getElementById(section.containerId);
    section.rows.forEach((rowLabel) => {
      const rowHeader = document.createElement("div");
      rowHeader.classList.add("row-label");
      rowHeader.innerText = rowLabel;
      container.appendChild(rowHeader);

      for (let i = 1; i <= seatsPerRow; i++) {
        const seat = document.createElement("div");
        seat.classList.add("seat");
        seat.innerText = i;
        const seatId = `${rowLabel}${i}`;

        seat.setAttribute("data-row", rowLabel);
        seat.setAttribute("data-seat", i);
        seat.setAttribute("data-cost", section.price);
        seat.setAttribute("data-id", seatId);

        if (bookedSeats.includes(seatId)) {
          seat.classList.add("booked");
        } else {
          seat.addEventListener("click", () => {
            if (seat.classList.contains("selected")) {
              seat.classList.remove("selected");
              return;
            }

            const selectedSeats = document.querySelectorAll(".seat.selected");
            if (selectedSeats.length >= allowedSeats) {
              alert(`You can select up to ${allowedSeats} seat${allowedSeats > 1 ? 's' : ''}. To select more, increase the number of seats (maximum allowed is 10).`);
              return;
            }

            seat.classList.add("selected");
          });
        }
        container.appendChild(seat);
      }
    });
  });

  const form = document.querySelector("form");
  form.addEventListener("submit", function (event) {
    if (!isLoggedIn) {
      alert("You must be logged in to confirm your booking.");
      const currentUrl = window.location.href;
      window.location.href = "../login/index.html?redirect=" + encodeURIComponent(currentUrl);
      event.preventDefault();
      return;
    }

    const selectedSeats = document.querySelectorAll(".seat.selected");

    if (selectedSeats.length === 0) {
      alert("Please select at least one seat before confirming your booking.");
      event.preventDefault();
      return;
    }

    let seatList = [], totalAmount = 0, seatTypes = [], seatPricesList = [];

    selectedSeats.forEach((seat) => {
      const seatId = seat.getAttribute("data-id");
      const cost = parseInt(seat.getAttribute("data-cost"));
      const row = seat.getAttribute("data-row");
      let type = "";
      if (["I"].includes(row)) type = "VIP";
      else if (["H", "G", "F", "E"].includes(row)) type = "GOLD";
      else if (["D", "C", "B", "A"].includes(row)) type = "SILVER";

      seatList.push(seatId);
      seatTypes.push(type);
      seatPricesList.push(cost);
      if (!isNaN(cost)) totalAmount += cost;
    });

    document.getElementById("selectedSeatsInput").value = seatList.join(",");
    document.getElementById("totalAmountInput").value = totalAmount;
    document.getElementById("seatTypesInput").value = seatTypes.join(",");
    document.getElementById("seatPricesInput").value = seatPricesList.join(",");
  });
});
</script>
</body>
</html>
