<?php
include("dbConnection.php");
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['id'];

// Fetch existing profile
$sql = "SELECT full_name, occupation, bio, avatar FROM profiles WHERE user_id=?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
} else {
    die("Profile not found!");
}

$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'];
    $occupation = $_POST['occupation'];
    $bio = $_POST['bio'];

    // Handle avatar upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = 'uploads/avatar_' . $userId . '.' . $fileExtension;
        move_uploaded_file($fileTmpPath, $newFileName);
        $avatarPath = $newFileName;
    } else {
        $avatarPath = $profile['avatar']; // keep old avatar if no new file
    }

    // Update profile
    $updateSql = "UPDATE profiles SET full_name=?, occupation=?, bio=?, avatar=? WHERE user_id=?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssssi", $fullName, $occupation, $bio, $avatarPath, $userId);
    $updateStmt->execute();
    $updateStmt->close();

    header("Location: showcaseProfile.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Roboto', sans-serif;
    background: linear-gradient(135deg, #89f7fe, #66a6ff);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}
.edit-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    padding: 40px 30px;
    width: 400px;
    transition: transform 0.3s, box-shadow 0.3s;
}
.edit-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 30px 80px rgba(0,0,0,0.3);
}
.edit-card h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}
.edit-card img {
    display: block;
    margin: 0 auto 20px;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid #fff;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}
.edit-card input[type="text"],
.edit-card textarea {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 14px;
}
.edit-card textarea {
    resize: vertical;
    height: 100px;
}
.edit-card input[type="file"] {
    margin-bottom: 15px;
}
.edit-card button {
    width: 100%;
    padding: 12px;
    background: #66a6ff;
    color: #fff;
    border: none;
    border-radius: 30px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.3s, transform 0.3s;
}
.edit-card button:hover {
    background: #89f7fe;
    transform: translateY(-3px);
}
</style>
</head>
<body>
<div class="edit-card">
    <h2>Edit Profile</h2>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <form action="" method="POST" enctype="multipart/form-data">
        <?php if (!empty($profile['avatar']) && file_exists($profile['avatar'])): ?>
            <img src="<?= htmlspecialchars($profile['avatar']) ?>" alt="Profile Image">
        <?php else: ?>
            <img src="default-avatar.png" alt="Profile Image">
        <?php endif; ?>
        <input type="text" name="full_name" placeholder="Full Name" value="<?= htmlspecialchars($profile['full_name']) ?>" required>
        <input type="text" name="occupation" placeholder="Occupation" value="<?= htmlspecialchars($profile['occupation']) ?>" required>
        <textarea name="bio" placeholder="About Me" required><?= htmlspecialchars($profile['bio']) ?></textarea>
        <input type="file" name="avatar" accept="image/*">
        <button type="submit">Update Profile</button>
    </form>
</div>
</body>
</html>
