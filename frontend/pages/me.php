<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('Профиль — CabrioRide'); ?>
    <link rel="stylesheet" href="<?php echo cabrio_asset_href('assets/css/styles.css'); ?>" />
  </head>
  <body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    <?php include __DIR__ . '/../components/nav.php'; ?>
    <main class="page">
      <div id="me" class="info-block" style="display:none">Загрузка профиля…</div>

      <!-- Та же карточка, что у участника в модалке: шапка с «Изменить», все поля на месте -->
      <section id="profile" class="sheet-card page-card" style="display:none;">
        <div class="sheet-head">
          <div class="sheet-head-title">Мой профиль</div>
          <div id="profileActions" class="sheet-actions"></div>
        </div>
        <div class="sheet-body">
          <div class="sheet-hero-wrap" id="profileHero">
            <div class="sheet-hero">
              <div class="sheet-hero-photo" id="profileAvatar"></div>
              <div class="sheet-hero-text">
                <div class="sheet-hero-name" id="profileName">&nbsp;</div>
                <div class="sheet-hero-meta" id="profileMeta"></div>
                <span id="role" class="role-badge" aria-live="polite"></span>
              </div>
            </div>
          </div>
          <div class="sheet-section-title">Основная информация</div>
          <div class="sheet-grid" id="profileFields"></div>
          <div class="sheet-section-title">Автомобили</div>
          <div class="sheet-links" id="cars-list"></div>
        </div>
      </section>

      <details id="debug-wrap" hidden style="margin-top:12px;">
        <summary>Диагностика (временная)</summary>
        <pre id="debug" style="white-space:pre-wrap;background:rgba(255,255,255,0.05);padding:8px;border-radius:8px;border:1px solid var(--border-color);"></pre>
        <div style="margin-top:8px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
          <button type="button" id="runNetTestBtn" class="btn-ghost">Тест соединения</button>
        </div>
        <pre id="netDebug" style="white-space:pre-wrap;background:rgba(255,255,255,0.05);padding:8px;border-radius:8px;border:1px solid var(--border-color);margin-top:8px;"></pre>
      </details>
    </main>
    <?php include __DIR__ . '/../components/footer.php'; ?>
    <script type="module" src="<?php echo cabrio_asset_href('assets/js/app.js'); ?>"></script>
    <script type="module">
      import '<?php echo cabrio_asset_href('assets/js/app.js'); ?>'
      import { initProfilePage } from '<?php echo cabrio_asset_href('assets/js/components/profile_view.js'); ?>'
      initProfilePage()
    </script>
  </body>
  </html>
