<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg,rgb(91, 209, 95), #4caf50);
            display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;
            position: relative;
        }
        .container {
            width: 380px; padding: 3rem; border-radius: 15px;
            background-color: rgba(255, 255, 255, 0.9); 
            box-shadow: 0 6px 12px rgba(0,0,0,0.3); 
            text-align: center;
            position: relative;
            z-index: 1;
        }
        h2 { font-size: 3rem; color: #4caf50; margin-bottom: 2rem; }
        .form-group { margin-bottom: 1rem; text-align: left; }
        .form-group label { font-weight: bold; margin-bottom: 0.5rem; display: block; }
        .form-group input {
            width: 100%; padding: 1rem; margin: 1rem 0; font-size: 1.25rem;
            border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;
        }
        .form-group input:focus { border-color: #4caf50; outline: none; box-shadow: 0 0 5px rgba(106,27,154,0.5); }
        .form-group button {
            width: 100%; padding: 1rem; font-size: 1.5rem; border: none; border-radius: 30px;
            background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);
            color: white; cursor: pointer; transition: all 0.3s ease;
        }
        .form-group button:hover { box-shadow: 0 0 30px rgba(0,0,0,0.5); transform: scale(1.05); }
        .link { text-align: center; margin-top: 1rem; }
        .link a { color: #6a1b9a; text-decoration: none; font-weight: bold; }
        .link a:hover { color: #4a148c; text-decoration: underline; }

        /* Home button styles */
        .home-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4caf50, #81c784);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-weight: bold;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            z-index: 2;
        }
        .home-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 15px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body>
    <!-- Home button -->
    <a href="../pages/home.php" class="home-btn">🏠</a>

    <div class="container">
        <h2>Login</h2>
        <form action="userLogin.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Log In</button>
            </div>
        </form>
        <div class="link">
            <a href="userRegister.php">Don't have an account? Create account</a>
        </div>
    </div>
</body>
</html>
