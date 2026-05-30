<?php
/**
 * AJAX endpoint za košaricu
 * POST params: action (add|remove|confirm), film_id, csrf_token
 * Response: JSON { success, count, message?, saved? }
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// CSRF
$token = $_POST['csrf_token'] ?? '';
if (!hash_equals(csrfToken(), $token)) {
    echo json_encode(['success' => false, 'message' => 'CSRF greška.']);
    exit;
}

if (!isset($_SESSION['kosharica'])) {
    $_SESSION['kosharica'] = [];
}

$action  = $_POST['action']  ?? '';
$film_id = (int)($_POST['film_id'] ?? 0);

// ── DODAJ u košaricu ──────────────────────────────────────────────────────────
if ($action === 'add' && $film_id > 0) {
    // Provjeri je li već u košarici
    foreach ($_SESSION['kosharica'] as $item) {
        if ($item['id'] === $film_id) {
            echo json_encode(['success' => false, 'message' => 'Film je već u košarici.', 'count' => count($_SESSION['kosharica'])]);
            exit;
        }
    }

    // Dohvati film iz baze
    $stmt = $pdo->prepare('SELECT id, naslov, zanr, godina, ocjena FROM filmovi WHERE id = ?');
    $stmt->execute([$film_id]);
    $film = $stmt->fetch();

    if (!$film) {
        echo json_encode(['success' => false, 'message' => 'Film nije pronađen.']);
        exit;
    }

    $_SESSION['kosharica'][] = [
        'id'     => $film['id'],
        'naslov' => $film['naslov'],
        'zanr'   => $film['zanr'],
        'godina' => $film['godina'],
        'ocjena' => $film['ocjena'],
    ];

    echo json_encode([
        'success'      => true,
        'count'        => count($_SESSION['kosharica']),
        'low_rating'   => (float)$film['ocjena'] < 5.0,
    ]);
    exit;
}

// ── UKLONI iz košarice ────────────────────────────────────────────────────────
if ($action === 'remove' && $film_id > 0) {
    $_SESSION['kosharica'] = array_values(
        array_filter($_SESSION['kosharica'], fn($item) => $item['id'] !== $film_id)
    );
    echo json_encode(['success' => true, 'count' => count($_SESSION['kosharica'])]);
    exit;
}

// ── POTVRDI posudbu (spremi u zeljeni_filmovi) ────────────────────────────────
if ($action === 'confirm') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Morate biti prijavljeni.']);
        exit;
    }

    if (empty($_SESSION['kosharica'])) {
        echo json_encode(['success' => false, 'message' => 'Košarica je prazna.']);
        exit;
    }

    $saved = 0;
    $stmt  = $pdo->prepare('
        INSERT IGNORE INTO zeljeni_filmovi (id_korisnika, id_filma)
        VALUES (?, ?)
    ');

    foreach ($_SESSION['kosharica'] as $item) {
        $stmt->execute([$_SESSION['korisnik_id'], $item['id']]);
        if ($stmt->rowCount() > 0) $saved++;
    }

    $_SESSION['kosharica'] = [];

    echo json_encode([
        'success' => true,
        'saved'   => $saved,
        'count'   => 0,
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Nepoznata akcija.']);
