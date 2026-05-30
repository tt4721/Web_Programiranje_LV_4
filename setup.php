<?php
/**
 * SETUP – pokreni jednom za kreiranje admin korisnika
 * Nakon pokretanja OBRIŠI ovu datoteku ili premjesti izvan web roota!
 *
 * URL: http://localhost/filmoteka/lv4/setup.php
 */
session_start();
require_once 'includes/db.php';

echo '<!DOCTYPE html><html lang="hr"><head><meta charset="UTF-8">
<title>Setup – Filmoteka</title>
<style>
body{font-family:sans-serif;background:#111;color:#eee;padding:40px;max-width:600px;margin:0 auto}
h1{color:#c9a84c}
.ok{color:#4caf50;background:#0a2a0a;padding:12px;border-radius:6px;margin:10px 0}
.err{color:#f44;background:#2a0a0a;padding:12px;border-radius:6px;margin:10px 0}
pre{background:#222;padding:12px;border-radius:6px;overflow-x:auto}
a{color:#c9a84c}
</style></head><body>';

echo '<h1>Filmoteka – Setup</h1>';

// ── Provjeri postoji li admin ─────────────────────────────────────────────────
$stmt = $pdo->query("SELECT COUNT(*) FROM korisnici WHERE uloga = 'administrator'");
$adminCount = (int)$stmt->fetchColumn();

if ($adminCount > 0) {
    echo '<div class="ok">✓ Admin korisnik već postoji. Setup nije potreban.</div>';
    echo '<p><a href="index.php">← Natrag na početnu</a></p>';
    echo '</body></html>';
    exit;
}

// ── Kreiraj admin ─────────────────────────────────────────────────────────────
$admin_ime  = 'admin';
$admin_mail = 'admin@filmoteka.hr';
$admin_pass = 'Admin123!';
$hash       = password_hash($admin_pass, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare(
        "INSERT INTO korisnici (korisnicko_ime, email, lozinka_hash, uloga) VALUES (?, ?, ?, 'administrator')"
    );
    $stmt->execute([$admin_ime, $admin_mail, $hash]);

    echo '<div class="ok">✓ Admin korisnik uspješno kreiran!</div>';
    echo '<pre>';
    echo "Korisničko ime : admin\n";
    echo "Lozinka        : Admin123!\n";
    echo "Email          : admin@filmoteka.hr\n";
    echo '</pre>';
    echo '<div class="err">⚠ OBRIŠI ovu datoteku (setup.php) nakon pokretanja!</div>';
} catch (PDOException $e) {
    echo '<div class="err">Greška: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// ── Provjeri broj filmova ─────────────────────────────────────────────────────
$filmCount = (int)$pdo->query('SELECT COUNT(*) FROM filmovi')->fetchColumn();
echo '<p style="color:#aaa">Filmova u bazi: <strong style="color:#c9a84c">' . $filmCount . '</strong>';
if ($filmCount === 0) {
    echo ' – uvezi <code>database.sql</code> za seed podatke!';
}
echo '</p>';

echo '<p><a href="index.php">← Idi na aplikaciju</a> | <a href="login.php">Prijava</a></p>';
echo '</body></html>';
