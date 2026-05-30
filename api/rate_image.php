<?php
/**
 * AJAX endpoint za ocjenjivanje slika
 * POST params: slika_id, ocjena (1-5), csrf_token
 * Response: JSON { success, avg, count, message? }
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Morate biti prijavljeni.']);
    exit;
}

// CSRF
$token = $_POST['csrf_token'] ?? '';
if (!hash_equals(csrfToken(), $token)) {
    echo json_encode(['success' => false, 'message' => 'CSRF greška.']);
    exit;
}

$slika_id = (int)($_POST['slika_id'] ?? 0);
$ocjena   = (int)($_POST['ocjena']   ?? 0);

if ($slika_id <= 0 || $ocjena < 1 || $ocjena > 5) {
    echo json_encode(['success' => false, 'message' => 'Nevažeći parametri.']);
    exit;
}

// Provjeri postoji li slika
$stmt = $pdo->prepare('SELECT id FROM slike WHERE id = ?');
$stmt->execute([$slika_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Slika ne postoji.']);
    exit;
}

// INSERT ili UPDATE ocjene (ON DUPLICATE KEY UPDATE)
$stmt = $pdo->prepare('
    INSERT INTO ocjene (id_korisnik, id_slika, ocjena)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE ocjena = VALUES(ocjena), vrijeme_ocjene = NOW()
');
$stmt->execute([$_SESSION['korisnik_id'], $slika_id, $ocjena]);

// Dohvati novu prosječnu ocjenu
$stmt = $pdo->prepare('
    SELECT ROUND(AVG(ocjena), 1) AS avg_ocjena, COUNT(*) AS count
    FROM ocjene
    WHERE id_slika = ?
');
$stmt->execute([$slika_id]);
$result = $stmt->fetch();

echo json_encode([
    'success' => true,
    'avg'     => $result['avg_ocjena'],
    'count'   => $result['count'],
]);
