<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// ── Cart akcije (AJAX POST iz api/cart.php, ali i direktni POST) ──────────────
// ── Inicijalizacija session košarice ─────────────────────────────────────────
if (!isset($_SESSION['kosharica'])) {
    $_SESSION['kosharica'] = [];
}

// ── Flash poruke ──────────────────────────────────────────────────────────────
$flash_success = flashGet('success');
$flash_error   = flashGet('error');
$flash_warning = flashGet('warning');

// ── Filtriranje + sortiranje (GET params) ─────────────────────────────────────
$filter_naslov  = trim($_GET['naslov']     ?? '');
$filter_zanr    = trim($_GET['zanr']       ?? '');
$filter_zemlja  = trim($_GET['zemlja']     ?? '');
$filter_ocjena  = trim($_GET['ocjena_min'] ?? '');
$sort_col       = $_GET['sort']   ?? 'naslov';
$sort_dir       = $_GET['dir']    ?? 'asc';

$allowed_sort = ['naslov','zanr','godina','trajanje_min','ocjena','rezisery','zemlja_porijekla'];
$allowed_dir  = ['asc','desc'];
if (!in_array($sort_col, $allowed_sort)) $sort_col = 'naslov';
if (!in_array($sort_dir, $allowed_dir))  $sort_dir  = 'asc';

// ── SQL upit s filtrima ───────────────────────────────────────────────────────
$where  = [];
$params = [];

if ($filter_naslov !== '') {
    $where[]  = 'naslov LIKE ?';
    $params[] = "%$filter_naslov%";
}
if ($filter_zanr !== '') {
    $where[]  = 'zanr LIKE ?';
    $params[] = "%$filter_zanr%";
}
if ($filter_zemlja !== '') {
    $where[]  = 'zemlja_porijekla LIKE ?';
    $params[] = "%$filter_zemlja%";
}
if ($filter_ocjena !== '' && is_numeric($filter_ocjena)) {
    $where[]  = 'ocjena >= ?';
    $params[] = (float)$filter_ocjena;
}

$sql = 'SELECT * FROM filmovi';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= " ORDER BY $sort_col $sort_dir";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$filmovi = $stmt->fetchAll();

// ── Dohvati session košaricu za prikaz ───────────────────────────────────────
$kosharica_ids = array_column($_SESSION['kosharica'], 'id');

// ── Pronađi filmove koji su u korisnikovoj videoteci ─────────────────────────
$videoteka_ids = [];
if (isLoggedIn()) {
    $stmt2 = $pdo->prepare('SELECT id_filma FROM zeljeni_filmovi WHERE id_korisnika = ?');
    $stmt2->execute([$_SESSION['korisnik_id']]);
    $videoteka_ids = array_column($stmt2->fetchAll(), 'id_filma');
}

// ── Sorter helper ─────────────────────────────────────────────────────────────
function sortLink(string $col, string $label, string $current_col, string $current_dir): string {
    $next_dir = ($current_col === $col && $current_dir === 'asc') ? 'desc' : 'asc';
    $arrow    = '';
    if ($current_col === $col) {
        $arrow = $current_dir === 'asc' ? ' ↑' : ' ↓';
    }
    $qs = http_build_query(array_merge($_GET, ['sort' => $col, 'dir' => $next_dir]));
    return "<a href=\"index.php?$qs\" style=\"color:inherit;text-decoration:none\">$label$arrow</a>";
}

$page_title  = 'Filmoteka – Popis Filmova';
$current_page = 'index';
require_once 'includes/header.php';
?>

<?php if ($flash_success): ?>
<div class="flash flash-success"><?= e($flash_success) ?></div>
<?php endif; ?>
<?php if ($flash_error): ?>
<div class="flash flash-error"><?= e($flash_error) ?></div>
<?php endif; ?>
<?php if ($flash_warning): ?>
<div class="flash flash-warning"><?= e($flash_warning) ?></div>
<?php endif; ?>

