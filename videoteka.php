<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$flash_success = flashGet('success');
$flash_error   = flashGet('error');

// ── Uklanjanje filma iz videoteke ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'remove') {
    csrfVerify();
    $film_id = (int)($_POST['film_id'] ?? 0);
    if ($film_id > 0) {
        $stmt = $pdo->prepare('DELETE FROM zeljeni_filmovi WHERE id_korisnika = ? AND id_filma = ?');
        $stmt->execute([$_SESSION['korisnik_id'], $film_id]);
        flashSet('success', 'Film uklonjen iz videoteke.');
    }
    header('Location: videoteka.php');
    exit;
}

// ── Dohvati korisnikovu videoteku ─────────────────────────────────────────────
$stmt = $pdo->prepare('
    SELECT f.*, zf.datum_dodavanja
    FROM zeljeni_filmovi zf
    JOIN filmovi f ON f.id = zf.id_filma
    WHERE zf.id_korisnika = ?
    ORDER BY zf.datum_dodavanja DESC
');
$stmt->execute([$_SESSION['korisnik_id']]);
$videoteka = $stmt->fetchAll();

$page_title   = 'Moja Videoteka – Filmoteka';
$current_page = 'videoteka';
require_once 'includes/header.php';
?>

<div class="uvodni-clanak">
    <article>
        <div class="article-accent">OSOBNA</div>
        <h2 style="font-family:var(--font-display);color:var(--gold)">Moja Videoteka</h2>
        <p>Filmovi koje ste dodali u svoju osobnu videoteku. Prijavljen kao:
            <strong style="color:var(--gold)"><?= e($_SESSION['korisnicko_ime']) ?></strong>
        </p>
    </article>
</div>

<main>
    <?php if ($flash_success): ?><div class="flash flash-success"><?= e($flash_success) ?></div><?php endif; ?>
    <?php if ($flash_error):   ?><div class="flash flash-error"><?= e($flash_error) ?></div><?php endif; ?>

    <section class="sekcija-filmovi">
        <?php if (empty($videoteka)): ?>
        <div style="text-align:center;padding:40px;color:var(--text-muted)">
            <p style="font-size:1.1rem">Vaša videoteka je prazna.</p>
            <a href="index.php" class="add-btn" style="display:inline-block;margin-top:16px;text-decoration:none">
                ← Pregledaj filmove
            </a>
        </div>
        <?php else: ?>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <h3 style="color:var(--text-muted);font-size:0.9rem;font-weight:400">
                <?= count($videoteka) ?> film(ova) u videoteci
            </h3>
            <a href="index.php" style="color:var(--gold);font-size:0.85rem">← Dodaj još filmova</a>
        </div>

        <div class="tablica-wrapper">
            <table class="tablica-filmovi">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NASLOV</th>
                        <th>ŽANR</th>
                        <th>GODINA</th>
                        <th>MIN</th>
                        <th>OCJENA</th>
                        <th>REDATELJ</th>
                        <th>DODANO</th>
                        <th>AKCIJA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($videoteka as $i => $film): ?>
                    <tr>
                        <td data-label="#"><?= $i + 1 ?></td>
                        <td data-label="Naslov" style="font-weight:500"><?= e($film['naslov']) ?></td>
                        <td data-label="Žanr"><?= zanrBadge($film['zanr']) ?></td>
                        <td data-label="Godina"><?= e($film['godina']) ?></td>
                        <td data-label="Min"><?= e($film['trajanje_min']) ?></td>
                        <td data-label="Ocjena"><span class="ocjena">★ <?= e($film['ocjena']) ?></span></td>
                        <td data-label="Redatelj"><?= e($film['rezisery']) ?></td>
                        <td data-label="Dodano" style="color:var(--text-muted);font-size:0.8rem">
                            <?= date('d.m.Y.', strtotime($film['datum_dodavanja'])) ?>
                        </td>
                        <td data-label="Akcija">
                            <form method="POST" action="videoteka.php"
                                  onsubmit="return confirm('Ukloniti <?= e(addslashes($film['naslov'])) ?> iz videoteke?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="action"  value="remove">
                                <input type="hidden" name="film_id" value="<?= $film['id'] ?>">
                                <button type="submit" class="add-btn" style="background:#c0392b">Ukloni</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>
