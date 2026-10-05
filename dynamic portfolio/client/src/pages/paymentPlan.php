<?php 
$portfolioId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($portfolioId <= 0) {
    echo "Invalid portfolio ID!";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Plan</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    body {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #f093fb, #f5576c);
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        color: #fff;
    }

    .container {
        max-width: 1000px;
        margin: 80px auto;
        text-align: center;
        padding: 0 20px;
    }

    h2 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 10px;
        text-shadow: 2px 2px 10px rgba(0,0,0,0.3);
    }

    p.text-center {
        font-size: 1.2rem;
        margin-bottom: 60px;
        text-shadow: 1px 1px 5px rgba(0,0,0,0.2);
    }

    .plans {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 40px;
    }

    .card {
        background: #fff;
        color: #333;
        border-radius: 25px;
        width: 300px;
        padding: 50px 30px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        transition: transform 0.5s, box-shadow 0.5s;
        position: relative;
        overflow: hidden;
    }

    .card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(120deg, #ff6b6b, #fcb045, #ff6b6b, #fcb045);
        animation: rotate 6s linear infinite;
        z-index: 0;
        opacity: 0.2;
    }

    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .card h3 {
        font-size: 2rem;
        margin-bottom: 15px;
        color: #ff6b6b;
        position: relative;
        z-index: 1;
    }

    .card p {
        font-size: 1.1rem;
        margin-bottom: 30px;
        font-weight: 500;
        position: relative;
        z-index: 1;
    }

    .btn-primary {
        display: inline-block;
        text-decoration: none;
        padding: 15px 30px;
        border-radius: 50px;
        background: linear-gradient(135deg, #ff6b6b, #fcb045);
        color: #fff;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.4s ease;
        position: relative;
        z-index: 1;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #ff4b4b, #ff9f43);
        transform: scale(1.1);
        box-shadow: 0 15px 25px rgba(0,0,0,0.3);
    }

    .ribbon {
        position: absolute;
        top: -10px;
        right: -10px;
        background: #ff9f43;
        color: #fff;
        padding: 10px 25px;
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 5px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        transform: rotate(15deg);
        z-index: 1;
    }

    .card:hover {
        transform: scale(1.08);
        box-shadow: 0 35px 60px rgba(0,0,0,0.3);
    }

    @media(max-width: 768px) {
        .plans { flex-direction: column; align-items: center; }
        .card { width: 80%; }
    }
</style>
</head>
<body>
<div class="container">
    <h2>Choose Your Plan</h2>
    <p class="text-center">Select a plan and unlock your portfolio instantly!</p>

    <div class="plans">
        <div class="card">
            <h3>Basic Plan</h3>
            <p>$5 - One Portfolio</p>
            <a href="payment.php?id=<?php echo $portfolioId; ?>&plan=basic" class="btn-primary">Proceed to Pay</a>
        </div>

        <div class="card">
            <div class="ribbon">Best Value</div>
            <h3>Pro Plan</h3>
            <p>$10 - Multiple Portfolios</p>
            <a href="payment.php?id=<?php echo $portfolioId; ?>&plan=pro" class="btn-primary">Proceed to Pay</a>
        </div>
    </div>
</div>
</body>
</html>
