<?php
require_once '../auth/config.php';

// Ana sayfa: giriş yapılmışsa dashboard, yoksa login'e yönlendir
if (!empty($_SESSION['user_id'])) {
    header('Location: /Hobby-Club-Management-System/clubs/dashboard.php');
} else {
    header('Location: /Hobby-Club-Management-System/auth/login.php');
}
exit;
?>
