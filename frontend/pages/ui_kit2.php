<?php
/**
 * UI Kit 2 — только проба более светлой тёмной темы.
 * Канон по-прежнему ui_kit.php. Сюда не переносим живое приложение, пока не скажем «нравится».
 */
require __DIR__ . '/../partials/meta.php';

$kit2Url = 'https://dev.cabrioride.by/app/frontend/pages/ui_kit2.php';
$kit1Url = 'https://dev.cabrioride.by/app/frontend/pages/ui_kit.php';
?>
<!doctype html>
<html lang="ru" class="ui-kit ui-kit2-page">
  <head>
    <?php render_meta('UI Kit 2 — сумерки — CabrioRide'); ?>
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/styles.css'); ?>" />
    <link rel="stylesheet" href="../assets/css/ui_kit_page.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/ui_kit_page.css'); ?>" />
    <link rel="stylesheet" href="../assets/css/ui_kit2_theme.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/ui_kit2_theme.css'); ?>" />
  </head>
  <body>
    <div class="kit-shell">
      <header class="kit-hero">
        <h1>UI Kit 2</h1>
        <p>Новая тема «сумерки»: серо-синий вечер, тёмная карточка и песочные поля с тёмным текстом. Это не осветлённый kit 1.</p>
        <a class="kit-link" href="ui_kit.php">← обратно к эталону (kit 1)</a>
        <br>
        <a class="kit-link" href="<?php echo htmlspecialchars($kit2Url); ?>"><?php echo htmlspecialchars($kit2Url); ?></a>
      </header>

      <div class="kit-layout">
        <nav class="kit-toc" aria-label="Оглавление">
          <h2>Три модалки</h2>
          <a href="#k2-palette">Палитра</a>
          <a href="#k2-event">1. Событие — просмотр</a>
          <a href="#k2-event-edit">2. Событие — правка</a>
          <a href="#k2-guide">3. Место — просмотр</a>
          <div class="kit-toc-group">Эталон</div>
          <a href="<?php echo htmlspecialchars($kit1Url); ?>">UI Kit 1</a>
        </nav>

        <main class="kit-stage ui-kit2">
          <header class="kit-group-head" id="k2-palette">
            <span class="kit-group-kicker">Тема</span>
            <h2>Что изменилось</h2>
            <p>Другие цвета, не «плюс 20% яркости» к ночи. Если зайдёт — обсудим перенос в styles.css.</p>
          </header>
          <article class="kit-item">
            <div class="kit-demo">
              <div class="ui-kit2-palette">
                <div class="ui-kit2-swatch" style="background:#3d4754;color:#f4efe6">
                  <b>Небо / фон</b>
                  #3d4754 сумерки
                </div>
                <div class="ui-kit2-swatch" style="background:#2a303a;color:#f4efe6">
                  <b>Карточка</b>
                  #2a303a камень
                </div>
                <div class="ui-kit2-swatch" style="background:#efe6d4;color:#2a2622">
                  <b>Поле</b>
                  #efe6d4 песок, текст тёмный
                </div>
                <div class="ui-kit2-swatch" style="background:#2bb8a8;color:#14332f">
                  <b>Акцент</b>
                  #2bb8a8 море · золото #d4a05a
                </div>
              </div>
            </div>
          </article>

          <article class="kit-item" id="k2-event">
            <header class="kit-item-head">
              <span class="kit-num">1</span>
              <div>
                <h2>Событие — просмотр</h2>
                <p class="kit-used">Та же модалка, что п. 58 в kit 1</p>
              </div>
            </header>
            <div class="kit-demo">
              <div id="d2-event"></div>
            </div>
          </article>

          <article class="kit-item" id="k2-event-edit">
            <header class="kit-item-head">
              <span class="kit-num">2</span>
              <div>
                <h2>Событие — правка</h2>
                <p class="kit-used">Та же модалка, что п. 72 в kit 1</p>
              </div>
            </header>
            <div class="kit-demo">
              <p class="kit-note">Песочные поля, внутри при правке ещё светлее (#fff8ec).</p>
              <div id="d2-event-edit"></div>
            </div>
          </article>

          <article class="kit-item" id="k2-guide">
            <header class="kit-item-head">
              <span class="kit-num">3</span>
              <div>
                <h2>Место — просмотр</h2>
                <p class="kit-used">Та же модалка, что п. 59 в kit 1</p>
              </div>
            </header>
            <div class="kit-demo">
              <div id="d2-guide"></div>
            </div>
          </article>
        </main>
      </div>
    </div>
    <script type="module" src="../assets/js/ui_kit2_demo.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/ui_kit2_demo.js'); ?>"></script>
  </body>
</html>
