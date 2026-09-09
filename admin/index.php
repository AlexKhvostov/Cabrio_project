<?php
/**
 * Админка: пост от бота этой среды (токен из .env).
 * URL: /app/admin/  — в нижнее меню Mini App не ставится.
 */
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

$flash = '';
$flashErr = '';
$flashWarn = '';
$draftFile = __DIR__ . '/draft_club_post.html';
$draft = is_file($draftFile) ? (string)file_get_contents($draftFile) : '';

$userOk = admin_env('ADMIN_PANEL_USER');
$passOk = admin_env('ADMIN_PANEL_PASSWORD');
$chatIdDefault = admin_first_id(admin_env('CLUB_CHAT_ID'));
$chatNameEnv = admin_env('CLUB_CHAT_NAME');
$miniLink = admin_env('MINI_APP_LINK', 'https://t.me/CabrioRideBot/app');
$tokenRaw = trim(admin_env('BOT_TOKEN'));
$tokenMask = admin_mask_token($tokenRaw);
$testIdDefault = admin_first_id(admin_env('ADMIN_IDS'));
$presets = admin_post_presets();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_csrf_ok()) {
        $flashErr = 'Сессия устарела. Обновите страницу и войдите снова.';
    } elseif (($_POST['action'] ?? '') === 'login') {
        $_SESSION['tries'] = (int)($_SESSION['tries'] ?? 0) + 1;
        if ($_SESSION['tries'] > 8) {
            $flashErr = 'Слишком много попыток входа. Закройте вкладку и подождите.';
        } elseif ($userOk === '' || $passOk === '') {
            $flashErr = 'В .env этой среды не заданы ADMIN_PANEL_USER и ADMIN_PANEL_PASSWORD.';
        } else {
            $u = (string)($_POST['user'] ?? '');
            $p = (string)($_POST['pass'] ?? '');
            if (hash_equals($userOk, $u) && hash_equals($passOk, $p)) {
                $_SESSION['admin_ok'] = 1;
                $_SESSION['tries'] = 0;
                header('Location: index.php');
                exit;
            }
            $flashErr = 'Неверный логин или пароль.';
        }
    } elseif (($_POST['action'] ?? '') === 'logout') {
        $_SESSION = [];
        session_destroy();
        header('Location: index.php');
        exit;
    } elseif (($_POST['action'] ?? '') === 'send' && admin_logged_in()) {
        $now = time();
        $last = (int)($_SESSION['last_send'] ?? 0);
        if ($now - $last < 8) {
            $flashErr = 'Подождите несколько секунд перед следующей отправкой.';
        } else {
            $text = trim((string)($_POST['text'] ?? ''));
            $len = mb_strlen($text);
            $toMe = !empty($_POST['to_me']);
            $chatIdForm = admin_first_id((string)($_POST['chat_id'] ?? ''));
            $testIdForm = admin_first_id((string)($_POST['test_tg_id'] ?? ''));
            $target = $toMe ? $testIdForm : $chatIdForm;
            if ($text === '') {
                $flashErr = 'Введите текст поста.';
            } elseif ($len > 4096) {
                $flashErr = 'Слишком длинно: ' . $len . ' символов. Лимит Telegram — 4096.';
            } elseif ($toMe && $testIdForm === '') {
                $flashErr = 'Для отправки себе укажите ваш Telegram id (цифры).';
            } elseif (!$toMe && $chatIdForm === '') {
                $flashErr = 'Укажите id чата группы.';
            } else {
                try {
                    require_once dirname(__DIR__) . '/bot/services/BotService.php';
                    $bot = new BotService();
                    $extra = ['disable_web_page_preview' => true];
                    if (!empty($_POST['with_button']) && $miniLink !== '') {
                        $extra['reply_markup'] = json_encode([
                            'inline_keyboard' => [[
                                ['text' => 'Открыть приложение', 'url' => $miniLink],
                            ]],
                        ], JSON_UNESCAPED_UNICODE);
                    }
                    $bot->sendMessage($target, $text, 'HTML', $extra);
                    $_SESSION['last_send'] = $now;
                    $flash = $toMe
                        ? 'Сообщение ушло вам в личку (id ' . $testIdForm . ').'
                        : 'Сообщение ушло в группу (id ' . $chatIdForm . ').';
                } catch (Throwable $e) {
                    $flashErr = 'Telegram не принял сообщение: ' . $e->getMessage();
                }
            }
        }
    }
}

