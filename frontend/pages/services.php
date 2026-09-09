<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('Отзывы — CabrioRide'); ?>
    <link rel="stylesheet" href="<?php echo cabrio_asset_href('assets/css/styles.css'); ?>" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
  </head>
  <body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    <?php include __DIR__ . '/../components/nav.php'; ?>
    <main class="page">
    <?php $FILTERS_CONFIG = [
      'searchPlaceholder' => 'Поиск по названию и ярлыкам...',
      'filters' => [
        ['id' => 'typeFilter', 'placeholder' => 'Все ярлыки']
      ]
    ]; include __DIR__ . '/../components/filters.php'; ?>
<div id="guideAccessBanner" class="info-block" style="margin-bottom:12px;display:none">
  <h3>Доступ к отзывам</h3>
  <p style="margin:0">Раздел открыт участникам клуба (роль member и выше).</p>
</div>
      <div id="guide" class="cars-grid"><div class="list-busy" style="grid-column:1/-1"><div class="spinner"></div>Загрузка…</div></div>
      <button type="button" id="addGuideFab" class="fab fab-add" hidden title="Добавить" aria-label="Добавить"><span class="fab-icon">+</span></button>
    </main>
    <script type="module" src="<?php echo cabrio_asset_href('assets/js/app.js'); ?>"></script>
    <script type="module" src="<?php echo cabrio_asset_href('assets/js/modals/guide_modal.js'); ?>"></script>
    <script src="<?php echo cabrio_asset_href('assets/js/list_debug.js'); ?>"></script>
    <script>
      if (window.CabrioListDebug) {
        CabrioListDebug.start({
          route: '/api/guide-objects',
          listId: 'guide',
          bannerId: 'guideAccessBanner',
          fabId: 'addGuideFab',
          kind: 'guide',
          modalUrl: <?php echo json_encode(html_entity_decode(cabrio_asset_href('assets/js/modals/guide_modal.js'), ENT_QUOTES, 'UTF-8'), JSON_UNESCAPED_SLASHES); ?>,
          cardUrl: <?php echo json_encode(html_entity_decode(cabrio_asset_href('assets/js/components/cards/guide_card.js'), ENT_QUOTES, 'UTF-8'), JSON_UNESCAPED_SLASHES); ?>
        })
      }
    </script>
  </body>
</html>
