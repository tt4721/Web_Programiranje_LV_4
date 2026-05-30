<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$flash_success = flashGet('success');
$flash_error   = flashGet('error');

// ── Admin: upload slike ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAdmin()) {
    csrfVerify();

    if (isset($_FILES['slika']) && $_FILES['slika']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['slika'];

        if (!isValidImage($file)) {
            flashSet('error', 'Slika mora biti JPEG, PNG ili WebP i ne smije prelaziti 5 MB.');
        } else {
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fname    = uniqid('slika_', true) . '.' . $ext;
            $dest     = __DIR__ . '/slike/' . $fname;
            $putanja  = 'slike/' . $fname;
            $opis     = trim($_POST['opis'] ?? '');

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $stmt = $pdo->prepare('INSERT INTO slike (naziv_datoteke, opis, putanja) VALUES (?, ?, ?)');
                $stmt->execute([$fname, $opis, $putanja]);
                flashSet('success', 'Slika uspješno uploadana.');
            } else {
                flashSet('error', 'Greška pri spremanju slike.');
            }
        }
    }
    header('Location: galerija.php');
    exit;
}

// ── Admin: obriši sliku ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_image' && isAdmin()) {
    csrfVerify();
    $slika_id = (int)($_POST['slika_id'] ?? 0);
    $stmt     = $pdo->prepare('SELECT putanja FROM slike WHERE id = ?');
    $stmt->execute([$slika_id]);
    $slika = $stmt->fetch();
    if ($slika) {
        @unlink(__DIR__ . '/' . $slika['putanja']);
        $pdo->prepare('DELETE FROM slike WHERE id = ?')->execute([$slika_id]);
        flashSet('success', 'Slika obrisana.');
    }
    header('Location: galerija.php');
    exit;
}

// ── Auto-sinkronizacija slika iz /slike/ mape s bazom ────────────────────────
$slike_dir = __DIR__ . '/slike/';
if (is_dir($slike_dir)) {
    foreach (glob($slike_dir . '*.{jpg,jpeg,png,webp}', GLOB_BRACE) as $path) {
        $fname = basename($path);
        $stmt  = $pdo->prepare('SELECT id FROM slike WHERE naziv_datoteke = ?');
        $stmt->execute([$fname]);
        if (!$stmt->fetch()) {
            $pdo->prepare('INSERT INTO slike (naziv_datoteke, putanja) VALUES (?, ?)')
                ->execute([$fname, 'slike/' . $fname]);
        }
    }
}

// ── Auto-sinkronizacija filmskih slika iz /images/ ────────────────────────────
$images_dir = __DIR__ . '/images/';
if (is_dir($images_dir)) {
    foreach (glob($images_dir . '*.{jpg,jpeg,webp,png}', GLOB_BRACE) as $path) {
        $fname = basename($path);
        $stmt  = $pdo->prepare('SELECT id FROM slike WHERE naziv_datoteke = ?');
        $stmt->execute([$fname]);
        if (!$stmt->fetch()) {
            $pdo->prepare('INSERT INTO slike (naziv_datoteke, putanja) VALUES (?, ?)')
                ->execute([$fname, 'images/' . $fname]);
        }
    }
}

// ── Dohvati sve slike + prosječne ocjene ─────────────────────────────────────
$slike = $pdo->query('
    SELECT s.*,
           ROUND(AVG(o.ocjena), 1) AS avg_ocjena,
           COUNT(o.id)             AS broj_ocjena
    FROM slike s
    LEFT JOIN ocjene o ON o.id_slika = s.id
    GROUP BY s.id
    ORDER BY s.datum_dodavanja DESC
')->fetchAll();

// ── Korisnikove ocjene (ako je prijavljen) ────────────────────────────────────
$moje_ocjene = [];
if (isLoggedIn()) {
    $stmt = $pdo->prepare('SELECT id_slika, ocjena FROM ocjene WHERE id_korisnik = ?');
    $stmt->execute([$_SESSION['korisnik_id']]);
    foreach ($stmt->fetchAll() as $row) {
        $moje_ocjene[$row['id_slika']] = (int)$row['ocjena'];
    }
}

$page_title   = 'Galerija – Filmoteka';
$current_page = 'galerija';
$extra_css    = ['style_slike.css'];
require_once 'includes/header.php';
?>

<style>
/* ── Star rating widget ────────────────────────────────── */
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 2px;
}
.star-rating input  { display: none; }
.star-rating label  {
    font-size: 1.5rem;
    color: #555;
    cursor: pointer;
    transition: color 0.15s;
}
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label { color: var(--gold); }

