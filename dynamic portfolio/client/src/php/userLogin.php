<?php
include("dbConnection.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $sql = "SELECT * FROM portfolio_user WHERE email='$email'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);

            if (password_verify($password, $row['password'])) {
                // Save session data
                $_SESSION['id'] = $row['id'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['username'] = $row['username'];

                // Redirect to portfolioform.html after login
                header("Location: profile.php");
                exit();

            } else {
                echo "Incorrect password.";
            }
        } else {
            echo "No user found with this email.";
        }
    } else {
        echo "Please fill all fields.";
    }
} else {
    header("Location: login.php");
    exit();
}
?>
