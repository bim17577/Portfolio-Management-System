<?php
include("../php/dbConnection.php");
session_start();

// --- Handle search filter ---
$occupationFilter = "";
if (isset($_GET['occupation'])) {
    $occupationFilter = trim($_GET['occupation']);
}

// --- Fetch latest 6 profiles (with optional filter) ---
$selectAvatar = "avatar"; // database column storing image path
if ($occupationFilter !== '') {
    $stmt = $conn->prepare("
        SELECT full_name, occupation, bio, $selectAvatar 
        FROM profiles 
        WHERE occupation LIKE ? 
        ORDER BY id DESC 
        LIMIT 9
    ");
    $like = "%" . $occupationFilter . "%";
    $stmt->bind_param("s", $like);
} else {
    $stmt = $conn->prepare("
        SELECT full_name, occupation, bio, $selectAvatar 
        FROM profiles 
        ORDER BY id DESC 
        LIMIT 9
    ");
}

$stmt->execute();
$result = $stmt->get_result();
$profiles = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Profiles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg,rgb(219, 252, 187),rgb(55, 104, 41));
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .profile-card {
            box-shadow: 0px 8px 25px rgba(0,0,0,0.2);
            border-radius: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            background: #fff;
        }
        .profile-card:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0px 12px 40px rgba(0,0,0,0.3);
        }
        .profile-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }
        .card-body {
            text-align: center;
            padding: 20px;
        }
        .card-title {
            font-weight: 700;
            color: #333;
        }
        .text-muted {
            color: #777 !important;
        }
        .card-text {
            margin-top: 10px;
            color: #555;
        }
        .search-form input {
            border-radius: 50px;
            padding: 10px 20px;
            border: none;
            box-shadow: 0px 3px 10px rgba(0,0,0,0.1);
        }
        .search-form button, .search-form a {
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
        }
        h2 {
            font-weight: 700;
            color: #333;
            text-shadow: 1px 1px 5px rgba(0,0,0,0.1);
        }

        .back-home-btn {
    margin-top: 30px;
    text-align: center;
}

.back-home-btn a {
    text-decoration: none;
}

.back-home-btn button {
    padding: 12px 28px;
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(90deg, #4caf50, #81c784);
    border: none;
    border-radius: 35px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 10px;
}

.back-home-btn button:hover {
    background: linear-gradient(90deg, #388e3c, #66bb6a);
    transform: scale(1.05);
}
    </style>
</head>
<body>
<div class="container py-5">

    <h2 class="text-center mb-4">Profiles</h2>

    <!-- Search Form -->
    <form method="GET" class="d-flex justify-content-center mb-5 search-form">
        <input type="text" name="occupation" class="form-control w-50 me-2"
               placeholder="Search by occupation..."
               value="<?= htmlspecialchars($occupationFilter) ?>">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="viewprofile.php" class="btn btn-secondary ms-2">Reset</a>
    </form>

    <div class="row g-4">
        <?php if (count($profiles) > 0): ?>
            <?php foreach ($profiles as $profile): ?>
                <div class="col-md-4">
                    <div class="card profile-card">
                        <?php 
                        $avatar = $profile[$selectAvatar];
                        // check if file exists in '../php/uploads/' folder
                        if (!empty($avatar) && file_exists("../php/uploads/" . basename($avatar))) {
                            $imgSrc = "../php/uploads/" . basename($avatar);
                        } else {
                            $imgSrc = "https://via.placeholder.com/300x220?text=No+Image";
                        }
                        ?>
                        <img src="<?= $imgSrc ?>" class="profile-img" alt="Profile Picture">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($profile['full_name']) ?></h5>
                            <p class="text-muted"><?= htmlspecialchars($profile['occupation']) ?></p>
                            <p class="card-text"><?= nl2br(htmlspecialchars($profile['bio'])) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">No profiles found.</p>
        <?php endif; ?>
    </div>

</div>

<!-- Back to Home Button -->
<div class="back-home-btn">
    <a href="../pages/home.php"><button>Back to Home</button></a>
  </div>
</main>

</body>
</html>
