<?php
/**
 * Полоска Telegram: CabrioRide всегда, под ним раздел.
 * Кнопка i — всплывающая подсказка, интерфейс под ней не разъезжается.
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
          <span class="hint-wrap">
            <button type="button" class="app-hint-btn" data-hint="<?php echo htmlspecialchars($hintId, ENT_QUOTES); ?>" aria-expanded="false" aria-label="О разделе">i</button>
            <span class="hint-pop" hidden><?php echo htmlspecialchars($hintText, ENT_QUOTES); ?></span>
          </span>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
