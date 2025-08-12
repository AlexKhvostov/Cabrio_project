<?php
// Универсальный компонент блока фильтров для страниц списка
// Ожидает конфигурацию в переменной $FILTERS_CONFIG:
// [
//   'searchPlaceholder' => '...',
//   'filters' => [
//       ['id' => 'filter0', 'placeholder' => 'Все ...'],
//       ['id' => 'filter1', 'placeholder' => 'Все ...']
//   ],
//   'yearFilter' => true|false
// ]
// Опции для select обычно наполняются динамически на странице после загрузки данных

$cfg = $FILTERS_CONFIG ?? [];
$searchPlaceholder = $cfg['searchPlaceholder'] ?? 'Поиск...';
$filters = $cfg['filters'] ?? [];
$yearFilter = (bool)($cfg['yearFilter'] ?? false);
?>

<section class="filters-section">
  <!-- Поисковая строка -->
  <div class="search-bar">
    <span class="search-icon">🔎</span>
    <input id="filters-search" type="text" class="search-input" placeholder="<?php echo htmlspecialchars($searchPlaceholder); ?>" />
  </div>

  <!-- Ряд селектов и поле года -->
  <div class="filter-row">
    <?php if (!empty($filters)): ?>
      <?php foreach ($filters as $idx => $f): ?>
        <select id="<?php echo htmlspecialchars($f['id'] ?? ('filter'.$idx)); ?>" class="filter-select">
          <option value=""><?php echo htmlspecialchars($f['placeholder'] ?? 'Все'); ?></option>
          <!-- Опции добавляются на странице через JS после загрузки данных -->
        </select>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($yearFilter): ?>
      <input id="filters-year" type="number" class="filter-input" placeholder="Год" min="1950" max="2100" />
    <?php endif; ?>
  </div>
</section>