<div class="uvodni-clanak">
    <article aria-labelledby="clanak-naslov">
        <div class="article-accent">DOBRODOŠLI</div>
        <h2 id="clanak-naslov">O ovoj stranici</h2>
        <p>
            Filmoteka je pregled klasičnih filmova iz cijelog svijeta. Stranica prikazuje popis filmova
            s ocjenama, žanrovima i osnovnim informacijama. Koristite navigaciju za pristup grafikonima i galeriji slika.
            <?php if (!isLoggedIn()): ?>
            <a href="login.php" style="color:var(--gold)">Prijavite se</a> da biste dodavali filmove u osobnu videoteku.
            <?php endif; ?>
        </p>
    </article>
</div>

<main>
    <!-- Gumb košarice -->
    <div class="cart-bar">
        <button id="toggle-cart" aria-label="Otvori košaricu">
            🛒 Košarica <span id="cart-count"><?= count($_SESSION['kosharica']) ?></span>
        </button>
    </div>

    <div class="glavni-grid">
        <section class="sekcija-filmovi">

            <!-- Filtri -->
            <form method="GET" action="index.php" id="filter-container">
                <div class="filter-grid">
                    <div class="filter-field">
                        <label for="f-naslov">Naslov</label>
                        <input type="text" id="f-naslov" name="naslov"
                               placeholder="Pretraži naslov..."
                               value="<?= e($filter_naslov) ?>">
                    </div>
                    <div class="filter-field">
                        <label for="f-zanr">Žanr</label>
                        <input type="text" id="f-zanr" name="zanr"
                               placeholder="npr. Drama, Crime..."
                               value="<?= e($filter_zanr) ?>">
                    </div>
                    <div class="filter-field">
                        <label for="f-zemlja">Zemlja</label>
                        <input type="text" id="f-zemlja" name="zemlja"
                               placeholder="npr. USA"
                               value="<?= e($filter_zemlja) ?>">
                    </div>
                    <div class="filter-field">
                        <label for="f-ocjena">Min. ocjena</label>
                        <input type="number" id="f-ocjena" name="ocjena_min"
                               placeholder="8.5" step="0.1" min="1" max="10"
                               value="<?= e($filter_ocjena) ?>">
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter">Filtriraj</button>
                        <a href="index.php" class="btn-reset">Poništi</a>
                    </div>
                </div>
                <?php foreach (['sort','dir'] as $k): ?>
                <input type="hidden" name="<?= $k ?>" value="<?= e($_GET[$k] ?? '') ?>">
                <?php endforeach; ?>
            </form>

            <!-- Tablica filmova -->
            <div id="table-container" class="tablica-wrapper" style="margin-top:16px">
                <table class="tablica-filmovi">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= sortLink('naslov',           'NASLOV',   $sort_col, $sort_dir) ?></th>
                            <th><?= sortLink('zanr',             'ŽANR',     $sort_col, $sort_dir) ?></th>
                            <th><?= sortLink('godina',           'GODINA',   $sort_col, $sort_dir) ?></th>
                            <th><?= sortLink('trajanje_min',     'MIN',      $sort_col, $sort_dir) ?></th>
                            <th><?= sortLink('ocjena',           'OCJENA',   $sort_col, $sort_dir) ?></th>
                            <th><?= sortLink('rezisery',         'REDATELJ', $sort_col, $sort_dir) ?></th>
                            <th><?= sortLink('zemlja_porijekla', 'ZEMLJA',   $sort_col, $sort_dir) ?></th>
                            <th>AKCIJA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($filmovi)): ?>
                        <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">
                            Nema filmova koji odgovaraju filtru.
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($filmovi as $i => $film): ?>
                        <?php
                            $u_kosharici  = in_array($film['id'], $kosharica_ids);
                            $u_videoteci  = in_array($film['id'], $videoteka_ids);
                        ?>
                        <tr>
                            <td data-label="#"><?= $i + 1 ?></td>
                            <td data-label="Naslov" style="font-weight:500"><?= e($film['naslov']) ?></td>
                            <td data-label="Žanr"><?= zanrBadge($film['zanr']) ?></td>
                            <td data-label="Godina"><?= e($film['godina']) ?></td>
                            <td data-label="Min"><?= e($film['trajanje_min']) ?></td>
                            <td data-label="Ocjena"><span class="ocjena">★ <?= e($film['ocjena']) ?></span></td>
                            <td data-label="Redatelj"><?= e($film['rezisery']) ?></td>
                            <td data-label="Zemlja"><?= e($film['zemlja_porijekla']) ?></td>
                            <td data-label="Akcija">
                                <?php if ($u_videoteci): ?>
                                    <span class="in-videoteka">✓ U videoteci</span>
                                <?php elseif ($u_kosharici): ?>
                                    <button class="btn-table-remove btn-cart-remove"
                                            data-id="<?= $film['id'] ?>"
                                            data-naslov="<?= e($film['naslov']) ?>">
                                        − Ukloni
                                    </button>
                                <?php else: ?>
                                    <button class="btn-table-add btn-cart-add"
                                            data-id="<?= $film['id'] ?>"
                                            data-naslov="<?= e($film['naslov']) ?>"
                                            data-zanr="<?= e($film['zanr']) ?>"
                                            data-godina="<?= e($film['godina']) ?>"
                                            data-ocjena="<?= e($film['ocjena']) ?>">
                                        + Dodaj
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p style="color:var(--text-muted);font-size:0.8rem;margin-top:12px">
                Prikazano <?= count($filmovi) ?> filmova.
            </p>
        </section>

        <!-- Aside – filmske slike -->
        <aside aria-label="Galerija filmskih slika">
            <div class="aside-header">
                <h3>Istaknuti filmovi</h3>
            </div>
            <?php
            $poster_images = glob(__DIR__ . '/images/*.{jpg,jpeg,webp,png}', GLOB_BRACE);
            $posters = array_slice($poster_images, 0, 5);
            foreach ($posters as $img):
                $fname = basename($img);
                $title = ucwords(str_replace(['_', '-', '.webp', '.jpg', '.jpeg', '.png'], [' ', ' ', '', '', '', ''], $fname));
            ?>
            <div class="aside-slika-wrapper">
                <img src="images/<?= e($fname) ?>" alt="<?= e($title) ?>" loading="lazy">
            </div>
            <?php endforeach; ?>
            <?php if (empty($posters)): ?>
            <p style="color:var(--text-muted);font-size:0.82rem">Nema slika u /images/ mapi.</p>
            <?php endif; ?>
        </aside>
    </div>

    <!-- Košarica sidebar -->
    <aside id="cart-sidebar" role="dialog" aria-label="Košarica">
        <div class="cart-header">
            <h3>Košarica</h3>
            <button id="cart-close" onclick="toggleCart()" aria-label="Zatvori košaricu">✕</button>
        </div>
        <div id="cart-items">
            <?php if (empty($_SESSION['kosharica'])): ?>
            <p style="color:#aaa;padding:10px">Košarica je prazna.</p>
            <?php else: ?>
            <?php foreach ($_SESSION['kosharica'] as $idx => $item): ?>
            <div class="cart-item" data-id="<?= $item['id'] ?>">
                <strong><?= e($item['naslov']) ?></strong><br>
                <small><?= e($item['godina']) ?> | <?= e($item['zanr']) ?></small><br>
                <button class="remove-btn btn-cart-remove"
                        data-id="<?= $item['id'] ?>"
                        data-naslov="<?= e($item['naslov']) ?>">
                    Ukloni ✕
                </button>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div id="cart-footer" style="display:<?= empty($_SESSION['kosharica']) ? 'none' : 'block' ?>">
            <?php if (!isLoggedIn()): ?>
            <a href="login.php" class="add-btn" style="display:block;text-align:center;text-decoration:none;padding:12px">
                Prijavi se za potvrdu
            </a>
            <?php else: ?>
            <button id="confirm-booking" style="width:100%;padding:12px;background:#4caf50;color:#fff;border:none;cursor:pointer;font-weight:bold;border-radius:4px">
                POTVRDI POSUDBU
            </button>
            <?php endif; ?>
        </div>
    </aside>
