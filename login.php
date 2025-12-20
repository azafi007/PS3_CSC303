<?php
session_start();
require_once 'config.php';

// If already logged in, go straight to dashboard
if (isset($_SESSION['username'])) {
    header('Location: dashboard_system.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if ($user === '' || $pass === '') {
        $error = 'Please enter both username and password.';
    } else {
        // SIMPLE DEMO: hard-coded login
        // username: admin, password: 1234
        if ($user === 'admin' && $pass === '1234') {
            $_SESSION['username'] = 'admin';
            $_SESSION['staff_id'] = 0; // demo id
            header('Location: dashboard_system.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FIS Admin – Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      margin: 0;
      background: radial-gradient(circle at top left, #ff8a80, #ffb74d 20%, #42a5f5 55%, #7e57c2 90%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }
    .login-card {
      width: 100%;
      max-width: 420px;
      background: rgba(255,255,255,0.96);
      border-radius: 18px;
      box-shadow: 0 18px 45px rgba(0,0,0,0.22);
      overflow: hidden;
    }
    .login-header {
      background: linear-gradient(90deg, #0d6efd, #6610f2);
      color: #fff;
      padding: 24px 24px 40px;
      position: relative;
      text-align: center;
    }
    .login-logo {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: radial-gradient(circle at 30% 20%, #fff, #90caf9);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 30px;
      font-weight: 700;
      color: #0d47a1;
      border: 3px solid rgba(255,255,255,0.85);
      position: absolute;
      left: 50%;
      bottom: -36px;
      transform: translateX(-50%);
      box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    }
    .login-body { padding: 48px 28px 26px; }
    .form-control {
      border-radius: 999px;
      padding-left: 16px;
      padding-right: 16px;
    }
    .btn-login {
      border-radius: 999px;
      background: linear-gradient(90deg, #0d6efd, #6610f2);
      border: none;
      font-weight: 600;
      letter-spacing: 0.03em;
    }
    .btn-login:hover {
      background: linear-gradient(90deg, #0b5ed7, #5a1bd8);
    }
    .text-small { font-size: 0.86rem; }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-header">
      <h4 class="mb-1">FIS Admin</h4>
      <p class="mb-0 text-light text-small">Secure access to Financial Institution System</p>
      <div class="login-logo">FI</div>
    </div>
    <div class="login-body">
      <?php if ($error): ?>
        <div class="alert alert-danger py-2 mb-3">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="post" autocomplete="off">
        <div class="mb-3">
          <label class="form-label text-small">Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label text-small">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-login w-100 text-white mb-2">
          Sign in
        </button>
      </form>

      <p class="text-center text-muted text-small mt-2 mb-0">
        Log in using a staff username and password.
      </p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
