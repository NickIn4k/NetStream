<?php
    include __DIR__ . '/includes/header.php';
?>

<section class="hero-main">
    <h1>
        NetStream&trade;
    </h1>
    <p>Emozioni in ogni click.</p>
</section>

<section class="catalogue-search">
    <h2>Esplora il nostro catalogo</h2>
    <?php include __DIR__ . '/includes/cercaCatalogo.php'; ?>
</section>

<section class="plans-main">
    <div class="plans">
        <div class="plan">
            <h2>Base</h2>
            <p>6.99€ / mese </p>
            <p>Qualità SD</p>
            <p>2 Profili </p>
        </div>

        <div class="plan">
            <h2>Medium</h2>
            <p>9.99€ / mese </p>
            <p>Qualità HD</p>
            <p>4 Profili </p>
        </div>

        <div class="plan">
            <h2>Pro</h2>
            <p>12.99€ / mese </p>
            <p>Qualità 4K</p>
            <p>6 Profili </p>
        </div>
    </div>
</section>

<?php
    include __DIR__ . '/includes/footer.php';
?>