</main>

<?php require_once 'includes/footer.php'; ?>

<script>
// ── Košarica – stanje iz PHP sessiona ────────────────────────────────────────
let cartCount = <?= count($_SESSION['kosharica']) ?>;

function toggleCart() {
    document.getElementById('cart-sidebar').classList.toggle('open');
}

document.getElementById('toggle-cart').addEventListener('click', toggleCart);

// ── Generički AJAX cart request ───────────────────────────────────────────────
async function cartRequest(action, filmId, filmData = {}) {
    const body = new URLSearchParams({ action, film_id: filmId, ...filmData,
                                       csrf_token: '<?= csrfToken() ?>' });
    const res  = await fetch('api/cart.php', { method: 'POST', body });
    return res.json();
}

// ── Dodaj u košaricu ─────────────────────────────────────────────────────────
document.querySelectorAll('.btn-cart-add').forEach(btn => {
    btn.addEventListener('click', async function () {
        const id     = this.dataset.id;
        const naslov = this.dataset.naslov;
        const zanr   = this.dataset.zanr;
        const godina = this.dataset.godina;
        const ocjena = parseFloat(this.dataset.ocjena);

        // Upozorenje za nisku ocjenu
        if (ocjena < 5.0) {
            if (!confirm(`Ovaj film ima nisku ocjenu (${ocjena}) – jeste li sigurni da ga želite dodati?`)) {
                return;
            }
        }

        const data = await cartRequest('add', id, { naslov, zanr, godina });
        if (data.success) {
            cartCount = data.count;
            document.getElementById('cart-count').textContent = cartCount;
            addCartItem({ id, naslov, zanr, godina });
            this.textContent = '− Ukloni';
            this.classList.replace('btn-cart-add', 'btn-cart-remove');
            this.classList.replace('btn-table-add', 'btn-table-remove');
            document.getElementById('cart-footer').style.display = 'block';
        } else {
            alert(data.message || 'Greška');
        }
    });
});

