<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'ReliaWork2', ENT_QUOTES, 'UTF-8') ?> — ReliaWork2</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e2a3a 0%, #2d4a6e 50%, #1a3a5c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .auth-wrapper {
            width: 100%;
            max-width: 460px;
            padding: 1rem;
        }
        .auth-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            overflow: hidden;
        }
        .auth-header {
            background: linear-gradient(135deg, #1e2a3a, #2d4a6e);
            color: #fff;
            padding: 2rem;
            text-align: center;
        }
        .auth-header .brand-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .auth-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .auth-header p {
            font-size: 0.85rem;
            opacity: 0.75;
            margin: 0.25rem 0 0;
        }
        .auth-body {
            padding: 2rem;
            background: #fff;
        }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card card">
        <div class="auth-header">
            <div class="brand-icon"><i class="bi bi-briefcase-fill"></i></div>
            <h1>ReliaWork2</h1>
            <p>Job Fair Management System</p>
        </div>
        <div class="auth-body">
            <?php renderFlashMessages(); ?>
            <?= $content ?? '' ?>
        </div>
    </div>
    <p class="text-center text-white-50 mt-3 small">&copy; <?= date('Y') ?> ReliaWork2. All rights reserved.</p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
</body>
</html>
