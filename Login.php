<?php session_start(); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <?php if (isset($_SESSION['error'])): ?>
        <p style="color:red;"><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="check_login.php" method="post">
        <div>
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
</body>
</html>

