<?php
// contact_submit.php

// Correct include path to your dbConnection.php
include(__DIR__ . "/../php/dbConnection.php"); // adjust according to your folder structure
session_start();

// Enable error reporting (for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if DB connection exists
if (!$conn) {
    die("Database connection failed.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data safely
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $rating = $_POST['rating'] ?? 0;

    // Prepare and execute insert query
    $stmt = $conn->prepare("INSERT INTO feedback (name, email, subject, message, rating) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssssi", $name, $email, $subject, $message, $rating);

    if ($stmt->execute()) {
        // Successfully saved, redirect to home
        header("Location: home.php?status=success");
        exit();
    } else {
        // Error saving
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // Invalid request method
    echo "Invalid request.";
}
?>
