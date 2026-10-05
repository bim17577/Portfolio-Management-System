<?php
include("dbConnection.php");
session_start();

$searchResults = [];

if (isset($_GET['query'])) {
    $query = "%" . $_GET['query'] . "%";

    // Search title, description, and JSON content
    $stmt = $conn->prepare(
        "SELECT DISTINCT p.id, p.title, p.description
         FROM portfolios p
         LEFT JOIN portfolio_contents pc ON p.id = pc.portfolio_id
         WHERE p.title LIKE ? OR p.description LIKE ? OR pc.content LIKE ?
         ORDER BY p.created_at DESC"
    );

    if ($stmt) {
        $stmt->bind_param("sss", $query, $query, $query);
        $stmt->execute();
        $result = $stmt->get_result();
        $searchResults = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        echo "SQL Prepare Error: " . $conn->error;
    }
}


// Base URL for portfolio view (your PC IPv4 + project path)
$hostIP = "192.168.8.101"; // Your local PC IP
$basePath = "/Portfolio-Project/dynamic-portfolio/client/src/php/showcasePortfolio.php?id=";

// Get the current Ngrok public URL automatically
$ngrokApi = file_get_contents("http://127.0.0.1:4040/api/tunnels");
$data = json_decode($ngrokApi, true);

// Build the site URL dynamically
if(isset($data['tunnels'][0]['public_url'])){
    $siteURL = $data['tunnels'][0]['public_url'] . $basePath;
} else {
    $siteURL = "http://$hostIP$basePath"; // fallback to local IP
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Portfolios</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f0fbf5; 
      color: #1a3b1a; 
      margin: 0;
      padding-top: 70px; 
      padding-bottom: 60px; 
      width: 100vw;
      overflow-x: hidden;
    }
    header {
      background: #4caf50; 
      color: white;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 70px;
      display: flex;
      align-items: center;
      padding: 0 40px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
      z-index: 1000;
    }
    header h1 { font-size: 1.8rem; margin: 0; font-weight: 700; letter-spacing: 1.2px; }
    nav { margin-left: auto; }
    nav a { color: white; margin-left: 24px; font-weight: 600; text-decoration: none; font-size: 1rem; transition: color 0.3s ease; }
    nav a:hover { color: #a8e6a1; text-decoration: underline; }
    footer {
      background: #2e7d32;
      color: white;
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.95rem;
      font-weight: 600;
      box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.15);
    }
    .hero { text-align: center; padding: 40px 20px 20px; }
    .search-form { margin-top: 20px; display: flex; justify-content: center; gap: 10px; }
    .search-form input {
      padding: 10px;
      width: 300px;
      border-radius: 25px;
      border: 2px solid #4caf50;
      font-size: 1rem;
    }
    .search-form button {
      padding: 10px 20px;
      background: #4caf50;
      color: white;
      border: none;
      border-radius: 25px;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.3s;
    }
    .search-form button:hover { background: #388e3c; }
    .portfolio-section {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 35px;
      width: 90%;
      margin: auto;
      padding-bottom: 60px;
    }
    .portfolio-card {
      background: linear-gradient(145deg, #ffffff, #e6f4ea);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 12px 25px rgba(76, 175, 80, 0.2);
      transition: transform 0.4s ease, box-shadow 0.4s ease;
      display: flex;
      flex-direction: column;
      cursor: pointer;
      border: 3px solid black;
      margin-bottom: 3px;
    }
    .portfolio-card:hover { transform: translateY(-6px) scale(1.02); box-shadow: 0 20px 35px rgba(76, 175, 80, 0.35); }
    .portfolio-image { width: 100%; height: 220px; object-fit: cover; border-bottom: 1px solid #c8e6c9; transition: transform 0.4s ease; }
    .portfolio-card:hover .portfolio-image { transform: scale(1.05); }
    .portfolio-summary { padding: 22px 25px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
    .portfolio-title { font-size: 1.9rem; font-weight: 700; color: #2c3e50; margin-bottom: 12px; }
    .portfolio-description { color: #4a4a4a; font-size: 1.05rem; line-height: 1.6; margin-bottom: 18px; }
    .btn-view-portfolio {
      padding: 12px 28px; font-size: 1rem; font-weight: 700; color: #fff;
      background: linear-gradient(90deg, #4caf50, #81c784);
      border: none; border-radius: 35px; cursor: pointer; align-self: flex-start; transition: all 0.3s ease;
    }
    .btn-view-portfolio:hover { background: linear-gradient(90deg, #388e3c, #66bb6a); transform: scale(1.05); }
    .qr-container img { margin-top: 15px; width: 150px; height: 150px; border-radius: 15px; box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3); transition: transform 0.3s ease; }
    .qr-container img:hover { transform: scale(1.05); }
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

<header>
  <h1>Smart Portfolio Site</h1>
  <nav>
    <a href="./home.php">Home</a>
    <a href="./portfolio.php">Portfolio</a>
    <a href="./about.html">About</a>
    <a href="./contact.html">Contact</a>
  </nav>
</header>

<section class="hero">
  <h1>Search Portfolios</h1>
  <form method="get" action="search.php" class="search-form">
    <input type="text" name="query" placeholder="Search portfolios..." 
           value="<?= isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '' ?>" required>
    <button type="submit">Search</button>
  </form>
</section>

<main class="portfolio-container">
  <section class="portfolio-section">
    <?php if (!empty($searchResults)): ?>
        <?php foreach ($searchResults as $portfolio): ?>
        <article class="portfolio-item">
          <div class="portfolio-card">
            <img src="https://picsum.photos/seed/<?php echo $portfolio['id']; ?>/1200/280" 
                 alt="<?php echo htmlspecialchars($portfolio['title']); ?>" class="portfolio-image" />
            <div class="portfolio-summary">
              <h2 class="portfolio-title"><?php echo htmlspecialchars($portfolio['title']); ?></h2>
              <p class="portfolio-description"><?php echo nl2br(htmlspecialchars($portfolio['description'])); ?></p>
              <button class="btn-view-portfolio" data-portfolio-url="<?php echo $siteURL.$portfolio['id']; ?>">
                View Portfolio
              </button>
              <div class="qr-container" style="margin-top:10px;"></div>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
    <?php elseif (isset($_GET['query'])): ?>
        <p style="text-align:center; font-size:1.2rem;">
          No results found for "<strong><?= htmlspecialchars($_GET['query']) ?></strong>".
        </p>
    <?php endif; ?>
  </section>

  <!-- Back to Home Button -->
  <div class="back-home-btn">
    <a href="../pages/home.php"><button>Back to Home</button></a>
  </div>
</main>

<footer>
  &copy; 2025 Smart Portfolio Site. All rights reserved.
</footer>

<script>
document.querySelectorAll('.btn-view-portfolio').forEach(button => {
  button.addEventListener('click', () => {
    const url = button.dataset.portfolioUrl;
    const qrContainer = button.nextElementSibling;

    if (qrContainer.style.display === 'none' || qrContainer.style.display === '') {
      qrContainer.innerHTML = ''; 
      new QRCode(qrContainer, { text: url, width: 150, height: 150 });
      qrContainer.style.display = 'block';
      button.textContent = "Hide QR";
    } else {
      qrContainer.style.display = 'none';
      qrContainer.innerHTML = '';
      button.textContent = "View Portfolio";
    }
  });
});
</script>
</body>
</html>