$botLabel = '';
$botUsername = admin_env('BOT_USERNAME');
$chatTitle = $chatNameEnv;
$formChatId = $chatIdDefault;
$formTestId = $testIdDefault;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'send') {
    $formChatId = admin_first_id((string)($_POST['chat_id'] ?? '')) ?: $chatIdDefault;
    $formTestId = admin_first_id((string)($_POST['test_tg_id'] ?? '')) ?: $testIdDefault;
}

if (admin_logged_in()) {
    try {
        require_once dirname(__DIR__) . '/bot/services/BotService.php';
        $bot = new BotService();
        $me = $bot->getMe();
        $u = (string)($me['result']['username'] ?? '');
        $fn = (string)($me['result']['first_name'] ?? '');
        if ($u !== '') {
            $botUsername = $u;
        }
        $botLabel = trim($fn . ($u !== '' ? ' @' . $u : ''));
        if ($u !== '' && strcasecmp($u, 'CabrioRideBot') !== 0) {
            $flashWarn = 'Сейчас бот этой среды — @' . $u . ', не @CabrioRideBot. В клубную группу на бою пишите только с боевого .env.';
        }
        if ($chatIdDefault !== '') {
            $chat = $bot->getChat($chatIdDefault);
            $got = (string)($chat['result']['title'] ?? '');
            if ($got !== '') {
                $chatTitle = $got;
            }
        }
    } catch (Throwable $e) {
        if ($flashErr === '') {
            $flashErr = 'Не удалось спросить Telegram о боте/чате: ' . $e->getMessage();
        }
    }
}

