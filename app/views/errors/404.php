<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found — ReliaWork2</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { background: linear-gradient(135deg, #1e2a3a, #2d4a6e); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { background: #fff; border-radius: 16px; padding: 3rem; text-align: center; max-width: 480px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .error-code { font-size: 6rem; font-weight: 900; color: #1e2a3a; line-height: 1; }
    </style>
</head>
<body>
<div class="error-card">
    <div class="error-code">404</div>
    <h3 class="fw-bold mt-2">Page Not Found</h3>
    <p class="text-muted">The page you're looking for doesn't exist or has been moved.</p>
    <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>/login" class="btn btn-primary mt-2">
        <i class="bi bi-house me-2"></i>Go Home
    </a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
