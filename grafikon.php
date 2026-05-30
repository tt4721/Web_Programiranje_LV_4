<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$page_title   = 'Grafikoni – Filmoteka';
$current_page = 'grafikon';
$extra_css    = ['grafikon.css'];
require_once 'includes/header.php';
?>

<main class="grafikon-container">
    <div class="page-intro">
        <div class="article-accent">STATISTIKA</div>
        <h2>Vizualizacija podataka</h2>
        <p>Distribucija filmova po žanru.</p>
    </div>

    <div class="grafikon-grid">
        <!-- PIE CHART -->
        <section class="grafikon-card" aria-label="Tortni grafikon distribucije po žanru">
            <div class="card-header">
                <h3>Distribucija po žanru</h3>
                <span class="card-badge">Kružni prikaz</span>
            </div>

            <div class="pie-wrap">
                <div class="pie-chart"
                     role="img"
                     aria-label="Kružni grafikon: Komedija 30%, Drama 25%, Akcija 15%, Ostalo 30%">
                    <div class="tooltip t1">Komedija 30%</div>
                    <div class="tooltip t2">Drama 25%</div>
                    <div class="tooltip t3">Akcija 15%</div>
                    <div class="tooltip t4">Ostalo 30%</div>
                    <div class="pie-hole"></div>
                </div>
            </div>

            <ul class="legenda" aria-label="Legenda grafikona">
                <li><span class="dot dot1"></span> Komedija <strong>30%</strong></li>
                <li><span class="dot dot2"></span> Drama <strong>25%</strong></li>
                <li><span class="dot dot3"></span> Akcija <strong>15%</strong></li>
                <li><span class="dot dot4"></span> Ostalo <strong>30%</strong></li>
            </ul>
        </section>

        <!-- BAR CHART -->
        <section class="grafikon-card" aria-label="Stupčasti grafikon distribucije po žanru">
            <div class="card-header">
                <h3>Usporedba žanrova</h3>
                <span class="card-badge">Stupčasti prikaz</span>
            </div>

            <div class="bar-chart"
                 role="img"
                 aria-label="Stupčasti grafikon: Komedija 30%, Drama 25%, Akcija 15%, Ostalo 30%">
                <div class="bar-y-axis">
                    <span>100%</span><span>75%</span><span>50%</span><span>25%</span><span>0%</span>
                </div>
                <div class="bar-area">
                    <div class="bar-group">
                        <div class="bar bar1" style="--value: 30%"><span class="bar-val">30%</span></div>
                        <span class="bar-label">Komedija</span>
                    </div>
                    <div class="bar-group">
                        <div class="bar bar2" style="--value: 25%"><span class="bar-val">25%</span></div>
                        <span class="bar-label">Drama</span>
                    </div>
                    <div class="bar-group">
                        <div class="bar bar3" style="--value: 15%"><span class="bar-val">15%</span></div>
                        <span class="bar-label">Akcija</span>
                    </div>
                    <div class="bar-group">
                        <div class="bar bar4" style="--value: 30%"><span class="bar-val">30%</span></div>
                        <span class="bar-label">Ostalo</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
