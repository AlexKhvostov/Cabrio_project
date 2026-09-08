<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('События — CabrioRide'); ?>
    <link rel="stylesheet" href="<?php echo cabrio_asset_href('assets/css/styles.css'); ?>" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
  </head>
  <body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    <?php include __DIR__ . '/../components/nav.php'; ?>
    <main class="page">
      <section class="info-block empty-section">
        <h2>События</h2>
        <p style="margin:0">Поездки и встречи клуба появятся здесь. Раздел подключим по ТЗ — пока это вход в меню.</p>
      </section>
    </main>
    <script type="module" src="<?php echo cabrio_asset_href('assets/js/app.js'); ?>"></script>
    <script type="module">
      import '<?php echo cabrio_asset_href('assets/js/app.js'); ?>'
    </script>
  </body>
  </html>
