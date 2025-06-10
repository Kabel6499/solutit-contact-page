<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
include 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    $stmt = $conn->prepare("SELECT id, passwort FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['passwort'])) {
            $_SESSION['user_id'] = $row['id'];
            header("Location: stats.php");
            exit;
        }
    }
    $error = "Login fehlgeschlagen. Bitte überprüfe Benutzername und Passwort.";
}
?>

<!DOCTYPE html>
<html lang="de" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Login | solutiT Messe-Statistik</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Favicon für Browser-Tab -->
    <link rel="icon" type="image/png" href="https://solutit.de/wp-content/uploads/2022/09/cropped-favicon-solutit-32x32.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #212529 !important;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 400px;
            margin: auto;
            background: #23272b;
            border-radius: 12px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.12);
            padding: 2.5rem 2rem 2rem 2rem;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .favicon-circle {
            width: 80px;
            height: 80px;
            display: inline-block;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
        }
        .favicon-circle img {
            width: 48px;
            height: 48px;
            display: block;
        }
        .login-title {
            text-align: center;
            font-weight: bold;
            font-size: 1.4rem;
            margin-bottom: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-container">
            <div class="favicon-circle">
                <img src="https://solutit.de/wp-content/uploads/2022/09/cropped-favicon-solutit-32x32.png" alt="solutiT Favicon">
            </div>
        </div>
        <div class="login-title">solutiT Messe-Statistik Login</div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label for="username" class="form-label">Benutzername</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Passwort</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</body>
</html>
