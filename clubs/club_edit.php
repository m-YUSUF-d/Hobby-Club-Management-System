<?php
require_once 'config.php';
requireLogin();

$pdo = getDB();
$id  = (int)($_GET['id'] ?? 0);

// Kulübü getir — sadece bu kullanıcıya ait olanı
$stmt = $pdo->prepare('SELECT * FROM clubs WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
$club = $stmt->fetch();

if (!$club) {
    $_SESSION['flash'] = 'Kulüp bulunamadı veya bu işlem için yetkiniz yok.';
    header('Location: dashboard.php');
    exit;
}

$errors = [];

$categoryOptions = [
    'Spor', 'Müzik', 'Sanat', 'Teknoloji', 'Doğa & Outdoor',
    'Edebiyat', 'Yemek & Mutfak', 'Oyun & Hobi', 'Seyahat', 'Diğer'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name']         ?? '');
    $category     = trim($_POST['category']     ?? '');
    $description  = trim($_POST['description']  ?? '');
    $founded_date = trim($_POST['founded_date'] ?? '');
    $member_count = (int)($_POST['member_count'] ?? 0);

    // Doğrulama
    if ($name === '') {
        $errors[] = 'Kulüp adı boş bırakılamaz.';
    } elseif (strlen($name) > 100) {
        $errors[] = 'Kulüp adı en fazla 100 karakter olabilir.';
    }
    if ($category === '' || !in_array($category, $categoryOptions)) {
        $errors[] = 'Geçerli bir kategori seçin.';
    }
    if ($member_count < 0) {
        $errors[] = 'Üye sayısı negatif olamaz.';
    }
    if ($founded_date !== '' && !strtotime($founded_date)) {
        $errors[] = 'Geçerli bir kuruluş tarihi girin.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE clubs
            SET name = ?, category = ?, description = ?, founded_date = ?, member_count = ?
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([
            $name,
            $category,
            $description,
            $founded_date ?: null,
            $member_count,
            $id,
            $_SESSION['user_id']
        ]);
        $_SESSION['flash'] = "\"$name\" kulübü başarıyla güncellendi!";
        header('Location: dashboard.php');
        exit;
    }

    // Hata varsa form değerlerini koru
    $club['name']         = $name;
    $club['category']     = $category;
    $club['description']  = $description;
    $club['founded_date'] = $founded_date;
    $club['member_count'] = $member_count;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kulüp Düzenle — Hobi Kulübü</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f0f2f5; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
        .form-card { border: none; border-radius: 1rem; box-shadow: 0 4px 20px rgba(0,0,0,.1); }
        .form-card .card-header { background: linear-gradient(135deg, #f6c90e, #f0932b); border-radius: 1rem 1rem 0 0 !important; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-dark navbar-expand-lg mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-people-fill me-2"></i>Hobi Kulübü
        </a>
        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="text-white opacity-75 small">
                <i class="bi bi-person-circle me-1"></i><?= e($_SESSION['username']) ?>
            </span>
            <a class="btn btn-outline-light btn-sm" href="logout.php"
               onclick="return confirm('Çıkış yapmak istediğinize emin misiniz?')">
                <i class="bi bi-box-arrow-right me-1"></i>Çıkış
            </a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kulüp Düzenle</li>
                </ol>
            </nav>

            <div class="card form-card">
                <div class="card-header text-white py-3 px-4">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Kulüp Düzenle</h5>
                </div>
                <div class="card-body p-4">

                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= e($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kulüp Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= e($club['name']) ?>"
                                   maxlength="100" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="">— Kategori Seçin —</option>
                                <?php foreach ($categoryOptions as $opt): ?>
                                    <option value="<?= e($opt) ?>"
                                        <?= ($club['category'] === $opt) ? 'selected' : '' ?>>
                                        <?= e($opt) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Açıklama</label>
                            <textarea name="description" class="form-control" rows="4"><?= e($club['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Kuruluş Tarihi</label>
                                <input type="date" name="founded_date" class="form-control"
                                       value="<?= e($club['founded_date'] ?? '') ?>"
                                       max="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Üye Sayısı</label>
                                <input type="number" name="member_count" class="form-control"
                                       value="<?= e($club['member_count']) ?>" min="0">
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning flex-fill py-2 fw-semibold text-white">
                                <i class="bi bi-check-lg me-2"></i>Değişiklikleri Kaydet
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-lg me-1"></i>İptal
                            </a>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
