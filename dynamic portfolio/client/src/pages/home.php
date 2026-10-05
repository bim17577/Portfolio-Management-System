<?php
include("../php/dbConnection.php");
session_start();

// Fetch all portfolios from DB (latest 6)
$stmt = $conn->prepare("SELECT id, title, description FROM portfolios ORDER BY created_at DESC LIMIT 6");
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







// Fetch latest 5 feedbacks from the database
$feedbackStmt = $conn->prepare("SELECT name, subject, message, rating, created_at FROM feedback ORDER BY created_at DESC LIMIT 5");
$feedbackStmt->execute();
$feedbackResult = $feedbackStmt->get_result();
$feedbacks = $feedbackResult->fetch_all(MYSQLI_ASSOC);
$feedbackStmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Portfolio Blog Style - Light Green Theme</title>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <style>
   
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
  background-color: #2e7d32;
  color: #ffffff;
  padding: 25px 50px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;   /* full width */
  z-index: 1000;
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
.hero {
    background: linear-gradient(135deg, #4caf50, #81c784);
    color: #fff;
    text-align: center;
    padding: 100px 40px;  /* optional bigger padding */
    border-radius: 20px;
    margin: 30px 40px 50px; /* maintain top/bottom and side spacing */
    width: auto;          /* allow full width */
    max-width: none;      /* remove max-width */
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
  }
  
  .hero h1 {
    font-size: 3.2rem;
    font-weight: 700;
    margin-bottom: 20px;
    line-height: 1.2;
    letter-spacing: 1.5px;
  }
  .hero p {
    font-size: 1.3rem;
    max-width: 750px;
    margin: 0 auto;
  }
  /* Simple search box styling */
 .search-box {
      text-align: center;
      margin: 30px auto;
    }
    .search-box input[type="text"] {
      width: 50%;
      padding: 12px;
      border: 2px solid #388e3c;
      border-radius: 30px;
      outline: none;
      font-size: 1rem;
    }
    .search-box button {
      padding: 12px 24px;
      background: #388e3c;
      border: none;
      color: white;
      border-radius: 30px;
      margin-left: 10px;
      cursor: pointer;
      font-weight: 600;
    }
    .search-box button:hover {
      background: #2c6d2c;
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


<section class="banner-container">
  <div class="banner-left" style="position: relative;">
    <img src="/Portfolio-Project/dynamic-portfolio/client/public/uploads/image7.png" alt="Left Banner" class="banner-img" />
    <div style="position: absolute; bottom: 40px; left: 30px; color: white; background: rgba(0, 60, 0, 0.6); padding: 20px 30px; border-radius: 18px; max-width: 70%;">
      <h2 style="margin:0 0 12px 0; font-weight:700; font-size:2rem;">Discover Inspiring Portfolios</h2>
      <button
        onclick="window.location.href='ExploreMore.html'" 
        style="background:#388e3c; border:none; color:white; padding:8px 22px; border-radius:30px; font-weight:600; font-size:0.95rem; cursor:pointer;">
        Explore Now
      </button>
    </div>
  </div>
  <div class="banner-right">
    <div style="position: relative;">
      <img src="/Portfolio-Project/dynamic-portfolio/client/public/uploads/image 3.png" alt="Right Top Banner" class="banner-img" />
      <div style="position: absolute; bottom: 20px; left: 16px; color: white; background: rgba(0, 60, 0, 0.5); padding: 14px 20px; border-radius: 14px; max-width: 90%;">
        <h3 style="margin:0 0 8px 0; font-weight:600; font-size:1.4rem;">Students & Creators</h3>
       
      <button
        onclick="window.location.href='viewprofile.php'" 
        style="background:#388e3c; border:none; color:white; padding:8px 22px; border-radius:30px; font-weight:600; font-size:0.95rem; cursor:pointer;">
        View Profiles
      </button>
      </div>
    </div>
    <div style="position: relative;">
      <img src="/Portfolio-Project/dynamic-portfolio/client/public/uploads/image5.png" alt="Right Bottom Banner" class="banner-img" />
      <div style="position: absolute; bottom: 20px; left: 16px; color: white; background: rgba(0, 60, 0, 0.5); padding: 14px 20px; border-radius: 14px; max-width: 90%;">
        <h3 style="margin:0 0 8px 0; font-weight:600; font-size:1.4rem;">Professionals & Innovators</h3>
        <button
        onclick="window.location.href='about.html'" 
        style="background:#388e3c; border:none; color:white; padding:8px 22px; border-radius:30px; font-weight:600; font-size:0.95rem; cursor:pointer;">
        Discover More
      </button>
      </div>
    </div>
  </div>
</section>

<section class="portfolio-info" style="max-width:1500px; margin:0 auto 60px; padding:0 20px; display:flex; gap:40px; flex-wrap:wrap; align-items:center;">
  <div style="flex:1 1 400px; min-width:320px;">
    <img src="/Portfolio-Project/dynamic-portfolio/client/public/uploads/image6.webp" alt="Portfolio Creation" style="width:100%; border-radius:18px; box-shadow:0 12px 35px rgba(76,175,80,0.3);" />
  </div>
  <div style="flex:1 1 400px; min-width:320px; color:#1a3b1a;">
    <h2 style="font-weight:700; font-size:2.4rem; margin-bottom:16px;">Create Your Own Portfolio with Ease</h2>
    <p style="font-size:1.2rem; line-height:1.5; margin-bottom:24px;">Showcase your skills, projects, and experiences with our easy-to-use portfolio builder. Whether you're a student, professional, or creative, our platform helps you stand out and make a lasting impression.</p>
  </div>
</section>

<section class="hero">
  <h1>Portfolios</h1>
  <p>Explore portfolios.</p>

  <!-- 🔎 Search Box -->
  <div class="search-box">
    <form method="get" action="../php/search.php">
      <input type="text" name="query" placeholder="Search portfolios by title or description..." required>
      <button type="submit">Search</button>
    </form>
  </div>
</section>

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

<section class="hero">
  <div class="hero-content">
    <h1>Create Your Portfolio</h1>
    <p>Showcase your projects, skills, and achievements to the world. Start building your professional portfolio today!</p>
    <a href="./../php/login.php" class="btn-create-portfolio">Create Portfolio</a>
  </div>
</section>

<section class="feedback-section" style="max-width:800px; margin:60px auto; padding:30px; background:#f0fbf5; border-radius:15px; box-shadow:0 8px 20px rgba(0,0,0,0.1);">
  <h2 style="text-align:center; margin-bottom:30px; color:#2c3e50;">User Feedback</h2>

  <?php if (!empty($feedbacks)): ?>
    <?php foreach ($feedbacks as $fb): ?>
      <div style="margin-bottom:20px; padding:20px; border-left:5px solid #4caf50; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
        <strong><?php echo htmlspecialchars($fb['name']); ?></strong> 
        <span style="color:#888; font-size:0.9rem;">(<?php echo date('d M Y', strtotime($fb['created_at'])); ?>)</span>
        <p style="margin:5px 0;"><em><?php echo htmlspecialchars($fb['subject']); ?></em></p>
        <p><?php echo nl2br(htmlspecialchars($fb['message'])); ?></p>
        <p>Rating: 
          <?php
          for ($i=0; $i < $fb['rating']; $i++) {
              echo "⭐"; // show stars
          }
          ?>
        </p>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p style="text-align:center; color:#555;">No feedback submitted yet.</p>
  <?php endif; ?>
</section>

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

        // --- Show QR + update button ---
        qrContainer.style.display = 'block';
        button.textContent = "Hide QR + Share";

      } else {
        // --- Hide QR ---
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
