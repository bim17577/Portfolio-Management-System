<?php
include("../php/dbConnection.php"); // adjust path as needed
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize feedback message
$feedbackMsg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $rating = intval($_POST['rating'] ?? 0);

    if ($name && $email && $subject && $message && $rating) {
        $stmt = $conn->prepare("INSERT INTO feedback (name, email, subject, message, rating) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $email, $subject, $message, $rating);
        if ($stmt->execute()) {
            $feedbackMsg = "✅ Feedback submitted successfully!";
        } else {
            $feedbackMsg = "❌ Error saving feedback: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $feedbackMsg = "⚠️ Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Feedback - Smart Portfolio</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #a8edea, #fed6e3);
    margin: 0;
    padding: 0;
}
.container { max-width: 700px; margin: 60px auto; }
.feedback-msg {
    text-align:center; 
    margin-bottom: 25px; 
    font-size: 1.2rem; 
    padding: 15px;
    border-radius: 12px;
    color: #fff;
    background: rgba(0,0,0,0.2);
    backdrop-filter: blur(8px);
    animation: fadeIn 1s ease-in-out;
}
.contact-section {
    padding: 35px;
    background: rgba(255,255,255,0.9);
    border-radius: 25px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    transition: transform 0.3s ease;
}
.contact-section:hover { transform: scale(1.02); }
.contact-section h2 { text-align:center; margin-bottom:30px; color:#333; font-weight:700; }
.contact-form label { display:block; margin:15px 0 5px; font-weight:600; color:#555; }
.contact-form input, .contact-form textarea, .contact-form select {
    width:100%; padding:14px; border:2px solid #4caf50; border-radius:12px; font-size:1rem; transition: all 0.3s ease;
}
.contact-form input:focus, .contact-form textarea:focus, .contact-form select:focus {
    border-color:#388e3c;
    box-shadow: 0 0 10px rgba(56,142,60,0.3);
    outline:none;
}
.contact-form textarea { min-height:140px; resize:vertical; }
.contact-form button {
    margin-top:25px; padding:14px 35px; font-size:1.1rem; font-weight:700;
    color:#fff; background: linear-gradient(90deg, #4caf50, #81c784);
    border:none; border-radius:50px; cursor:pointer; transition: all 0.3s ease;
}
.contact-form button:hover { 
    background: linear-gradient(90deg, #388e3c, #66bb6a); 
    transform: scale(1.05) rotate(-1deg); 
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

/* Animation */
@keyframes fadeIn {
    0% { opacity:0; transform: translateY(-10px);}
    100% { opacity:1; transform: translateY(0);}
}

/* Responsive */
@media(max-width:768px){
    .contact-section { padding:25px; }
}
</style>
</head>
<body>

<div class="container">
    <?php if($feedbackMsg): ?>
    <div class="feedback-msg"><?php echo $feedbackMsg; ?></div>
    <?php endif; ?>

    <section class="contact-section">
        <h2><i class="bi bi-chat-square-text-fill"></i> Send Your Feedback</h2>
        <form class="contact-form" action="" method="post">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Your Name" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Your Email" required>

            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" placeholder="Subject" required>

            <label for="message">Message</label>
            <textarea id="message" name="message" placeholder="Write your message..." required></textarea>

            <label for="rating">Rating</label>
            <select id="rating" name="rating" required>
                <option value="">Select your rating</option>
                <option value="5">Excellent</option>
                <option value="4">Very Good</option>
                <option value="3">Good</option>
                <option value="2">Fair</option>
                <option value="1">Poor</option>
            </select>

            <button type="submit"><i class="bi bi-send-fill"></i> Send Feedback</button>
        </form>
    </section>
</div>

</body>
</html>
