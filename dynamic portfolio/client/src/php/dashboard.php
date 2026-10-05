<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
$userId = $_SESSION['id']; // Ensure user ID is stored in session
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Portfolio System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Icons -->
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
          transition: transform 0.2s ease-in-out, box-shadow 0.2s;
          height: 100%;
      }
      .card-custom:hover {
          transform: scale(1.05);
          box-shadow: 0 6px 20px rgba(0,0,0,0.2);
      }
      .card-custom h5 {
          font-weight: 600;
          color: #007bff;
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
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-2 d-none d-md-block sidebar">
      <div class="text-center mb-4">
          <h4>Portfolio</h4>
          <p class="small">Welcome, <?php echo htmlspecialchars($username); ?>!</p>
      </div>
      <ul class="nav flex-column">
          <li><a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
          <li><a href="profile.php" class="active"><i class="bi bi-person-circle"></i> Profile</a></li>
          <li><a href="showcaseProfile.php"><i class="bi bi-star"></i> Showcase</a></li>
          <li><a href="portfolioform.php"><i class="bi bi-folder-plus"></i> Create Portfolio</a></li>
          <li><a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </nav>

    <!-- Main content -->
    <main class="col-md-10 ms-sm-auto col-lg-10 content">
      <!-- Top Navbar -->
      <nav class="navbar navbar-expand-lg mb-4 px-3">
        <div class="container-fluid">
          <a class="navbar-brand" href="#">Dashboard</a>
          <div class="d-flex">
            <span class="me-3">👤 <?php echo htmlspecialchars($username); ?></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
          </div>
        </div>
      </nav>

      <!-- Dashboard Cards -->
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card card-custom p-3">
            <h5><i class="bi bi-person-lines-fill"></i> Profile</h5>
            <p>View or update your personal profile details.</p>
            <a href="profile.php" class="btn btn-primary">Go to Profile</a>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-custom p-3">
            <h5><i class="bi bi-folder-plus"></i> Create Portfolio</h5>
            <p>Build a new portfolio with your story, projects, and education.</p>
            <a href="portfolioform.php" class="btn btn-success">Create Now</a>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-custom p-3">
            <h5><i class="bi bi-journal-bookmark"></i> My Portfolios</h5>
            <p>Check, edit, and manage your saved portfolios.</p>
            <a href="viewportfolio.php" class="btn btn-warning">View Portfolios</a>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-custom p-3">
            <h5><i class="bi bi-cloud-arrow-down"></i> Upgrade & Download</h5>
            <p>Upgrade your portfolio and download it as a PDF.</p>
            <a href="../pages/paymentPlan.php?id=<?php echo $userId; ?>" class="btn btn-info">Upgrade</a>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-custom p-3">
            <h5><i class="bi bi-chat-left-text"></i> Feedback</h5>
            <p>Give us your feedback to improve our system.</p>
            <a href="Userfeedback.php" class="btn btn-dark">Give Feedback</a>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
