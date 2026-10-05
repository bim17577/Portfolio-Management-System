<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg,rgb(91, 209, 95), #4caf50);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            position: relative; /* Needed for absolute positioning of button */
        }

        /* Circular Home Button */
        .home-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background-color: #4caf50;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            font-weight: bold;
            font-size: 1rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }
        .home-btn:hover {
            background-color: #1e88e5;
            transform: scale(1.1);
        }

        .container {
            width: 460px;
            padding: 20px;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        h2 { font-size: 2rem; color: #4caf50; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; text-align: left; }
        .form-group label { font-weight: bold; margin-bottom: 0.5rem; display: block; }
        .form-group input {
            width: 100%; padding: 0.8rem; font-size: 1rem;
            border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;
        }
        .form-group input:focus { border-color: #4caf50; outline: none; box-shadow: 0 0 5px rgba(33,150,243,0.5); }
        .form-group button {
            width: 100%; padding: 0.75rem; border: none; border-radius: 30px;
            background-color: #4caf50; color: white; cursor: pointer;
            font-size: 1.25rem; transition: all 0.3s ease;
        }
        .form-group button:hover { background-color: #1e88e5; transform: scale(1.05); }
        .link { text-align: center; margin-top: 0.5rem; }
        .link a { color: #2196f3; text-decoration: none; font-weight: bold; }
        .link a:hover { color:rgb(30, 229, 83); text-decoration: underline; }
    </style>
</head>
<body>
    <!-- Home Button -->
    <a href="../pages/home.php" class="home-btn">🏠</a>

    <div class="container">
        <h2>Signup</h2>
        <form action="userRegistration.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Sign Up</button>
            </div>
        </form>
        <div class="link">
            <a href="login.php">Already have an account? Log in</a>
        </div>
    </div>
</body>
</html>
