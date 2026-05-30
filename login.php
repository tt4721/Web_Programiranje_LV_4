<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Već prijavljen → na početnu
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    $korisnicko_ime = trim($_POST['korisnicko_ime'] ?? '');
    $lozinka        = $_POST['lozinka'] ?? '';

    if ($korisnicko_ime === '' || $lozinka === '') {
        $errors[] = 'Sva polja su obavezna.';
    } else {
        if (loginUser($pdo, $korisnicko_ime, $lozinka)) {
            flashSet('success', 'Dobrodošli, ' . $_SESSION['korisnicko_ime'] . '!');
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Pogrešno korisničko ime ili lozinka.';
        }
    }
}

$page_title   = 'Prijava – Filmoteka';
$current_page = 'login';
require_once 'includes/header.php';
?>

<main style="max-width:420px;margin:60px auto;padding:0 20px">
    <div class="sekcija-filmovi" style="border-radius:12px">
        <div class="article-accent" style="margin-bottom:8px">PRIJAVA</div>
        <h2 style="font-family:var(--font-display);color:var(--gold);margin-bottom:24px">Prijavite se</h2>

        <?php foreach ($errors as $err): ?>
        <div class="flash flash-error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="login.php">
            <?= csrfField() ?>

            <div class="form-group">
                <label for="korisnicko_ime">Korisničko ime</label>
                <input type="text" id="korisnicko_ime" name="korisnicko_ime"
                       value="<?= e($_POST['korisnicko_ime'] ?? '') ?>"
                       required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="lozinka">Lozinka</label>
                <input type="password" id="lozinka" name="lozinka"
                       required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-primary">Prijava</button>
        </form>

        <p style="margin-top:20px;color:var(--text-muted);font-size:0.88rem">
            Nemate račun?
            <a href="register.php" style="color:var(--gold)">Registrirajte se</a>
        </p>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
