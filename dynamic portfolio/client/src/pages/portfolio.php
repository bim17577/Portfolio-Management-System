<?php
include("../php/dbConnection.php");
session_start();

// Fetch all portfolios from DB (latest 6)
$stmt = $conn->prepare("SELECT id, title, description FROM portfolios ORDER BY created_at DESC LIMIT 9");
$stmt->execute();
$result = $stmt->get_result();
$portfolios = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Portfolio Blog Style - Light Green Theme</title>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <style>
    /* Body and overall layout */
    body {
  display: flex;
  flex-direction: column;
  min-height: 100vh; /* full viewport height */
  margin: 0;
  padding-top: 70px; /* space for fixed header */
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: #f0fbf5;
  color: #1a3b1a;
}

main {
  flex: 1; /* main content takes all remaining space */
}

   /* Header Styles */
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

/* Site title */
header h1 {
  font-size: 1.8rem;
  margin: 0;
  font-weight: 700;
  letter-spacing: 1.2px;
}

/* Navigation - desktop */
nav {
  display: flex;
  margin-left: auto;
}

nav a {
  color: white;
  margin-left: 24px;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.3s ease;
  font-size: 1rem;
}

nav a:hover {
  color: #a8e6a1;
  text-decoration: underline;
}

/* Hamburger toggle - hidden on desktop */
.nav-toggle {
  display: none;
  font-size: 2rem;
  cursor: pointer;
  margin-left: auto;
  transition: transform 0.3s ease;
}

/* Mobile responsiveness */
@media (max-width: 992px) {
  nav {
    position: absolute;
    top: 70px;
    right: 0;
    background-color: #2e7d32;
    flex-direction: column;
    width: 200px;
    display: none;
    padding: 20px;
    border-radius: 0 0 0 10px;
  }

  nav.active {
    display: flex;
  }

  nav a {
    margin: 12px 0;
  }

  /* Show toggle and move it to right */
  .nav-toggle {
    display: block;
    margin-left: auto;
  }
}

    /* Footer Styles */
    footer {
  background-color: #2e7d32; /* dark green base */
  color: #ffffff;
  padding: 25px 50px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.footer-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: auto;
  width: 100%;
}

.footer-left,
.footer-right {
  display: flex;
  gap: 20px;
}

.footer-left a,
.footer-right a {
  color: white;
  text-decoration: none;
  font-weight: 500;
  position: relative;
  transition: all 0.3s ease;
}

/* Elegant underline hover effect for links */
.footer-left a::after,
.footer-right a::after {
  content: "";
  position: absolute;
  width: 0%;
  height: 2px;
  bottom: -3px;
  left: 0;
  background-color: #81c784; /* bright green */
  transition: 0.3s;
}

.footer-left a:hover::after,
.footer-right a:hover::after {
  width: 100%;
}

.footer-center {
  text-align: center;
  font-size: 0.95rem;
  color: #c8e6c9; /* lighter green for subtle contrast */
}

/* Social icons styling */
.footer-right a i {
  font-size: 1.2rem;
  transition: transform 0.3s ease, color 0.3s ease;
}

.footer-right a:hover i {
  color: #81c784;
  transform: scale(1.3);
}

/* Responsive: stack vertically on small screens */
@media (max-width: 768px) {
  .footer-container {
    flex-direction: column;
    text-align: center;
    gap: 15px;
  }
  .footer-left,
  .footer-center,
  .footer-right {
    justify-content: center;
  }
}

    /* Portfolio Section Styling */
.portfolio-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 35px;
  width: 90%;
  margin: auto;
  padding-bottom: 60px;
}

