<?php
// ============================================================
//  config.php  —  Veritabanı bağlantısı & genel ayarlar
// ============================================================
//  CANLIYA ALIRKEN bu değerleri hosting bilgilerinizle güncelleyin!
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'hobi_kulubu');
define('DB_USER', 'root');      // hosting'de size verilen kullanıcı adı
define('DB_PASS', '');          // hosting'de size verilen şifre
define('DB_CHARSET', 'utf8mb4');

// Oturum başlat (her sayfada config.php dahil edileceği için buraya koyuyoruz)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PDO bağlantısı
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Hassas hata mesajını kullanıcıya gösterme
            die('<div style="font-family:sans-serif;color:red;padding:2rem;">
                    <strong>Veritabanı bağlantısı kurulamadı.</strong><br>
                    Lütfen <code>config.php</code> dosyasındaki bağlantı bilgilerini kontrol edin.
                 </div>');
        }
    }
    return $pdo;
}

// Yardımcı: oturum kontrolü — giriş yapılmamışsa login'e yönlendir
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Yardımcı: giriş yapılmışsa dashboard'a yönlendir
function redirectIfLoggedIn(): void {
    if (!empty($_SESSION['user_id'])) {
        header('Location: dashboard.php');
        exit;
    }
}

// Yardımcı: XSS'e karşı çıktıyı temizle
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
