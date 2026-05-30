<?php
function isLoggedIn(): bool {
    return !empty($_SESSION['korisnik_id']);
}

function isAdmin(): bool {
    return !empty($_SESSION['uloga']) && $_SESSION['uloga'] === 'administrator';
}

function requireLogin(string $redirect = 'login.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'Morate biti prijavljeni za pristup toj stranici.';
        header("Location: $redirect");
        exit;
    }
}

function requireAdmin(string $redirect = 'index.php'): void {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['flash_error'] = 'Pristup dozvoljen samo administratorima.';
        header("Location: $redirect");
        exit;
    }
}

function loginUser(PDO $pdo, string $korisnicko_ime, string $lozinka): bool {
    $stmt = $pdo->prepare('SELECT id, korisnicko_ime, lozinka_hash, uloga FROM korisnici WHERE korisnicko_ime = ?');
    $stmt->execute([$korisnicko_ime]);
    $user = $stmt->fetch();

    if ($user && password_verify($lozinka, $user['lozinka_hash'])) {
        session_regenerate_id(true);
        $_SESSION['korisnik_id']    = $user['id'];
        $_SESSION['korisnicko_ime'] = $user['korisnicko_ime'];
        $_SESSION['uloga']          = $user['uloga'];
        return true;
    }
    return false;
}

function logoutUser(): void {
    session_unset();
    session_destroy();
}

function registerUser(PDO $pdo, string $korisnicko_ime, string $email, string $lozinka): bool|string {
    // Provjera duplikata
    $stmt = $pdo->prepare('SELECT id FROM korisnici WHERE korisnicko_ime = ? OR email = ?');
    $stmt->execute([$korisnicko_ime, $email]);
    if ($stmt->fetch()) {
        return 'Korisničko ime ili email već postoji.';
    }

    $hash = password_hash($lozinka, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO korisnici (korisnicko_ime, email, lozinka_hash) VALUES (?, ?, ?)');
    $stmt->execute([$korisnicko_ime, $email, $hash]);
    return true;
}
