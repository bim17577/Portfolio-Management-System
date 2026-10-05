<?php
include("dbConnection.php");
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['id'];
$username = $_SESSION['username'];
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'];
    $occupation = $_POST['occupation'];
    $bio = $_POST['bio'];

    $avatarPath = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatarPath = 'uploads/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarPath);
    }

    // Check if profile exists
    $stmt = $conn->prepare("SELECT id FROM profiles WHERE user_id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $profileExists = $result->num_rows > 0;
    $stmt->close();

    if ($profileExists) {
        $stmt = $conn->prepare("UPDATE profiles SET full_name=?, occupation=?, bio=?, avatar=COALESCE(?, avatar) WHERE user_id=?");
        $stmt->bind_param("ssssi", $fullName, $occupation, $bio, $avatarPath, $userId);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO profiles (user_id, full_name, occupation, bio, avatar) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $userId, $fullName, $occupation, $bio, $avatarPath);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: showcaseprofile.php");
    exit();
}

// Fetch profile if exists
$stmt = $conn->prepare("SELECT full_name, occupation, bio, avatar FROM profiles WHERE user_id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Dashboard - Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .sidebar {
        min-height: 100vh;
        background: linear-gradient(180deg, #007bff, #6610f2);
        color: #fff;
        padding-top: 20px;
    }
    .sidebar h4 {
        font-weight: 600;
    }
    .sidebar a {
        color: #fff;
        text-decoration: none;
        display: block;
        padding: 12px 20px;
        transition: 0.3s;
        border-radius: 8px;
        margin-bottom: 5px;
    }
    .sidebar a:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    .sidebar .active {
        background: rgba(255, 255, 255, 0.3);
        font-weight: 600;
    }
    .content {
        padding: 20px;
    }
    .card-custom {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        padding: 30px;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s;
    }
    .card-custom:hover {
        transform: scale(1.02);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
    .navbar {
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-radius: 10px;
    }
    .navbar-brand {
        font-weight: 600;
        color: #007bff !important;
    }
    .avatar-preview {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid #fff;
        margin-bottom: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }
</style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block sidebar">
            <div class="text-center mb-4">
                <h4>My Dashboard</h4>
                <p class="small">Welcome!</p>
            </div>
            <ul class="nav flex-column">
                <li><a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="profile.php" class="active"><i class="bi bi-person-circle"></i> Profile</a></li>
                <li><a href="showcaseProfile.php"><i class="bi bi-star"></i> Showcase</a></li>
                <li><a href="portfolioform.php"><i class="bi bi-folder-plus"></i> Create Portfolio</a></li>
                <li><a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto col-lg-10 content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg mb-4 px-3">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">Profile</a>
                    <div class="d-flex">
                    <span class="me-3">👤 <?php echo htmlspecialchars($username); ?></span>
                        <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </div>
                </div>
            </nav>

            <!-- Profile Form -->
            <div class="card-custom">
                <h4 class="mb-3"><i class="bi bi-person-lines-fill"></i> Update Your Profile</h4>
                <form method="POST" enctype="multipart/form-data">
                    <div class="text-center">
                        <img src="<?php echo htmlspecialchars($user['avatar'] ?? 'images/default-avatar.png'); ?>" 
                             class="avatar-preview mb-3" id="avatarPreview">
                    </div>
                    <input class="form-control mb-3" type="file" name="avatar" accept="image/*"
                           onchange="document.getElementById('avatarPreview').src=window.URL.createObjectURL(this.files[0])">
                    <input class="form-control mb-3" type="text" name="full_name" placeholder="Full Name"
                           value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                    <input class="form-control mb-3" type="text" name="occupation" placeholder="Occupation"
                           value="<?php echo htmlspecialchars($user['occupation'] ?? ''); ?>">
                    <textarea class="form-control mb-3" name="bio" placeholder="About Me"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    <button type="submit" class="btn btn-primary w-100">Save Profile</button>
                </form>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
