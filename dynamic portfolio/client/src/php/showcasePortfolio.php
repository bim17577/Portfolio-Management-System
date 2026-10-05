<?php
include("dbConnection.php");
session_start();

// Check if portfolio ID is provided
if (!isset($_GET['id'])) { 
    echo "Portfolio not found!"; 
    exit(); 
}

$portfolio_id = intval($_GET['id']);

// Only set $userId if user is logged in
$userId = isset($_SESSION['id']) ? $_SESSION['id'] : null;

// Get portfolio details
$stmt = $conn->prepare("SELECT * FROM portfolios WHERE id=?");
$stmt->bind_param("i", $portfolio_id);
$stmt->execute();
$portfolio = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$portfolio) { 
    echo "No portfolio found!"; 
    exit(); 
}

// Get portfolio contents
$stmt2 = $conn->prepare("SELECT * FROM portfolio_contents WHERE portfolio_id=?");
$stmt2->bind_param("i", $portfolio_id);
$stmt2->execute();
$contents = $stmt2->get_result();
$stmt2->close();

// Get all media (images/videos)
$stmt3 = $conn->prepare("SELECT * FROM portfolio_images WHERE portfolio_id=?");
$stmt3->bind_param("i", $portfolio_id);
$stmt3->execute();
$mediaFiles = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt3->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($portfolio['title']); ?> - Showcase</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body { background: #1e1e2f; font-family: 'Poppins', sans-serif; color: #f0f0f0; }
.hero-header { background: linear-gradient(135deg, #ff7e5f, #feb47b); color: #fff; text-align: center; padding: 80px 20px; border-radius: 0 0 50% 50%/10%; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
.hero-header h1 { font-size: 3.5em; text-shadow: 2px 2px 15px rgba(0,0,0,0.3); }
.section { padding: 50px 20px; background: #2a2a3d; margin-bottom: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); transition: transform 0.3s; }
.section:hover { transform: translateY(-5px); }
.section h2 { color: #ff7e5f; border-bottom: 2px solid #feb47b; padding-bottom: 8px; margin-bottom: 20px; display: inline-block; }
.masonry { column-count: 3; column-gap: 20px; }
.masonry-item { margin-bottom: 20px; break-inside: avoid; border-radius: 15px; overflow: hidden; background: #33334d; box-shadow: 0 5px 25px rgba(0,0,0,0.3); transition: transform 0.3s; }
.masonry-item:hover { transform: scale(1.05); }
.masonry-item img, .masonry-item video, .masonry-item iframe { width: 100%; display: block; }
.masonry-item .caption { padding: 10px; text-align: center; font-size: 0.9em; color: #ccc; }
.home-button { text-align: center; margin: 50px 0; }
.home-button a { display:inline-block; padding:15px 50px; font-size:18px; font-weight:600; border-radius:50px; text-decoration:none; background: linear-gradient(135deg, #ff7e5f, #feb47b); color:#fff; transition: transform 0.3s, box-shadow 0.3s; }
.home-button a:hover { transform: scale(1.1); box-shadow: 0 10px 35px rgba(0,0,0,0.4); }
.upgrade-card {
    max-width: 900px;
    margin: 50px auto;
    padding: 30px 40px;
    border-radius: 25px;
    background: linear-gradient(135deg, #ffecd2, #fcb69f);
    text-align: center;
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}
.upgrade-card h2 {
    font-size: 28px;
    color: #d35400;
    margin-bottom: 15px;
}
.upgrade-card p {
    font-size: 18px;
    color: #333;
    margin-bottom: 25px;
    line-height: 1.5;
}
.upgrade-btn {
    display: inline-block;
    padding: 15px 35px;
    background: linear-gradient(to right, #ff7e5f, #feb47b);
    color: #fff;
    font-weight: 700;
    font-size: 18px;
    border-radius: 15px;
    text-decoration: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.upgrade-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}
@media(max-width:1200px){ .masonry { column-count: 2; } }
@media(max-width:768px){ .masonry { column-count: 1; } }
</style>
</head>
<body>

<div class="hero-header">
    <h1><?php echo htmlspecialchars($portfolio['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
</div>

<div class="container my-5">
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
                                echo '<div class="masonry"><div class="masonry-item"><iframe width="100%" height="250" src="https://www.youtube.com/embed/'.htmlspecialchars($m[1], ENT_QUOTES).'" frameborder="0" allowfullscreen></iframe></div></div>';
                            } elseif (preg_match('~vimeo\.com/(\d+)~i', $url, $m)) {
                                echo '<div class="masonry"><div class="masonry-item"><iframe width="100%" height="250" src="https://player.vimeo.com/video/'.htmlspecialchars($m[1], ENT_QUOTES).'" frameborder="0" allowfullscreen></iframe></div></div>';
                            } else {
                                echo '<p><a href="'.htmlspecialchars($url, ENT_QUOTES).'" target="_blank">Watch video</a></p>';
                            }
                        }
                        continue;
                    }

                    // Other links
                   // Other links or values
if (is_array($value)) {
    // Convert array to comma-separated string
    echo '<p><strong>' . ucfirst($key) . ':</strong> ' . htmlspecialchars(implode(', ', $value), ENT_QUOTES) . '</p>';
} elseif (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
    // URLs
    echo '<p><strong>' . ucfirst($key) . ':</strong> <a href="' . htmlspecialchars($value, ENT_QUOTES) . '" target="_blank">' . htmlspecialchars($value, ENT_QUOTES) . '</a></p>';
} else {
    // All other values
    echo '<p><strong>' . ucfirst($key) . ':</strong> ' . htmlspecialchars((string)$value, ENT_QUOTES) . '</p>';
}

                    ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endwhile; ?>

    <h2 class="text-center my-5" style="color:#ff7e5f;">Media Gallery</h2>
    <div class="masonry" id="portfolio-gallery">
        <?php foreach($mediaFiles as $file): ?>
            <div class="masonry-item">
                <?php if(isset($file['file_path']) && !empty($file['file_path'])): ?>
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

    <div class="upgrade-card">
    <h2>Download Portfolio</h2>
    <p>Download your complete portfolio as a PDF to share with clients, employers, or schools. Showcase your projects, skills, and experience in a professional format, ready to impress!</p>
    <a href="../pages/paymentPlan.php?id=<?php echo $userId; ?>" class="upgrade-btn">Download Portfolio</a>
</div>


    <div class="home-button">
        <a href="../pages/home.php">Back to Home</a>
    </div>
</div>

</body>
</html>
