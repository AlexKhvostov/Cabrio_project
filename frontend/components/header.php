<?php
/**
 * Полоска Telegram: CabrioRide всегда, под ним раздел.
 * Кнопка i открывает подсказку поверх экрана — список под ней не сдвигается.
 * Нажатия на пустую шапку не перехватываем — иначе не сработают «свернуть / закрыть».
 */
require_once __DIR__ . '/section_meta.php';
$section = cabrio_section_meta();
$pageSection = $section['title'] ?? '';
$hintText = $section['text'] ?? '';
$hintId = $section['id'] ?? '';
?>
<div class="app-topbar">
  <div class="app-topbar-inner<?php echo $pageSection !== '' ? ' has-section' : ''; ?>">
    <span class="app-topbar-name">Cabrio<span>Ride</span></span>
    <?php if ($pageSection !== ''): ?>
      <div class="app-topbar-section-row">
        <span class="app-topbar-section"><?php echo htmlspecialchars($pageSection, ENT_QUOTES); ?></span>
        <?php if ($hintText !== ''): ?>
          <button type="button" class="app-hint-btn" data-hint="<?php echo htmlspecialchars($hintId, ENT_QUOTES); ?>" aria-expanded="false" aria-controls="appHintPanel" aria-label="О разделе">i</button>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php if ($hintText !== ''): ?>
<div class="app-hint" id="appHintPanel" hidden>
  <button type="button" class="app-hint-scrim" aria-label="Закрыть подсказку"></button>
  <div class="app-hint-card">
    <p><?php echo htmlspecialchars($hintText); ?></p>
  </div>
</div>
<?php endif; ?>
