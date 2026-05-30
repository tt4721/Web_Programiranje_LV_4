<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAdmin();

$flash_success = flashGet('success');
$flash_error   = flashGet('error');
$errors        = [];
$edit_film     = null;

// ── CRUD akcije ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $action = $_POST['action'] ?? '';

    // ── DODAJ film ────────────────────────────────────────────────────────────
    if ($action === 'add' || $action === 'edit') {
        $data = [
            'naslov'           => trim($_POST['naslov']           ?? ''),
            'zanr'             => trim($_POST['zanr']             ?? ''),
            'godina'           => trim($_POST['godina']           ?? ''),
            'trajanje_min'     => trim($_POST['trajanje_min']     ?? ''),
            'ocjena'           => trim($_POST['ocjena']           ?? ''),
            'rezisery'         => trim($_POST['rezisery']         ?? ''),
            'zemlja_porijekla' => trim($_POST['zemlja_porijekla'] ?? ''),
        ];
        $errors = validateFilm($data);

        if (empty($errors)) {
            if ($action === 'add') {
                $stmt = $pdo->prepare('
                    INSERT INTO filmovi (naslov, zanr, godina, trajanje_min, ocjena, rezisery, zemlja_porijekla)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $data['naslov'], $data['zanr'], (int)$data['godina'],
                    (int)$data['trajanje_min'], (float)$data['ocjena'],
                    $data['rezisery'], $data['zemlja_porijekla']
                ]);
                flashSet('success', 'Film "' . $data['naslov'] . '" uspješno dodan.');

                if ((float)$data['ocjena'] < 5.0) {
                    flashSet('warning', 'Upozorenje: Film "' . $data['naslov'] . '" ima nisku ocjenu (' . $data['ocjena'] . ')!');
                }
            } else {
                $film_id = (int)($_POST['film_id'] ?? 0);
                $stmt    = $pdo->prepare('
                    UPDATE filmovi SET naslov=?, zanr=?, godina=?, trajanje_min=?, ocjena=?, rezisery=?, zemlja_porijekla=?
                    WHERE id=?
                ');
                $stmt->execute([
                    $data['naslov'], $data['zanr'], (int)$data['godina'],
                    (int)$data['trajanje_min'], (float)$data['ocjena'],
                    $data['rezisery'], $data['zemlja_porijekla'], $film_id
                ]);
                flashSet('success', 'Film "' . $data['naslov'] . '" uspješno ažuriran.');
            }
            header('Location: dashboard.php');
            exit;
        }
        // Ako ima grešaka, ostani na formi s unesenim podacima
        if ($action === 'edit') {
            $edit_film = array_merge(['id' => (int)($_POST['film_id'] ?? 0)], $data);
        }
    }

    // ── OBRIŠI film ───────────────────────────────────────────────────────────
    if ($action === 'delete') {
        $film_id = (int)($_POST['film_id'] ?? 0);
        $stmt    = $pdo->prepare('DELETE FROM filmovi WHERE id = ?');
        $stmt->execute([$film_id]);
        flashSet('success', 'Film je obrisan.');
        header('Location: dashboard.php');
        exit;
    }

    // ── CSV UVOZ ──────────────────────────────────────────────────────────────
    if ($action === 'csv_import') {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $file    = $_FILES['csv_file']['tmp_name'];
            $handle  = fopen($file, 'r');
            $header  = fgetcsv($handle); // preskoči zaglavlje
            $added   = 0;
            $skipped = 0;

            $stmt = $pdo->prepare('
                INSERT IGNORE INTO filmovi (naslov, zanr, godina, trajanje_min, ocjena, rezisery, zemlja_porijekla)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 7) { $skipped++; continue; }
                [$naslov, $zanr, $godina, $trajanje, $ocjena, $reziser, $zemlja] = array_map('trim', $row);
                if (empty($naslov)) { $skipped++; continue; }
                $stmt->execute([$naslov, $zanr, (int)$godina, (int)$trajanje, (float)$ocjena, $reziser, $zemlja]);
                $added++;
            }
            fclose($handle);
            flashSet('success', "CSV uvoz završen: $added dodano, $skipped preskočeno.");
        } else {
            flashSet('error', 'Greška pri uploadu CSV datoteke.');
        }
        header('Location: dashboard.php');
        exit;
    }
}

// ── Učitaj film za editiranje (GET) ───────────────────────────────────────────
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM filmovi WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit_film = $stmt->fetch();
}

// ── Dohvati sve filmove ───────────────────────────────────────────────────────
$filmovi = $pdo->query('SELECT * FROM filmovi ORDER BY naslov ASC')->fetchAll();

