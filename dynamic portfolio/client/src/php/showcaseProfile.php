<?php
include("dbConnection.php");
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$userId = $_SESSION['id'];

// --- Handle profile deletion ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_profile'])) {
    // Delete profile
    $stmtDelete = $conn->prepare("DELETE FROM profiles WHERE user_id = ?");
    $stmtDelete->bind_param("i", $userId);
    $stmtDelete->execute();
    $stmtDelete->close();

    // Optional: delete user's portfolio and contents
    $stmtPortfolio = $conn->prepare("SELECT id FROM portfolios WHERE user_id = ?");
    $stmtPortfolio->bind_param("i", $userId);
    $stmtPortfolio->execute();
    $stmtPortfolio->bind_result($portfolioId);
    if ($stmtPortfolio->fetch()) {
        $stmtPortfolio->close();

        // Delete portfolio contents
        $stmtContents = $conn->prepare("DELETE FROM portfolio_contents WHERE portfolio_id=?");
        $stmtContents->bind_param("i", $portfolioId);
        $stmtContents->execute();
        $stmtContents->close();

        // Delete portfolio images
        $stmtImages = $conn->prepare("DELETE FROM portfolio_images WHERE portfolio_id=?");
        $stmtImages->bind_param("i", $portfolioId);
        $stmtImages->execute();
        $stmtImages->close();

        // Delete portfolio itself
        $stmtPortfolioDelete = $conn->prepare("DELETE FROM portfolios WHERE id=?");
        $stmtPortfolioDelete->bind_param("i", $portfolioId);
        $stmtPortfolioDelete->execute();
        $stmtPortfolioDelete->close();
    } else {
        $stmtPortfolio->close();
    }

    // Log out user after deletion
    $_SESSION = [];
    session_destroy();
    header("Location: login.php?deleted=1");
    exit();
}

// Fetch profile
$sql = "SELECT full_name, occupation, bio, avatar FROM profiles WHERE user_id=?";
$stmt = $conn->prepare($sql);
if (!$stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$profile = ($result->num_rows > 0) ? $result->fetch_assoc() : null;
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Showcase Portfolio</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.sidebar { min-height: 100vh; background: linear-gradient(180deg, #007bff, #6610f2); color: #fff; padding-top: 20px; }
.sidebar h4 { font-weight: 600; }
.sidebar a { color: #fff; text-decoration: none; display: block; padding: 12px 20px; transition: 0.3s; border-radius: 8px; margin-bottom: 5px; }
.sidebar a:hover { background: rgba(255, 255, 255, 0.2); }
.sidebar .active { background: rgba(255, 255, 255, 0.3); font-weight: 600; }
.content { padding: 20px; }
.card-custom { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); padding: 30px; transition: transform 0.2s ease-in-out, box-shadow 0.2s; }
.card-custom:hover { transform: scale(1.02); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
.navbar { background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 10px; }
.navbar-brand { font-weight: 600; color: #007bff !important; }
.profile-card { background: #fff; border-radius: 25px; box-shadow: 0 15px 40px rgba(0,0,0,0.2); padding: 40px 30px; text-align: center; transition: transform 0.4s, box-shadow 0.4s; margin-bottom: 40px; }
.profile-card:hover { transform: translateY(-12px); box-shadow: 0 25px 60px rgba(0,0,0,0.25); }
.profile-card img { border-radius: 50%; width: 150px; height: 150px; object-fit: cover; margin-bottom: 20px; border: 5px solid #fff; box-shadow: 0 5px 20px rgba(0,0,0,0.15); transition: transform 0.4s; }
.profile-card img:hover { transform: scale(1.08); }
.profile-card h2 { font-size: 24px; color: #222; margin: 10px 0 5px; }
.profile-card h4 { font-size: 15px; color: #6c63ff; font-weight: 600; margin-bottom: 20px; }
.profile-card p { font-size: 14px; color: #555; line-height: 1.6; margin-bottom: 25px; }
.profile-card .btn { display: inline-block; padding: 10px 22px; font-size: 14px; font-weight: 600; color: #fff; background: #6c63ff; border-radius: 30px; text-decoration: none; box-shadow: 0 5px 20px rgba(108,99,255,0.3); transition: all 0.3s; }
.profile-card .btn:hover { background: #574fd6; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(108,99,255,0.4); }
.profile-card .btn-danger { margin-left: 15px; background: #e53935; }
.profile-card .btn-danger:hover { background: #c62828; }
.showcase-cta { max-width: 500px; margin-left:auto; }
.showcase-cta h2 { font-size: 28px; color: #222; margin-bottom: 15px; }
.showcase-cta p { font-size: 16px; color: #555; line-height: 1.6; margin-bottom: 30px; }
.showcase-cta a { display: inline-block; padding: 15px 35px; font-size: 16px; font-weight: 600; color: #fff; background: #ff7b6c; border-radius: 30px; text-decoration: none; box-shadow: 0 5px 20px rgba(255,123,108,0.3); transition: all 0.3s; }
.showcase-cta a:hover { background: #ff5a45; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(255,123,108,0.4); }
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

            <div class="row">
                <!-- Profile Card -->
                <div class="col-md-6">
                    <div class="profile-card">
                        <?php if ($profile && !empty($profile['avatar']) && file_exists($profile['avatar'])): ?>
                            <img src="<?= htmlspecialchars($profile['avatar']) ?>" alt="Profile Image">
                        <?php else: ?>
                            <img src="default-avatar.png" alt="Profile Image">
                        <?php endif; ?>
                        <h2><?= htmlspecialchars($profile['full_name'] ?? 'No Name') ?></h2>
                        <h4><?= htmlspecialchars($profile['occupation'] ?? 'No Occupation') ?></h4>
                        <p><?= nl2br(htmlspecialchars($profile['bio'] ?? 'No bio available.')) ?></p>
                        <div class="d-flex justify-content-center">
                            <a href="editProfile.php" class="btn">Edit Profile</a>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete your profile? This cannot be undone.')">
                                <button type="submit" name="delete_profile" class="btn btn-danger">Delete Profile</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Call-to-Action -->
                <div class="col-md-6 d-flex align-items-center">
                    <div class="showcase-cta">
                        <h2>Ready to showcase your work?</h2>
                        <p>Create your own stunning portfolio and let your skills and projects shine! Add images, descriptions, and more to impress potential clients and employers.</p>
                        <a href="portfolioform.php">Create Your Portfolio</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
