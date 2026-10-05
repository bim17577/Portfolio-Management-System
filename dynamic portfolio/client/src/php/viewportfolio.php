<?php
include("dbConnection.php");
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['id'];
$username = $_SESSION['username'];

// --- Handle portfolio deletion ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_portfolio'])) {
    // Delete portfolio contents
    $stmtContents = $conn->prepare("DELETE FROM portfolio_contents WHERE portfolio_id=(SELECT id FROM portfolios WHERE user_id=?)");
    $stmtContents->bind_param("i", $userId);
    $stmtContents->execute();
    $stmtContents->close();

    // Delete portfolio media
    $stmtMedia = $conn->prepare("DELETE FROM portfolio_images WHERE portfolio_id=(SELECT id FROM portfolios WHERE user_id=?)");
    $stmtMedia->bind_param("i", $userId);
    $stmtMedia->execute();
    $stmtMedia->close();

    // Delete the portfolio itself
    $stmtPortfolio = $conn->prepare("DELETE FROM portfolios WHERE user_id=?");
    $stmtPortfolio->bind_param("i", $userId);
    $stmtPortfolio->execute();
    $stmtPortfolio->close();

    header("Location: dashboard.php?deleted=1");
    exit();
}

// Fetch the user's latest portfolio
$stmt = $conn->prepare("SELECT * FROM portfolios WHERE user_id=? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$portfolio = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$portfolio) {
    $noPortfolio = true;
} else {
    $portfolioId = $portfolio['id'];

    // Get portfolio contents
    $stmt2 = $conn->prepare("SELECT * FROM portfolio_contents WHERE portfolio_id=?");
    $stmt2->bind_param("i", $portfolioId);
    $stmt2->execute();
    $contents = $stmt2->get_result();
    $stmt2->close();

    // Get portfolio media (images/videos)
    $stmt3 = $conn->prepare("SELECT * FROM portfolio_images WHERE portfolio_id=?");
    $stmt3->bind_param("i", $portfolioId);
    $stmt3->execute();
    $mediaFiles = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt3->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Portfolio - <?php echo htmlspecialchars($username); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #f4f4f9; }
.navbar { margin-bottom: 30px; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.hero-header { background: linear-gradient(135deg,#ff7e5f,#feb47b); color: #fff; text-align:center; padding:60px 20px; border-radius:0 0 50% 50%/10%; }
.hero-header h1 { font-size: 3em; }
.section { padding: 40px 20px; background: #fff; margin-bottom: 30px; border-radius: 15px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); }
.section h2 { color:#ff7e5f; margin-bottom:20px; }
.masonry { column-count: 3; column-gap: 20px; }
.masonry-item { margin-bottom: 20px; break-inside: avoid; border-radius:15px; overflow:hidden; background:#eee; box-shadow:0 5px 15px rgba(0,0,0,0.1); }
.masonry-item img, .masonry-item video, .masonry-item iframe { width:100%; display:block; }
.masonry-item .caption { padding:10px; text-align:center; font-size:0.9em; color:#333; }
.home-button, .action-buttons { text-align:center; margin:30px 0; }
.home-button a, .action-buttons a, .action-buttons button { display:inline-block; padding:12px 30px; font-size:16px; font-weight:600; border-radius:50px; text-decoration:none; margin:0 10px; transition: transform 0.3s; }
.home-button a { background:#ff7e5f; color:#fff; }
.home-button a:hover, .action-buttons a:hover, .action-buttons button:hover { transform: scale(1.05); }
.action-buttons a { background:#0d6efd; color:#fff; }
.action-buttons button { background:#dc3545; color:#fff; border:none; cursor:pointer; }
@media(max-width:1200px){ .masonry { column-count:2; } }
@media(max-width:768px){ .masonry { column-count:1; } }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">My Portfolio</a>
    <div class="d-flex">
      <span class="me-3">👤 <?php echo htmlspecialchars($username); ?></span>
      <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>
</nav>

<div class="container">
<?php if(isset($noPortfolio) && $noPortfolio): ?>
    <div class="text-center mt-5">
        <h2>No Portfolio Found</h2>
        <p>You haven’t created a portfolio yet. Start building your portfolio now!</p>
        <a href="portfolioform.php" class="btn btn-primary">Create Portfolio</a>
    </div>
<?php else: ?>
    <div class="hero-header">
        <h1><?php echo htmlspecialchars($portfolio['title']); ?></h1>
    </div>

    <div class="action-buttons">
    <a href="editportfolio.php?id=<?php echo $portfolioId; ?>">Edit Portfolio</a>

        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete your portfolio? This cannot be undone.')">
            <button type="submit" name="delete_portfolio">Delete Portfolio</button>
        </form>
    </div>

    <?php while($row = $contents->fetch_assoc()): ?>
        <?php $items = json_decode($row['content'], true); ?>
        <div class="section">
            <h2><?php echo ucfirst($row['content_type']); ?></h2>
            <?php foreach($items as $item): ?>
                <?php foreach($item as $key => $value): ?>
                    <?php
                    if ($key === 'images') continue;

                    // Videos
                    if ($key === 'videos' && !empty($value)) {
                        $url = trim($value);
                        if (filter_var($url, FILTER_VALIDATE_URL)) {
                            if (preg_match('~(?:v=|youtu\.be/)([A-Za-z0-9_\-]{6,})~', $url, $m)) {
                                echo '<div class="masonry"><div class="masonry-item"><iframe width="100%" height="250" src="https://www.youtube.com/embed/'.htmlspecialchars($m[1]).'" frameborder="0" allowfullscreen></iframe></div></div>';
                            } elseif (preg_match('~vimeo\.com/(\d+)~i', $url, $m)) {
                                echo '<div class="masonry"><div class="masonry-item"><iframe width="100%" height="250" src="https://player.vimeo.com/video/'.htmlspecialchars($m[1]).'" frameborder="0" allowfullscreen></iframe></div></div>';
                            } else {
                                echo '<p><a href="'.htmlspecialchars($url).'" target="_blank">Watch video</a></p>';
                            }
                        }
                        continue;
                    }

                    // Links and text
                    if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
                        echo '<p><strong>'.ucfirst($key).':</strong> <a href="'.htmlspecialchars($value).'" target="_blank">'.htmlspecialchars($value).'</a></p>';
                    } else {
                        echo '<p><strong>'.ucfirst($key).':</strong> '.htmlspecialchars((string)$value).'</p>';
                    }
                    ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endwhile; ?>

    <h2 class="text-center my-5">Media Gallery</h2>
    <div class="masonry" id="portfolio-gallery">
        <?php foreach($mediaFiles as $file): ?>
            <div class="masonry-item">
                <?php if(!empty($file['file_path'])): ?>
                    <?php if(isset($file['file_type']) && strpos($file['file_type'], 'video') !== false): ?>
                        <video controls>
                            <source src="<?php echo htmlspecialchars($file['file_path']); ?>" type="<?php echo htmlspecialchars($file['file_type']); ?>">
                            Your browser does not support the video tag.
                        </video>
                    <?php else: ?>
                        <img src="<?php echo htmlspecialchars($file['file_path']); ?>" alt="Portfolio Image">
                    <?php endif; ?>
                <?php else: ?>
                    <img src="https://via.placeholder.com/300x220?text=No+Media" alt="No Media">
                <?php endif; ?>
                <?php if(!empty($file['caption'])): ?>
                    <div class="caption"><?php echo htmlspecialchars($file['caption']); ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="home-button">
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
<?php endif; ?>
</div>

</body>
</html>
