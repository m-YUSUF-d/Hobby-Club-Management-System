<?php
require_once 'config.php';
requireLogin();

$pdo = getDB();

// Arama filtresi
$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');

$where  = 'WHERE c.user_id = ?';
$params = [$_SESSION['user_id']];

if ($search !== '') {
    $where   .= ' AND (c.name LIKE ? OR c.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category !== '') {
    $where   .= ' AND c.category = ?';
    $params[] = $category;
}

// Kulüpleri getir
$stmt = $pdo->prepare("
    SELECT * FROM clubs c
    $where
    ORDER BY c.created_at DESC
");
$stmt->execute($params);
$clubs = $stmt->fetchAll();

// İstatistikler
$statsStmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total_clubs,
        COALESCE(SUM(member_count), 0) AS total_members,
        COUNT(DISTINCT category) AS total_categories
    FROM clubs
    WHERE user_id = ?
");
$statsStmt->execute([$_SESSION['user_id']]);
$stats = $statsStmt->fetch();

// Kategori listesi (filtre için)
$catStmt = $pdo->prepare("SELECT DISTINCT category FROM clubs WHERE user_id = ? ORDER BY category");
$catStmt->execute([$_SESSION['user_id']]);
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

// Bildirim mesajı (ekleme/düzenleme/silme sonrası)
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Hobi Kulübü</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f0f2f5; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
        .stat-card { border: none; border-radius: .75rem; box-shadow: 0 2px 12px rgba(0,0,0,.08); transition: transform .2s; }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-icon { width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .club-card { border: none; border-radius: .75rem; box-shadow: 0 2px 12px rgba(0,0,0,.07); transition: transform .2s, box-shadow .2s; }
        .club-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.12); }
        .badge-category { font-size: .75rem; }
        .empty-state { background: white; border-radius: .75rem; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-dark navbar-expand-lg mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-people-fill me-2"></i>Hobi Kulübü
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                <li class="nav-item">
                    <a class="btn btn-light btn-sm fw-semibold" href="club_add.php">
                        <i class="bi bi-plus-circle me-1"></i>Yeni Kulüp
                    </a>
                </li>
                <li class="nav-item">
                    <span class="navbar-text text-white opacity-75 me-2">
                        <i class="bi bi-person-circle me-1"></i><?= e($_SESSION['username']) ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm" href="logout.php"
                       onclick="return confirm('Çıkış yapmak istediğinize emin misiniz?')">
                        <i class="bi bi-box-arrow-right me-1"></i>Çıkış
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container pb-5">

    <!-- Flash mesaj -->
    <?php if ($flash): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= e($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- İstatistik Kartları -->
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-collection"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold"><?= $stats['total_clubs'] ?></div>
                        <div class="text-muted small">Toplam Kulüp</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold"><?= number_format($stats['total_members']) ?></div>
                        <div class="text-muted small">Toplam Üye</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-tags"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold"><?= $stats['total_categories'] ?></div>
                        <div class="text-muted small">Kategori</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Arama & Filtre -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1">Kulüp Ara</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="İsim veya açıklamada ara..."
                               value="<?= e($search) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Kategori</label>
                    <select name="category" class="form-select">
                        <option value="">Tüm Kategoriler</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                <?= e($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i>Filtrele
                    </button>
                    <?php if ($search || $category): ?>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Kulüp Listesi Başlığı -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-list-ul me-2 text-primary"></i>Kulüplerim
            <span class="badge bg-primary ms-2"><?= count($clubs) ?></span>
        </h5>
        <a href="club_add.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Yeni Kulüp Ekle
        </a>
    </div>

    <!-- Kulüp Kartları -->
    <?php if (empty($clubs)): ?>
        <div class="empty-state text-center py-5 px-3">
            <i class="bi bi-collection fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">Henüz kulüp eklenmemiş</h5>
            <p class="text-muted mb-4">
                <?= ($search || $category) ? 'Arama kriterlerine uygun kulüp bulunamadı.' : 'İlk kulübünüzü eklemek için aşağıdaki butona tıklayın.' ?>
            </p>
            <?php if (!$search && !$category): ?>
                <a href="club_add.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>İlk Kulübümü Ekle
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($clubs as $club): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card club-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title fw-bold mb-0"><?= e($club['name']) ?></h6>
                                <span class="badge bg-primary bg-opacity-10 text-primary badge-category">
                                    <?= e($club['category']) ?>
                                </span>
                            </div>
                            <?php if ($club['description']): ?>
                                <p class="card-text text-muted small mb-3" style="min-height:40px">
                                    <?= e(mb_strimwidth($club['description'], 0, 100, '...')) ?>
                                </p>
                            <?php endif; ?>
                            <div class="d-flex gap-3 text-muted small mb-3">
                                <span><i class="bi bi-people me-1"></i><?= $club['member_count'] ?> üye</span>
                                <?php if ($club['founded_date']): ?>
                                    <span><i class="bi bi-calendar me-1"></i>
                                        <?= date('d.m.Y', strtotime($club['founded_date'])) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0 pb-3 px-4">
                            <div class="d-flex gap-2">
                                <a href="club_edit.php?id=<?= $club['id'] ?>"
                                   class="btn btn-outline-warning btn-sm flex-fill">
                                    <i class="bi bi-pencil me-1"></i>Düzenle
                                </a>
                                <a href="club_delete.php?id=<?= $club['id'] ?>"
                                   class="btn btn-outline-danger btn-sm flex-fill"
                                   onclick="return confirm('Bu kulübü silmek istediğinize emin misiniz?')">
                                    <i class="bi bi-trash me-1"></i>Sil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
