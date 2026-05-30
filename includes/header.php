<!doctype html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/lv4.css">
    <?php if (!empty($extra_css)): foreach ($extra_css as $css): ?>
    <link rel="stylesheet" href="styles/<?= e($css) ?>">
    <?php endforeach; endif; ?>
    <title><?= e($page_title ?? 'Filmoteka') ?></title>
</head>
<body>

<header>
    <div class="header-inner">
        <div class="logo">
            <h1><a href="index.php" style="text-decoration:none;color:inherit">Filmoteka</a></h1>
        </div>

        <div class="nav-wrapper">
            <label class="nav-toggle-label" for="nav-toggle" aria-label="Otvori izbornik">
                <span>☰</span> Izbornik
            </label>
            <input type="checkbox" id="nav-toggle" aria-hidden="true">
            <nav aria-label="Primarna navigacija">
                <ul class="nav-menu">
                    <li><a href="index.php"    <?= ($current_page??'') === 'index'    ? 'aria-current="page"' : '' ?>>Početna</a></li>
                    <li><a href="grafikon.php" <?= ($current_page??'') === 'grafikon' ? 'aria-current="page"' : '' ?>>Grafikoni</a></li>
                    <li><a href="galerija.php" <?= ($current_page??'') === 'galerija' ? 'aria-current="page"' : '' ?>>Galerija</a></li>
                    <?php if (isLoggedIn()): ?>
                    <li><a href="videoteka.php" <?= ($current_page??'') === 'videoteka' ? 'aria-current="page"' : '' ?>>Moja Videoteka</a></li>
                    <?php if (isAdmin()): ?>
                    <li><a href="dashboard.php" <?= ($current_page??'') === 'dashboard' ? 'aria-current="page"' : '' ?>>Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" style="color:var(--accent-red)">Odjava (<?= e($_SESSION['username'] ?? '') ?>)</a></li>
                    <?php else: ?>
                    <li><a href="login.php" <?= ($current_page??'') === 'login' ? 'aria-current="page"' : '' ?>>Prijava</a></li>
                    <li><a href="register.php" <?= ($current_page??'') === 'register' ? 'aria-current="page"' : '' ?>>Registracija</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
    <div class="header-strip"></div>
</header>
