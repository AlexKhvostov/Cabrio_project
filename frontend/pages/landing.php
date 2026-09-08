<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('CabrioRide — Для участников клуба'); ?>
    <link rel="stylesheet" href="<?php echo cabrio_asset_href('assets/css/styles.css'); ?>" />
  </head>
  <body>
    <main class="page" style="display:flex;align-items:center;justify-content:center;">
      <div class="card" style="max-width:520px;width:100%;text-align:center;display:flex;flex-direction:column;gap:12px;">
        <h2 style="margin:0">CabrioRide</h2>
        <p style="margin:0;color:#bbb">Это приложение для участников клуба кабриолетов.</p>
        <p style="margin:0;color:#bbb">Откройте приложение через Telegram, используя кнопку в закреплённом сообщении чата клуба.</p>
        <div class="divider" style="height:1px;background:var(--border-color);"></div>
        <?php
          // Ссылка на клубный чат из .env (CHAT_INVITE_LINK), иначе публичный username
          $invite = getenv('CHAT_INVITE_LINK') ?: 'https://t.me/Cabrio_Ride';
          $invite = trim($invite);
          if ($invite !== '' && !preg_match('#^https?://#i', $invite)) {
              $invite = 'https://' . ltrim($invite, '/');
          }
        ?>
        <a class="btn-primary" href="<?php echo htmlspecialchars($invite, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Перейти в чат Telegram</a>
        <p style="margin:0;font-size:12px;color:#888">Если вы ещё не участник, подайте заявку на вступление в чате.</p>
      </div>
    </main>
  </body>
  </html>