.avg-stars { color: var(--gold); font-size: 1rem; }
.avg-info  { color: var(--text-muted); font-size: 0.78rem; margin-top: 2px; }

.galerija-slika-wrap {
    position: relative;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.galerija-slika-wrap img.gallery-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
    cursor: pointer;
    transition: opacity 0.3s;
}
.galerija-slika-wrap img.gallery-img:hover { opacity: 0.85; }
.gallery-info {
    padding: 10px 12px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.gallery-opis { color: var(--text-muted); font-size: 0.8rem; }

/* Lightbox */
.lb-toggle { display: none; }
.lightbox-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.92);
    z-index: 5000;
    justify-content: center;
    align-items: center;
}
.lb-toggle:checked ~ .lightbox-overlay { display: flex; }
.lightbox-box {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
}
.lightbox-box img { max-width: 100%; max-height: 85vh; border-radius: 6px; }
.lightbox-close {
    position: absolute;
    top: -36px; right: 0;
    color: var(--gold);
    font-size: 1.6rem;
    cursor: pointer;
    font-weight: bold;
}

/* Upozorenje niskih ocjena */
.low-rating-warning {
    background: #2a0808;
    border: 1px solid var(--accent-red);
    border-radius: var(--radius-sm);
    padding: 4px 8px;
    font-size: 0.74rem;
    color: var(--accent-red);
    display: inline-block;
}
</style>

<div class="uvodni-clanak">
    <article>
        <div class="article-accent">GALERIJA</div>
        <h2 style="font-family:var(--font-display);color:var(--gold)">Galerija slika</h2>
        <p>
            Kliknite na sliku za prikaz u punoj veličini.
            <?php if (isLoggedIn()): ?>
            Ocjenjujte slike klikom na zvjezdice.
            <?php else: ?>
            <a href="login.php" style="color:var(--gold)">Prijavite se</a> za ocjenjivanje slika.
            <?php endif; ?>
        </p>
    </article>
</div>

<main>

<?php if ($flash_success): ?><div class="flash flash-success"><?= e($flash_success) ?></div><?php endif; ?>
<?php if ($flash_error):   ?><div class="flash flash-error"><?= e($flash_error) ?></div><?php endif; ?>

<?php if (isAdmin()): ?>
<!-- Admin: upload slike -->
<section class="sekcija-filmovi" style="margin-bottom:24px">
    <h3 style="color:var(--gold);margin-bottom:16px;font-family:var(--font-display)">Upload slike (Admin)</h3>
    <form method="POST" action="galerija.php" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;flex:1;min-width:200px">
                <label>Slika (JPEG/PNG/WebP, max 5 MB)</label>
                <input type="file" name="slika" accept="image/jpeg,image/png,image/webp" required>
            </div>
            <div class="form-group" style="margin:0;flex:2;min-width:200px">
                <label>Opis (opcionalno)</label>
                <input type="text" name="opis" placeholder="Kratki opis slike...">
            </div>
            <button type="submit" class="add-btn">Upload</button>
        </div>
    </form>
</section>
<?php endif; ?>

