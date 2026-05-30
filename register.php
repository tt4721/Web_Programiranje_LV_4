<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    $korisnicko_ime = trim($_POST['korisnicko_ime'] ?? '');
    $email          = trim($_POST['email']          ?? '');
    $lozinka        = $_POST['lozinka']             ?? '';
    $lozinka2       = $_POST['lozinka2']            ?? '';

    // Validacija
    if ($korisnicko_ime === '') $errors[] = 'Korisničko ime je obavezno.';
    elseif (strlen($korisnicko_ime) < 3) $errors[] = 'Korisničko ime mora imati najmanje 3 znaka.';
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $korisnicko_ime)) $errors[] = 'Korisničko ime smije sadržavati samo slova, brojeve i _.';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email adresa nije ispravna.';

    if (strlen($lozinka) < 6) $errors[] = 'Lozinka mora imati najmanje 6 znakova.';
    if ($lozinka !== $lozinka2)         $errors[] = 'Lozinke se ne podudaraju.';

    if (empty($errors)) {
        $result = registerUser($pdo, $korisnicko_ime, $email, $lozinka);
        if ($result === true) {
            // Automatska prijava
            loginUser($pdo, $korisnicko_ime, $lozinka);
            flashSet('success', 'Registracija uspješna! Dobrodošli, ' . $korisnicko_ime . '!');
            header('Location: index.php');
            exit;
        } else {
            $errors[] = $result; // string s greškom
        }
    }
}

$page_title   = 'Registracija – Filmoteka';
$current_page = 'register';
require_once 'includes/header.php';
?>

<main style="max-width:420px;margin:60px auto;padding:0 20px">
    <div class="sekcija-filmovi" style="border-radius:12px">
        <div class="article-accent" style="margin-bottom:8px">NOVI RAČUN</div>
        <h2 style="font-family:var(--font-display);color:var(--gold);margin-bottom:24px">Registracija</h2>

        <?php foreach ($errors as $err): ?>
        <div class="flash flash-error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="register.php">
            <?= csrfField() ?>

            <div class="form-group">
                <label for="korisnicko_ime">Korisničko ime</label>
                <input type="text" id="korisnicko_ime" name="korisnicko_ime"
                       value="<?= e($_POST['korisnicko_ime'] ?? '') ?>"
                       required minlength="3" pattern="[a-zA-Z0-9_]+"
                       autocomplete="username">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= e($_POST['email'] ?? '') ?>"
                       required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="lozinka">Lozinka <small style="color:var(--text-muted)">(min. 6 znakova)</small></label>
                <input type="password" id="lozinka" name="lozinka"
                       required minlength="6" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="lozinka2">Ponovi lozinku</label>
                <input type="password" id="lozinka2" name="lozinka2"
                       required minlength="6" autocomplete="new-password">
            </div>

            <button type="submit" class="btn-primary">Registriraj se</button>
        </form>

        <p style="margin-top:20px;color:var(--text-muted);font-size:0.88rem">
            Već imate račun?
            <a href="login.php" style="color:var(--gold)">Prijavite se</a>
        </p>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
