<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VK OAuth Demo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .error {
            color: red;
            padding: 10px;
            border: 1px solid red;
            margin-bottom: 20px;
        }
        .user-info {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ccc;
        }
        .user-photo {
            max-width: 200px;
            border-radius: 50%;
        }
        .login-button {
            display: inline-block;
            padding: 10px 20px;
            background: #4C75A3;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>VK OAuth Demo</h1>

    <?php if ($error): ?>
        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($user)): ?>
        <a href="/login.php" class="login-button">Войти через VK</a>
    <?php else: ?>
        <div class="user-info">
            <h2>Добро пожаловать, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
            

            <h3>Информация о пользователе:</h3>
            <ul>
                <?php foreach ($user as $key => $value): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($key); ?>:</strong>
                        <?php if (is_array($value)): ?>
                            <pre><?php echo htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                        <?php else: ?>
                            <?php echo htmlspecialchars((string)$value); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <a href="/logout.php" class="login-button">Выйти</a>
        </div>
    <?php endif; ?>
</body>
</html>
