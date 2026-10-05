<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

// Check required GET parameters
$portfolioId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$plan = isset($_GET['plan']) ? $_GET['plan'] : '';

if ($portfolioId <= 0 || !in_array($plan, ['basic','pro'])) {
    die("Invalid request!");
}

// Set Stripe Secret API key (server-side)
\Stripe\Stripe::setApiKey('sk_test_51S2YzEDbPBjGTv6hpNcDzFsVc0RDmTmWYmcTjxnGiocm7YJU8EvIqiwpw74k2g0SS4z8H0dqXpdS871YKehqUsUl00Z1V26yHR');

// Set amount based on plan
$amount = $plan === 'basic' ? 500 : 1000; // cents: $5 or $10

try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount,
        'currency' => 'usd',
        'payment_method_types' => ['card'],
        'metadata' => [
            'portfolio_id' => $portfolioId,
            'plan' => $plan
        ]
    ]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    die("Error creating payment: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pay for Portfolio</title>
<script src="https://js.stripe.com/v3/"></script>
<style>
    #card-element { padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px; }
    #submit { padding: 10px 20px; background-color: #6772e5; color: white; border: none; border-radius: 5px; cursor: pointer; }
    #submit:hover { background-color: #5469d4; }
    #payment-message { margin-top: 15px; color: green; font-weight: bold; }
    #download-btn { display: none; margin-top: 20px; padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; }
    #download-btn:hover { background-color: #218838; }
</style>
</head>
<body>
<h2>Complete Payment for Portfolio #<?php echo $portfolioId; ?></h2>
<p>Plan: <?php echo ucfirst($plan); ?> - $<?php echo $amount / 100; ?></p>

<!-- Stripe Payment Form -->
<form id="payment-form">
  <div id="card-element"></div>
  <button id="submit">Pay Now</button>
  <p id="payment-message"></p>
</form>

<!-- Download PDF button (hidden initially) -->
<button id="download-btn" onclick="downloadPDF()">Download PDF</button>

<script>
// Stripe publishable key (client-side)
const stripe = Stripe('pk_test_51S2YzEDbPBjGTv6homON3JFlLLHczwxzszKgQcTSCx5ATJkbGFlLj13Eu6Aya8Kj5YoAHiBqHmudRTcHdcoOeQsK00ZQJrSyHv');

const elements = stripe.elements();
const card = elements.create('card');
card.mount('#card-element');

const form = document.getElementById('payment-form');
const paymentIntentClientSecret = "<?php echo $paymentIntent->client_secret; ?>";

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const { error, paymentIntent } = await stripe.confirmCardPayment(paymentIntentClientSecret, {
        payment_method: { card }
    });

    const messageEl = document.getElementById('payment-message');
    const downloadBtn = document.getElementById('download-btn');

    if (error) {
        messageEl.textContent = error.message;
        messageEl.style.color = 'red';
        downloadBtn.style.display = 'none';
    } else if (paymentIntent.status === 'succeeded') {
        messageEl.textContent = 'Payment successful! Thank you.';
        messageEl.style.color = 'green';
        downloadBtn.style.display = 'inline-block'; // show button
    }
});

// Function to download PDF
function downloadPDF() {
    window.location.href = '/Portfolio-Project/dynamic-portfolio/server/api/generate_pdf.php?data=HelloWorld';
}

</script>
</body>
</html>

