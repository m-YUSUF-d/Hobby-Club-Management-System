<?php
// ============================================================
//  config.php  —  Veritabanı bağlantısı & genel ayarlar
// ============================================================
//  CANLIYA ALIRKEN bu değerleri hosting bilgilerinizle güncelleyin!
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'hobi_kulubu');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

session_start();

function getDB() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;

    } catch (PDOException $e) {
        die("Veritabanı bağlantısı kurulamadı: " . $e->getMessage());
    }
}

// Yardımcı: oturum kontrolü — giriş yapılmamışsa login'e yönlendir
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /Hobby-Club-Management-System/auth/login.php');
        exit;
    }
}

function redirectIfLoggedIn(): void {
    if (!empty($_SESSION['user_id'])) {
        header('Location: /Hobby-Club-Management-System/clubs/dashboard.php');
        exit;
    }
}

// Yardımcı: XSS'e karşı çıktıyı temizle
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
