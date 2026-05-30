<?php
// Konekcija na bazu – čita env varijable (Railway) ili koristi localhost defaults (XAMPP)
$host   = getenv('DB_HOST')   ?: '127.0.0.1';
$dbname = getenv('DB_NAME')   ?: 'filmoteka';
$user   = getenv('DB_USER')   ?: 'root';
$pass   = getenv('DB_PASS')   ?: '';
$port   = getenv('DB_PORT')   ?: '3306';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('<div style="color:red;padding:20px;font-family:sans-serif">
        <strong>Greška baze podataka:</strong> ' . htmlspecialchars($e->getMessage()) . '
        <br><small>Provjeri XAMPP MySQL i uvezi database.sql</small>
    </div>');
}
