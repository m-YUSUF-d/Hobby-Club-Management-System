<?php
require_once 'config.php';
requireLogin();

$pdo = getDB();
$id  = (int)($_GET['id'] ?? 0);

// Sadece bu kullanıcıya ait kulübü sil
$stmt = $pdo->prepare('SELECT name FROM clubs WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
$club = $stmt->fetch();

if ($club) {
    $del = $pdo->prepare('DELETE FROM clubs WHERE id = ? AND user_id = ?');
    $del->execute([$id, $_SESSION['user_id']]);
    $_SESSION['flash'] = "\"{$club['name']}\" kulübü başarıyla silindi.";
} else {
    $_SESSION['flash'] = 'Kulüp bulunamadı veya bu işlem için yetkiniz yok.';
}

header('Location: dashboard.php');
exit;
?>
