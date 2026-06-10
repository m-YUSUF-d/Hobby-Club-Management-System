## Prompt 1
**Prompt:**
XAMPP MySQL "shutdown unexpectedly" hatası alıyorum, nasıl çözebilirim?

**Cevap:**
Bu hata genellikle MySQL port çakışması (3306), bozuk InnoDB dosyaları veya başka bir MySQL servisinin çalışmasından kaynaklanır. Çözüm için:
- XAMPP Control Panel'den MySQL portunu kontrol et
- Windows Services'te başka MySQL servisleri varsa durdur
- `xampp/mysql/data` içindeki logları kontrol et
- Gerekirse `backup` klasöründen data kurtarma yapılır

---

## Prompt 2
**Prompt:**
PHP projemi XAMPP'a attım ama "Not Found" hatası alıyorum

**Cevap:**
Bu hata genellikle dosya yolunun yanlış olmasından kaynaklanır. Proje mutlaka `htdocs` içinde olmalı ve URL doğru yazılmalıdır:
Örnek:
`http://localhost/proje_adi/index.php`

---

## Prompt 3
**Prompt:**
PHP login yaptıktan sonra dashboard'a yönlendirme çalışmıyor

**Cevap:**
Sorun genellikle yanlış `header()` path kullanımından olur. Relative path yerine absolute path kullanılmalıdır:
Örnek:
`header('Location: /proje_adi/clubs/dashboard.php');`

---

## Prompt 4
**Prompt:**
PHP session logout yaptıktan sonra tekrar login sayfasına dönmüyor

**Cevap:**
Session düzgün temizlenmelidir:
- `session_start()` kullanılmalı
- `$_SESSION = []` yapılmalı
- `session_destroy()` çağrılmalı
- Redirect absolute path ile yapılmalı

---

## Prompt 5
**Prompt:**
Proje yapısında auth ve clubs klasörleri kullanmak doğru mu?

**Cevap:**
Evet, bu doğru bir yapıdır.:
- `auth/` → login, register, logout
- `clubs/` → dashboard ve CRUD işlemleri
- `config.php` → merkezi yapı

---