<!-- Galerija -->
<section class="galerija">
    <?php if (empty($slike)): ?>
    <div style="text-align:center;padding:40px;color:var(--text-muted);grid-column:1/-1">
        <p>Nema slika u galeriji. <?= isAdmin() ? 'Uploadajte prvu sliku iznad.' : '' ?></p>
    </div>
    <?php endif; ?>

    <?php foreach ($slike as $slika): ?>
    <?php
        $lb_id       = 'lb_' . $slika['id'];
        $avg         = $slika['avg_ocjena'];
        $user_rating = $moje_ocjene[$slika['id']] ?? 0;
        $stars_full  = '';
        if ($avg !== null) {
            $full  = (int)$avg;
            $half  = ($avg - $full) >= 0.5;
            for ($s = 1; $s <= 5; $s++) {
                if ($s <= $full)      $stars_full .= '★';
                elseif ($s == $full+1 && $half) $stars_full .= '½';
                else                  $stars_full .= '☆';
            }
        }
    ?>
    <div class="galerija-slika-wrap lb-item">
        <!-- Lightbox toggle -->
        <input type="checkbox" id="<?= $lb_id ?>" class="lb-toggle">
        <label for="<?= $lb_id ?>">
            <img src="<?= e($slika['putanja']) ?>"
                 alt="<?= e($slika['opis'] ?: $slika['naziv_datoteke']) ?>"
                 class="gallery-img"
                 loading="lazy">
        </label>

        <div class="gallery-info">
            <?php if ($slika['opis']): ?>
            <p class="gallery-opis"><?= e($slika['opis']) ?></p>
            <?php endif; ?>

            <!-- Prosječna ocjena -->
            <div class="avg-stars">
                <?php if ($avg !== null): ?>
                    <?= $stars_full ?> <strong><?= $avg ?></strong>
                <?php else: ?>
                    ☆☆☆☆☆ <span style="color:var(--text-muted)">Nema ocjena</span>
                <?php endif; ?>
            </div>
            <div class="avg-info">
                <?php if ($slika['broj_ocjena'] > 0): ?>
                    Temeljem <?= $slika['broj_ocjena'] ?> ocjene/ocjena
                <?php endif; ?>
                <?php if ($avg !== null && $avg < 2.0): ?>
                    <span class="low-rating-warning">⚠ Niska ocjena</span>
                <?php endif; ?>
            </div>

            <!-- Star rating forma (samo za prijavljene) -->
            <?php if (isLoggedIn()): ?>
            <form class="star-form" data-slika-id="<?= $slika['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <div class="star-rating" title="Ocijeni ovu sliku">
                    <?php for ($star = 5; $star >= 1; $star--): ?>
                    <input type="radio" id="star_<?= $slika['id'] ?>_<?= $star ?>"
                           name="ocjena" value="<?= $star ?>"
                           <?= $user_rating === $star ? 'checked' : '' ?>>
                    <label for="star_<?= $slika['id'] ?>_<?= $star ?>"
                           title="<?= $star ?> zvjezdic<?= $star === 1 ? 'a' : 'e' ?>">★</label>
                    <?php endfor; ?>
                </div>
                <div class="rating-feedback" style="font-size:0.75rem;color:var(--text-muted);min-height:16px">
                    <?= $user_rating ? "Vaša ocjena: $user_rating ★" : 'Kliknite za ocjenu' ?>
                </div>
            </form>
            <?php endif; ?>

            <!-- Admin: obriši sliku -->
            <?php if (isAdmin()): ?>
            <form method="POST" action="galerija.php" style="margin-top:6px"
                  onsubmit="return confirm('Obrisati ovu sliku?')">
                <?= csrfField() ?>
                <input type="hidden" name="action"   value="delete_image">
                <input type="hidden" name="slika_id" value="<?= $slika['id'] ?>">
                <button type="submit" class="add-btn" style="background:#c0392b;font-size:0.72rem;padding:3px 8px">
                    Obriši sliku
                </button>
            </form>
            <?php endif; ?>
        </div>

        <!-- Lightbox overlay -->
        <div class="lightbox-overlay">
            <div class="lightbox-box">
                <label for="<?= $lb_id ?>" class="lightbox-close">✕</label>
                <img src="<?= e($slika['putanja']) ?>"
                     alt="<?= e($slika['opis'] ?: $slika['naziv_datoteke']) ?>">
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</section>

</main>

<?php require_once 'includes/footer.php'; ?>

<?php if (isLoggedIn()): ?>
<script>
// ── AJAX star rating ─────────────────────────────────────────────────────────
document.querySelectorAll('.star-form').forEach(form => {
    const slikaId  = form.dataset.slikaId;
    const feedback = form.querySelector('.rating-feedback');

    form.querySelectorAll('input[name="ocjena"]').forEach(radio => {
        radio.addEventListener('change', async function () {
            const ocjena = this.value;
            const csrf   = form.querySelector('[name="csrf_token"]').value;
            const body   = new URLSearchParams({ slika_id: slikaId, ocjena, csrf_token: csrf });

            feedback.textContent = 'Sprema...';
            try {
                const res  = await fetch('api/rate_image.php', { method: 'POST', body });
                const data = await res.json();

                if (data.success) {
                    feedback.textContent = `Vaša ocjena: ${ocjena} ★ (prosj: ${data.avg ?? '?'})`;
                    // Ažuriraj prikaz prosječne ocjene bez reloada
                    const avgEl = form.closest('.galerija-slika-wrap').querySelector('.avg-stars');
                    if (avgEl && data.avg) {
                        avgEl.innerHTML = `<strong>${data.avg}</strong> ★ <small style="color:var(--text-muted)">(${data.count} ocjene/a)</small>`;
                    }
                } else {
                    feedback.textContent = data.message || 'Greška';
                }
            } catch (err) {
                feedback.textContent = 'Greška veze.';
            }
        });
    });
});
</script>
<?php endif; ?>