$postedText = $draft;
$presetFile = (string)($_GET['preset'] ?? '');
if ($presetFile !== '' && admin_logged_in()) {
    foreach ($presets as $p) {
        if ($p['file'] === $presetFile) {
            $postedText = $p['body'];
            break;
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'send') {
    $postedText = (string)($_POST['text'] ?? $postedText);
}
$toMeChecked = $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['to_me']);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Рассылка в клуб — CabrioRide</title>
  <style>
    <?php admin_shared_css(); ?>
    .wrap{max-width:720px;margin:0 auto;padding:24px 16px 48px}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Пост от бота</h1>
    <p class="muted">От имени бота этой среды (.env). Не Mini App участников — отдельный вход.</p>
    <?php admin_nav(); ?>

    <?php if ($flash): ?><p class="ok"><?php echo admin_h($flash); ?></p><?php endif; ?>
    <?php if ($flashErr): ?><p class="bad"><?php echo admin_h($flashErr); ?></p><?php endif; ?>
    <?php if ($flashWarn): ?><p class="warn"><?php echo admin_h($flashWarn); ?></p><?php endif; ?>

    <?php if (!admin_logged_in()): ?>
      <form class="card" method="post">
        <input type="hidden" name="csrf" value="<?php echo admin_h(admin_csrf()); ?>">
        <input type="hidden" name="action" value="login">
        <div class="row">
          <label for="user">Логин</label>
          <input id="user" name="user" autocomplete="username" required>
        </div>
        <div class="row">
          <label for="pass">Пароль</label>
          <input id="pass" name="pass" type="password" autocomplete="current-password" required>
        </div>
        <p class="limits">Логин и пароль — ADMIN_PANEL_USER / ADMIN_PANEL_PASSWORD в .env этой среды. Не путать с ролью admin в клубе.</p>
        <button class="btn btn-go" type="submit">Войти</button>
      </form>
    <?php else: ?>
      <form method="post" style="display:inline">
        <input type="hidden" name="csrf" value="<?php echo admin_h(admin_csrf()); ?>">
        <input type="hidden" name="action" value="logout">
        <button class="btn btn-ghost" type="submit">Выйти</button>
      </form>

      <form class="card" method="post" id="sendForm">
        <input type="hidden" name="csrf" value="<?php echo admin_h(admin_csrf()); ?>">
        <input type="hidden" name="action" value="send">

        <div class="grid-2">
          <div class="row">
            <label for="chat_id">Id чата группы</label>
            <input id="chat_id" name="chat_id" value="<?php echo admin_h($formChatId); ?>" inputmode="numeric" autocomplete="off">
            <?php if ($chatTitle !== ''): ?>
              <p class="limits" style="white-space:normal">Сейчас в .env: <?php echo admin_h($chatTitle); ?></p>
            <?php endif; ?>
          </div>
          <div class="row">
            <label for="bot_name">Имя бота (это среда)</label>
            <input id="bot_name" name="bot_name" value="<?php echo admin_h($botLabel !== '' ? $botLabel : ($botUsername !== '' ? '@' . $botUsername : 'не удалось узнать')); ?>" readonly>
          </div>
        </div>
        <div class="row">
          <label for="token_mask">Токен бота из .env (не целиком)</label>
          <input id="token_mask" value="<?php echo admin_h($tokenMask); ?>" readonly>
          <p class="limits" style="white-space:normal">Полный BOT_TOKEN в страницу не выводим. Меняется только в .env на этой среде.</p>
        </div>

        <div class="row">
          <label for="preset">Готовый текст</label>
          <div class="preset-row">
            <select id="preset">
              <option value="">— выбрать файл из admin/posts —</option>
              <?php foreach ($presets as $p): ?>
                <option value="<?php echo admin_h($p['file']); ?>"<?php echo $presetFile === $p['file'] ? ' selected' : ''; ?>><?php echo admin_h($p['title']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <p class="limits" style="white-space:normal">Файлы лежат в папке admin/posts. Новый шаблон — новый .html рядом.</p>
        </div>

        <div class="row">
          <label for="text">Текст сообщения</label>
          <textarea id="text" name="text" maxlength="5000" required><?php echo admin_h($postedText); ?></textarea>
          <p class="count" id="cnt"></p>
          <p class="limits">HTML, не больше 4096 символов. Можно: &lt;b&gt; &lt;i&gt; &lt;a href="…"&gt; &lt;code&gt;. Предпросмотр ссылок выключен. Повтор не раньше чем через 8 секунд.</p>
        </div>

        <label class="check-line">
          <input type="checkbox" name="with_button" value="1">
          <span>Кнопка «Открыть приложение» (ссылка MINI_APP_LINK)</span>
        </label>

        <label class="check-line" id="toMeLine">
          <input type="checkbox" name="to_me" id="to_me" value="1"<?php echo $toMeChecked ? ' checked' : ''; ?>>
          <span>Тест: отправить мне в личку, не в группу. Бот должен когда-то получить от вас /start.</span>
        </label>
        <div class="row" id="testIdRow">
          <label for="test_tg_id">Ваш Telegram id (для теста в личку)</label>
          <input id="test_tg_id" name="test_tg_id" value="<?php echo admin_h($formTestId); ?>" inputmode="numeric" autocomplete="off">
          <p class="limits" style="white-space:normal">По умолчанию первый id из ADMIN_IDS в .env.</p>
        </div>

        <div class="actions">
          <button class="btn btn-go" type="submit" id="sendBtn">Отправить в группу</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
  <script>
    const ta = document.getElementById('text')
    const cnt = document.getElementById('cnt')
    const toMe = document.getElementById('to_me')
    const sendBtn = document.getElementById('sendBtn')
    const preset = document.getElementById('preset')
    function paint(){
      if (!ta || !cnt) return
      const n = ta.value.length
      cnt.textContent = n + ' / 4096 символов'
      cnt.classList.toggle('is-over', n > 4096)
    }
    function paintTarget(){
      if (!sendBtn) return
      sendBtn.textContent = toMe?.checked ? 'Отправить себе в личку' : 'Отправить в группу'
    }
    ta?.addEventListener('input', paint)
    toMe?.addEventListener('change', paintTarget)
    preset?.addEventListener('change', () => {
      if (!preset.value) return
      location.href = 'index.php?preset=' + encodeURIComponent(preset.value)
    })
    paint()
    paintTarget()
  </script>
</body>
</html>
