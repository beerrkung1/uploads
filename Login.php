<?php session_start(); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <!-- Viewport meta สำหรับ Mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 1em;
            background-color: #f0f0f0;
        }

        form {
            background: #fff;
            padding: 1em;
            border-radius: 8px;
            max-width: 300px;
            margin: 2em auto;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin-bottom: 0.5em;
            font-size: 1rem;
        }

        input[type="text"], input[type="password"] {
            width: 80%;
            padding: 0.75em;
            margin-bottom: 1em;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }

        button {
            width: 100%;
            padding: 0.75em;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
        }

        button:hover {
            background: #0056b3;
        }

        p.error {
            text-align: center;
            color: red;
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error"><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="check_login.php" method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required autocomplete="username">

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required autocomplete="current-password">

        <button type="submit">Login</button>
    </form>
</body>
</html>
