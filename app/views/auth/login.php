<?php
$base_path = defined('BASE_PATH') ? BASE_PATH : (getenv('BASE_PATH') !== false ? getenv('BASE_PATH') : ((getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) ? '' : '/infravision'));
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfraVision - Login</title>
    <link rel="icon" href="<?= $base_path ?>/assets/img/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #0b0f19;
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background-color: #151a27;
            border: 1px solid #1e2638;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
        }
        .form-control {
            background-color: #0b0f19;
            border: 1px solid #1e2638;
            color: #fff;
        }
        .form-control:focus {
            background-color: #0b0f19;
            color: #fff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        .brand-logo {
            font-size: 2rem;
            font-weight: 700;
            color: #3b82f6;
            text-align: center;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <img src="<?= $base_path ?>/assets/img/logo.png" alt="InfraVision" height="100" class="mb-3 rounded-circle shadow-lg">
        <h2 class="fw-bold">Infra<span class="text-primary">Vision</span></h2>
        <p class="text-secondary small">Monitoring & Intelligence System</p>
    </div>
    
    <?php if (isset($erro)): ?>
        <div class="alert alert-danger text-center" role="alert">
            <?= $erro ?>
        </div>
    <?php endif; ?>

    <form action="<?= $base_path ?>/login" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label text-light">E-mail</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email" value="admin@infravision.local" required>
            </div>
        </div>
        <div class="mb-4">
            <label for="senha" class="form-label text-light">Senha</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-lock"></i></span>
                <input type="password" class="form-control" id="senha" name="senha" value="admin123" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Entrar no Sistema</button>
    </form>
    
    <div class="text-center mt-4 text-secondary small">
        &copy; <?= date('Y') ?> InfraVision NOC
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
