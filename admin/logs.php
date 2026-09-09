<?php
/**
 * Журнал действий людей в Mini App: кто, когда, что сделал.
 * Не путать с activity_logs (выдача «активности» между людьми).
 */
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
admin_require_login();

require_once dirname(__DIR__) . '/backend/utils/AppAudit.php';

$action = trim((string)($_GET['action'] ?? ''));
$q = trim((string)($_GET['q'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 80;
$offset = ($page - 1) * $per;

$rows = [];
$total = 0;
$tableMissing = false;
$errorMsg = '';

try {
    $pack = AppAudit::list([
        'action' => $action,
        'q' => $q,
    ], $per, $offset);
    $rows = $pack['rows'];
    $total = $pack['total'];
} catch (Throwable $e) {
    $msg = $e->getMessage();
    if (stripos($msg, 'app_audit_logs') !== false) {
        $tableMissing = true;
    } else {
        $errorMsg = $msg;
    }
}

$pages = max(1, (int)ceil($total / $per));
$minsk = new DateTimeZone('Europe/Minsk');

function admin_when_minsk(string $utc, DateTimeZone $minsk): string
{
    try {
        $dt = new DateTime($utc, new DateTimeZone('UTC'));
        $dt->setTimezone($minsk);
        return $dt->format('d.m.Y H:i');
    } catch (Throwable $e) {
        return $utc;
    }
}

$qs = static function (array $extra) use ($action, $q): string {
    $p = array_merge(['action' => $action, 'q' => $q], $extra);
    $p = array_filter($p, static fn($v) => $v !== '' && $v !== null);
    return '?' . http_build_query($p);
};
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Журнал приложения — CabrioRide</title>
  <style><?php admin_shared_css(); ?></style>
</head>
<body>
  <div class="wrap">
    <h1>Журнал приложения</h1>
    <p class="muted">Кто что сделал в Mini App: вход, разделы, создание, правка, удаление, «еду» на встрече. Время — Минск.</p>
    <?php admin_nav(); ?>

    <form method="post" action="index.php" style="display:inline">
      <input type="hidden" name="csrf" value="<?php echo admin_h(admin_csrf()); ?>">
      <input type="hidden" name="action" value="logout">
      <button class="btn btn-ghost" type="submit">Выйти</button>
    </form>

    <?php if ($tableMissing): ?>
      <div class="card">
        <p class="bad">Таблица журнала ещё не создана в этой базе.</p>
        <p>Запустите скрипт <code>database/scripts/2026-09-09_app_audit_logs.sql</code> в phpMyAdmin (локально и на тесте — свои базы). Бой не трогать без отдельной команды.</p>
      </div>
    <?php elseif ($errorMsg !== ''): ?>
      <p class="bad"><?php echo admin_h($errorMsg); ?></p>
    <?php else: ?>
      <form class="card filters" method="get">
        <div>
          <label for="action">Действие</label>
          <select id="action" name="action">
            <option value="">Все</option>
            <?php foreach (['login' => 'Вход', 'view' => 'Раздел', 'create' => 'Создание', 'update' => 'Правка', 'delete' => 'Удаление'] as $code => $label): ?>
              <option value="<?php echo admin_h($code); ?>"<?php echo $action === $code ? ' selected' : ''; ?>><?php echo admin_h($label); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="q">Поиск по имени или тексту</label>
          <input id="q" name="q" value="<?php echo admin_h($q); ?>" placeholder="Иван, встреча, авто…">
        </div>
        <div>
          <button class="btn btn-go" type="submit">Показать</button>
        </div>
      </form>

      <p class="muted">Записей: <?php echo (int)$total; ?></p>
      <div class="card" style="overflow:auto">
        <table class="log-table">
          <thead>
            <tr>
              <th>Когда</th>
              <th>Кто</th>
              <th>Роль</th>
              <th>Действие</th>
              <th>Что</th>
              <th>Подробно</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$rows): ?>
              <tr><td colspan="6" class="muted">Пока пусто — откройте Mini App или сделайте действие в нём.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?php echo admin_h(admin_when_minsk((string)$r['created_at'], $minsk)); ?></td>
                <td><?php echo admin_h((string)$r['actor_name']); ?></td>
                <td><?php echo admin_h(AppAudit::roleName((string)$r['actor_role'])); ?></td>
                <td><?php echo admin_h(AppAudit::actionName((string)$r['action'])); ?></td>
                <td><?php
                  $what = AppAudit::entityName((string)$r['entity_type']);
                  if (!empty($r['section'])) {
                      $what .= ' · ' . AppAudit::sectionName((string)$r['section']);
                  }
                  echo admin_h($what);
                ?></td>
                <td><?php echo admin_h((string)$r['summary']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if ($pages > 1): ?>
        <div class="pager">
          <?php if ($page > 1): ?>
            <a class="admin-nav-a" href="<?php echo admin_h($qs(['page' => $page - 1])); ?>">Назад</a>
          <?php endif; ?>
          <span class="muted">стр. <?php echo (int)$page; ?> из <?php echo (int)$pages; ?></span>
          <?php if ($page < $pages): ?>
            <a class="admin-nav-a" href="<?php echo admin_h($qs(['page' => $page + 1])); ?>">Дальше</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</body>
</html>