// ── Statistika ────────────────────────────────────────────────────────────────
$stats = $pdo->query('
    SELECT COUNT(*) as ukupno,
           ROUND(AVG(ocjena),2) as avg_ocjena,
           MIN(godina) as najstariji,
           MAX(godina) as najnoviji
    FROM filmovi
')->fetch();

$korisnici_count = (int)$pdo->query('SELECT COUNT(*) FROM korisnici')->fetchColumn();
$videoteka_count = (int)$pdo->query('SELECT COUNT(*) FROM zeljeni_filmovi')->fetchColumn();

$page_title   = 'Admin Dashboard – Filmoteka';
$current_page = 'dashboard';
require_once 'includes/header.php';
?>

<div class="uvodni-clanak">
    <article>
        <div class="article-accent">ADMINISTRACIJA</div>
        <h2 style="font-family:var(--font-display);color:var(--gold)">Admin Panel</h2>
        <p>Upravljanje filmovima, korisnicima i podacima aplikacije.</p>
    </article>
</div>

<main>

<?php if ($flash_success): ?><div class="flash flash-success"><?= e($flash_success) ?></div><?php endif; ?>
<?php if ($flash_error):   ?><div class="flash flash-error"><?= e($flash_error) ?></div><?php endif; ?>
<?php $fw = flashGet('warning'); if ($fw): ?>
<div class="flash flash-warning"><?= e($fw) ?></div>
<?php endif; ?>

<!-- Statistike -->
<div class="dash-stats">
    <div class="stat-card"><span class="stat-num"><?= $stats['ukupno'] ?></span><span class="stat-lbl">Filmova</span></div>
    <div class="stat-card"><span class="stat-num"><?= $stats['avg_ocjena'] ?? 'N/A' ?></span><span class="stat-lbl">Prosj. ocjena</span></div>
    <div class="stat-card"><span class="stat-num"><?= $korisnici_count ?></span><span class="stat-lbl">Korisnika</span></div>
    <div class="stat-card"><span class="stat-num"><?= $videoteka_count ?></span><span class="stat-lbl">Posudbi</span></div>
</div>

<div class="dash-grid">

    <!-- Forma za dodavanje / editiranje -->
    <section class="sekcija-filmovi">
        <h3 style="color:var(--gold);margin-bottom:20px;font-family:var(--font-display)">
            <?= $edit_film ? 'Uredi film' : 'Dodaj novi film' ?>
        </h3>

        <?php foreach ($errors as $err): ?>
        <div class="flash flash-error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="dashboard.php">
            <?= csrfField() ?>
            <input type="hidden" name="action"  value="<?= $edit_film ? 'edit' : 'add' ?>">
            <?php if ($edit_film): ?>
            <input type="hidden" name="film_id" value="<?= (int)$edit_film['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Naslov *</label>
                <input type="text" name="naslov" required
                       value="<?= e($edit_film['naslov'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Žanr * <small style="color:var(--text-muted)">(npr. Drama, Action)</small></label>
                <input type="text" name="zanr" required
                       value="<?= e($edit_film['zanr'] ?? '') ?>">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group">
                    <label>Godina * (1888–<?= date('Y')+1 ?>)</label>
                    <input type="number" name="godina" required
                           min="1888" max="<?= date('Y')+1 ?>"
                           value="<?= e($edit_film['godina'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Trajanje (min) * (1–600)</label>
                    <input type="number" name="trajanje_min" required
                           min="1" max="600"
                           value="<?= e($edit_film['trajanje_min'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Ocjena * (1.0–10.0)</label>
                    <input type="number" name="ocjena" required step="0.1"
                           min="1.0" max="10.0"
                           value="<?= e($edit_film['ocjena'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Zemlja porijekla *</label>
                    <input type="text" name="zemlja_porijekla" required
                           value="<?= e($edit_film['zemlja_porijekla'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Redatelj(i) *</label>
                <input type="text" name="rezisery" required
                       value="<?= e($edit_film['rezisery'] ?? '') ?>">
            </div>

            <div style="display:flex;gap:10px">
                <button type="submit" class="btn-primary">
                    <?= $edit_film ? 'Spremi promjene' : 'Dodaj film' ?>
                </button>
                <?php if ($edit_film): ?>
                <a href="dashboard.php" class="add-btn" style="background:#666;text-decoration:none">Odustani</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- CSV uvoz -->
        <hr style="border-color:var(--border);margin:28px 0">
        <h3 style="color:var(--gold);margin-bottom:16px;font-family:var(--font-display)">Uvoz filmova iz CSV-a</h3>
        <p style="color:var(--text-muted);font-size:0.84rem;margin-bottom:12px">
            CSV mora imati zaglavlje: Naslov, Zanr, Godina, Trajanje_min, Ocjena, Rezisery, Zemlja_porijekla
        </p>
        <form method="POST" action="dashboard.php" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="csv_import">
            <div style="display:flex;gap:10px;align-items:flex-end">
                <div class="form-group" style="margin:0;flex:1">
                    <label>CSV datoteka</label>
                    <input type="file" name="csv_file" accept=".csv" required>
                </div>
                <button type="submit" class="add-btn">Uvezi</button>
            </div>
        </form>
    </section>

    <!-- Popis filmova -->
    <section class="sekcija-filmovi">
        <h3 style="color:var(--gold);margin-bottom:16px;font-family:var(--font-display)">
            Svi filmovi (<?= count($filmovi) ?>)
        </h3>
        <div class="tablica-wrapper">
            <table class="tablica-filmovi">
                <thead>
                    <tr>
                        <th>NASLOV</th>
                        <th>ŽANR</th>
                        <th>GOD.</th>
                        <th>OCJENA</th>
                        <th>AKCIJE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filmovi as $film): ?>
                    <tr>
                        <td data-label="Naslov"><?= e($film['naslov']) ?></td>
                        <td data-label="Žanr"><?= zanrBadge($film['zanr']) ?></td>
                        <td data-label="Godina"><?= e($film['godina']) ?></td>
                        <td data-label="Ocjena"><span class="ocjena">★ <?= e($film['ocjena']) ?></span>
                            <?php if ($film['ocjena'] < 5.0): ?>
                            <span style="color:var(--accent-red);font-size:0.7rem">⚠ NISKA</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Akcije" style="white-space:nowrap">
                            <a href="dashboard.php?edit=<?= $film['id'] ?>" class="add-btn" style="text-decoration:none">Uredi</a>
                            <form method="POST" action="dashboard.php" style="display:inline"
                                  onsubmit="return confirm('Obrisati film <?= e(addslashes($film['naslov'])) ?>?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="action"  value="delete">
                                <input type="hidden" name="film_id" value="<?= $film['id'] ?>">
                                <button type="submit" class="add-btn" style="background:#c0392b">Obriši</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</div><!-- /.dash-grid -->
</main>

<?php require_once 'includes/footer.php'; ?>
