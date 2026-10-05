<?php
include("dbConnection.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password

    if (!empty($email) && !empty($username) && !empty($password)) {
        $sql = "INSERT INTO portfolio_user (email, username, password) VALUES ('$email', '$username', '$password')";
        if (mysqli_query($conn, $sql)) {
            header("Location: login.php");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Please fill all fields.";
    }
} else {
    header("Location: userRegister.php");
    exit();
}
?>
