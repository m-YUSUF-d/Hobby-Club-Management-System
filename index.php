<?php
require_once 'config.php';

// Ana sayfa: giriş yapılmışsa dashboard, yoksa login'e yönlendir
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
?>
