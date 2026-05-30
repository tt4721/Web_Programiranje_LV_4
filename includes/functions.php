<?php
// Mapiranje žanra → CSS klasa
function zanrBadge(string $zanrStr): string {
    $map = [
        'Drama'       => 'zanr-drama',
        'Comedy'      => 'zanr-komedija',
        'Animation'   => 'zanr-animacija',
        'Romance'     => 'zanr-romantični',
        'Crime'       => 'zanr-krimic',
        'Thriller'    => 'zanr-triler',
        'Adventure'   => 'zanr-avantura',
        'Documentary' => 'zanr-dokumentarni',
        'Horror'      => 'zanr-horor',
        'Action'      => 'zanr-akcija',
        'Sci-Fi'      => 'zanr-triler',
        'Mystery'     => 'zanr-krimic',
        'Biography'   => 'zanr-dokumentarni',
        'War'         => 'zanr-akcija',
        'Western'     => 'zanr-avantura',
    ];

    $html  = '';
    $parts = array_map('trim', explode(',', $zanrStr));
    foreach ($parts as $zanr) {
        $cls   = $map[$zanr] ?? '';
        $safe  = htmlspecialchars($zanr);
        $html .= "<span class=\"zanr $cls\">$safe</span> ";
    }
    return $html;
}

// Flash poruke
function flashSet(string $key, string $msg): void {
    $_SESSION["flash_$key"] = $msg;
}

function flashGet(string $key): string {
    $val = $_SESSION["flash_$key"] ?? '';
    unset($_SESSION["flash_$key"]);
    return $val;
}

// CSRF token
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function csrfVerify(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        die('CSRF provjera neuspješna.');
    }
}

// Sanitizacija outputa
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Provjera formata slike
function isValidImage(array $file): bool {
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5 MB
    return in_array($file['type'], $allowed) && $file['size'] <= $maxSize;
}

// Validacija filma
function validateFilm(array $data): array {
    $errors = [];
    if (empty($data['naslov'])) {
        $errors[] = 'Naslov je obavezan.';
    }
    if (empty($data['zanr'])) {
        $errors[] = 'Žanr je obavezan.';
    }
    $god = (int)($data['godina'] ?? 0);
    if ($god < 1888 || $god > (int)date('Y') + 1) {
        $errors[] = 'Godina mora biti između 1888 i ' . ((int)date('Y') + 1) . '.';
    }
    $traj = (int)($data['trajanje_min'] ?? 0);
    if ($traj < 1 || $traj > 600) {
        $errors[] = 'Trajanje mora biti između 1 i 600 minuta.';
    }
    $ocj = (float)($data['ocjena'] ?? 0);
    if ($ocj < 1.0 || $ocj > 10.0) {
        $errors[] = 'Ocjena mora biti između 1.0 i 10.0.';
    }
    if (empty($data['rezisery'])) {
        $errors[] = 'Redatelj je obavezan.';
    }
    if (empty($data['zemlja_porijekla'])) {
        $errors[] = 'Zemlja je obavezna.';
    }
    return $errors;
}