/* Portfolio Card */
.portfolio-card {
  background: linear-gradient(145deg, #ffffff, #e6f4ea);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 12px 25px rgba(76, 175, 80, 0.2);
  transition: transform 0.4s ease, box-shadow 0.4s ease;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  border: 3px solid black; /* Only addition: black border */
}

.portfolio-card:hover {
  transform: translateY(-6px) scale(1.02);
  box-shadow: 0 20px 35px rgba(76, 175, 80, 0.35);
}

/* Portfolio Image */
.portfolio-image {
  width: 100%;
  height: 220px;
  object-fit: cover;
  border-bottom: 1px solid #c8e6c9;
  transition: transform 0.4s ease;
}

.portfolio-card:hover .portfolio-image {
  transform: scale(1.05);
}

/* Portfolio Summary */
.portfolio-summary {
  padding: 22px 25px;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* Titles & Description */
.portfolio-title {
  font-size: 1.9rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 12px;
}

.portfolio-description {
  color: #4a4a4a;
  font-size: 1.05rem;
  line-height: 1.6;
  margin-bottom: 18px;
}

/* View Portfolio Button */
.btn-view-portfolio {
  padding: 12px 28px;
  font-size: 1rem;
  font-weight: 700;
  color: #fff;
  background: linear-gradient(90deg, #4caf50, #81c784);
  border: none;
  border-radius: 35px;
  cursor: pointer;
  align-self: flex-start;
  transition: all 0.3s ease;
}

.btn-view-portfolio:hover {
  background: linear-gradient(90deg, #388e3c, #66bb6a);
  transform: scale(1.05);
}

/* QR Code */
.qr-container img {
  margin-top: 15px;
  width: 150px;
  height: 150px;
  border-radius: 15px;
  box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
  transition: transform 0.3s ease;
}

.qr-container img:hover {
  transform: scale(1.05);
}


  </style>
</head>
<body>

<header>
  <h1>Smart Portfolio Site</h1>

  <!-- Hamburger toggle button -->
  <div class="nav-toggle" id="nav-toggle">☰</div>

  <nav>
      <a href="./home.php">Home</a>
      <a href="./portfolio.php">Portfolio</a>
      <a href="./ExploreMore.html">Explore</a>
      <a href="./about.html">About</a>
      <a href="./contact.html">Contact</a>
      <a href="../php/login.php"><span>👤</span> Login</a>
    </nav>
</header>

<main class="portfolio-container">
  <section class="portfolio-section">
    <?php foreach ($portfolios as $portfolio): ?>
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
  </section>
</main>


<footer>
  <div class="footer-container">
    <!-- Left: Quick Links -->
    <div class="footer-left">
      <span class="quick-title">Quick Links:</span>
      <a href="home.php">Home</a>
      <a href="about.html">About</a>
      <a href="portfolio.php">Portfolio</a>
      <a href="contact.html">Contact</a>
    </div>

    <!-- Center: Copyright -->
    <div class="footer-center">
      &copy; 2025 Smart Portfolio Site. All rights reserved.
    </div>

    <!-- Right: Social Media -->
    <div class="footer-right">
      <a href="https://web.facebook.com/?_rdc=1&_rdr#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
      <a href="https://x.com/i/flow/login" title="Twitter"><i class="fab fa-twitter"></i></a>
      <a href="https://www.instagram.com/" title="Instagram"><i class="fab fa-instagram"></i></a>
      <a href="https://www.linkedin.com/home?originalSubdomain=lk" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
    </div>
  </div>
</footer>

<script>
  // Show/Hide QR code instantly using QRCode.js
  document.querySelectorAll('.btn-view-portfolio').forEach(button => {
    button.addEventListener('click', () => {
      const url = button.dataset.portfolioUrl;
      const qrContainer = button.nextElementSibling;

      if (qrContainer.style.display === 'none' || qrContainer.style.display === '') {
        qrContainer.innerHTML = ''; // clear previous QR

        // --- Generate QR Code ---
        new QRCode(qrContainer, {
          text: url,
          width: 150,
          height: 150,
        });

        // --- Add share links ---
        const shareDiv = document.createElement("div");
        shareDiv.classList.add("share-links");
        shareDiv.style.marginTop = "15px";
        shareDiv.innerHTML = `
          <p style="font-weight:600; margin-bottom:8px;">Share this portfolio:</p>
          <a href="https://api.whatsapp.com/send?text=${encodeURIComponent(url)}" target="_blank" title="Share on WhatsApp">
            <i class="fab fa-whatsapp" style="color:#25D366; font-size:1.5rem; margin-right:12px;"></i>
          </a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}" target="_blank" title="Share on Facebook">
            <i class="fab fa-facebook" style="color:#1877F2; font-size:1.5rem; margin-right:12px;"></i>
          </a>
          <a href="https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(url)}" target="_blank" title="Share on LinkedIn">
            <i class="fab fa-linkedin" style="color:#0077B5; font-size:1.5rem; margin-right:12px;"></i>
          </a>
          <a href="https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}" target="_blank" title="Share on Twitter">
            <i class="fab fa-twitter" style="color:#1DA1F2; font-size:1.5rem; margin-right:12px;"></i>
          </a>
          <a href="mailto:?subject=Check out this portfolio&body=${encodeURIComponent(url)}" title="Share via Email">
            <i class="fas fa-envelope" style="color:#c71610; font-size:1.5rem;"></i>
          </a>
        `;
        qrContainer.appendChild(shareDiv);

        qrContainer.style.display = 'block';
        button.textContent = "Hide QR + Share";

      } else {
        qrContainer.style.display = 'none';
        qrContainer.innerHTML = '';
        button.textContent = "View Portfolio";
      }
    });
  });

  // Mobile nav toggle
  const toggle = document.getElementById('nav-toggle');
  const nav = document.querySelector('nav');

  toggle.addEventListener('click', () => {
    nav.classList.toggle('active');
  });
</script>

</body>
</html>