// ── Ukloni iz košarice ────────────────────────────────────────────────────────
document.addEventListener('click', async function (e) {
    const btn = e.target.closest('.btn-cart-remove');
    if (!btn) return;

    const id     = btn.dataset.id;
    const naslov = btn.dataset.naslov;
    const data   = await cartRequest('remove', id);

    if (data.success) {
        cartCount = data.count;
        document.getElementById('cart-count').textContent = cartCount;

        // Ukloni iz sidebara
        const sideItem = document.querySelector(`#cart-items .cart-item[data-id="${id}"]`);
        if (sideItem) sideItem.remove();

        // Vrati gumb u tablici
        const tableBtn = document.querySelector(`.tablica-filmovi .btn-cart-remove[data-id="${id}"]`);
        if (tableBtn) {
            tableBtn.textContent = '+ Dodaj';
            tableBtn.classList.replace('btn-cart-remove', 'btn-cart-add');
            tableBtn.classList.replace('btn-table-remove', 'btn-table-add');
        }

        if (cartCount === 0) {
            document.getElementById('cart-items').innerHTML = '<p style="color:#aaa;padding:10px">Košarica je prazna.</p>';
            document.getElementById('cart-footer').style.display = 'none';
        }
    }
});

// ── Dodaj item u sidebar košaricu (bez reload) ────────────────────────────────
function addCartItem({ id, naslov, zanr, godina }) {
    const empty = document.querySelector('#cart-items p');
    if (empty) empty.remove();

    const div = document.createElement('div');
    div.className = 'cart-item';
    div.dataset.id = id;
    div.innerHTML = `<strong>${naslov}</strong><br>
        <small>${godina} | ${zanr}</small><br>
        <button class="remove-btn btn-cart-remove" data-id="${id}" data-naslov="${naslov}">Ukloni ✕</button>`;
    document.getElementById('cart-items').appendChild(div);
}

// ── Potvrdi posudbu ───────────────────────────────────────────────────────────
const confirmBtn = document.getElementById('confirm-booking');
if (confirmBtn) {
    confirmBtn.addEventListener('click', async function () {
        const data = await cartRequest('confirm', 0);
        if (data.success) {
            alert(`Uspješno ste dodali ${data.saved} film(ova) u videoteku!`);
            location.reload();
        } else {
            alert(data.message || 'Greška pri potvrdi.');
        }
    });
}
</script>
