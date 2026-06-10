<?php
require_once 'config.php';
redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password']      ?? '';

    if ($username === '' || $password === '') {
        $error = 'Kullanıcı adı ve şifre boş bırakılamaz.';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Oturum sabitleme saldırısına karşı ID yenile
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Kullanıcı adı veya şifre hatalı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap — Hobi Kulübü</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .card { border: none; border-radius: 1rem; box-shadow: 0 10px 40px rgba(0,0,0,.2); }
        .card-header { background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 1rem 1rem 0 0 !important; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-header text-white text-center py-4">
                    <i class="bi bi-people-fill fs-1"></i>
                    <h3 class="mb-0 mt-2">Hobi Kulübü</h3>
                    <p class="mb-0 opacity-75">Hesabına Giriş Yap</p>
                </div>
                <div class="card-body p-4">

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($_GET['registered'])): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>Kayıt başarılı! Giriş yapabilirsiniz.
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kullanıcı Adı</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="username" class="form-control"
                                       value="<?= e($_POST['username'] ?? '') ?>"
                                       placeholder="Kullanıcı adınız" required autofocus>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Şifre</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control"
                                       placeholder="Şifreniz" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Giriş Yap
                        </button>
                    </form>

                </div>
                <div class="card-footer text-center bg-white border-0 pb-4">
                    Hesabın yok mu?
                    <a href="register.php" class="text-decoration-none fw-semibold">Kayıt Ol</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
