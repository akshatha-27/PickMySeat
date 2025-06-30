<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include '../db.php';

// User Signup
if (isset($_POST['signup'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $location = trim($_POST['location']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (
        empty($first_name) || empty($last_name) || empty($email) || empty($phone) ||
        empty($dob) || empty($gender) || empty($location) || empty($password) || empty($confirm_password)
    ) {
        echo "<script>alert('All fields are required!'); window.history.back();</script>";
        exit();
    }

    if (!preg_match("/^[A-Za-z ]{2,}$/", $first_name)) {
        echo "<script>alert('First name must contain only letters and be at least 2 characters.'); window.history.back();</script>";
        exit();
    }

    if (!preg_match("/^[A-Za-z ]{2,}$/", $last_name)) {
        echo "<script>alert('Last name must contain only letters and be at least 2 characters.'); window.history.back();</script>";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format!'); window.history.back();</script>";
        exit();
    }

    if (!preg_match('/^\d{10}$/', $phone)) {
        echo "<script>alert('Phone number must be 10 digits!'); window.history.back();</script>";
        exit();
    }

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }

    if (strlen($password) < 6) {
        echo "<script>alert('Password must be at least 6 characters!'); window.history.back();</script>";
        exit();
    }

    $birthDate = new DateTime($dob);
    $today = new DateTime();
    $age = $birthDate->diff($today)->y;

    if ($age < 13 || $age > 120) {
        echo "<script>alert('Invalid age. You must be atlest 13 years old.'); window.history.back();</script>";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $checkUser = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $checkUser->bind_param("s", $email);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already registered!'); window.history.back();</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, dob, gender, location, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $phone, $dob, $gender, $location, $hashed_password);

        if ($stmt->execute()) {
            $_SESSION['id'] = $conn->insert_id;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['email'] = $email;
            $_SESSION['phone'] = $phone;
            $_SESSION['dob'] = $dob;
            $_SESSION['gender'] = $gender;
            $_SESSION['location'] = $location;
            $_SESSION['user'] = $first_name;
            $_SESSION['role'] = "user";

            if (isset($_GET['redirect'])) {
                $redirect = urldecode($_GET['redirect']);
                header("Location: $redirect");
                exit();
            }

            header("Location: ../homepage/home.php");
            exit();
        } else {
            echo "<script>alert('Error: " . $conn->error . "'); window.history.back();</script>";
        }
    }
}


// User Login
if (isset($_POST['signin'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        echo "<script>alert('Email and password are required!'); window.history.back();</script>";
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['id'] = $row['id'];
            $_SESSION['user'] = $row['first_name'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['last_name'] = $row['last_name'];
            $_SESSION['phone'] = $row['phone'];
            $_SESSION['dob'] = $row['dob'];
            $_SESSION['gender'] = $row['gender'];
            $_SESSION['location'] = $row['location'];
            $_SESSION['role'] = $row['role'];

            $redirect = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : ($row['role'] === 'admin' ? "../admin/admin-dashboard.php" : "../homepage/home.php");
            echo "<script>window.location.href = '$redirect';</script>";
            exit();
        } else {
            echo "<script>alert('Invalid password'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('No user found!'); window.history.back();</script>";
    }
}

// Forgot Password
if (isset($_POST['reset_password'])) {
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        echo "<script>alert('All fields are required!'); window.history.back();</script>";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email!'); window.history.back();</script>";
        exit();
    }

    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }

    if (strlen($new_password) < 6) {
        echo "<script>alert('Password must be at least 6 characters!'); window.history.back();</script>";
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $updateStmt->bind_param("ss", $hashed_password, $email);

        if ($updateStmt->execute()) {
            echo "<script>alert('Password reset successfully!'); window.location.href = 'index.html';</script>";
            exit();
        } else {
            echo "<script>alert('Error updating password!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Email not found!'); window.history.back();</script>";
    }
}

$conn->close();
?>
