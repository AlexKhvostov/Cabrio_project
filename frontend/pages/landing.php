<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('CabrioRide — Для участников клуба'); ?>
    <link rel="stylesheet" href="<?php echo cabrio_asset_href('assets/css/styles.css'); ?>" />
  </head>
  <body>
    <main class="page landing-page">
      <div class="landing-stack">
        <!-- Тот же кадр, что на главной Mini App. Здесь нет меню — человек не внутри приложения -->
        <figure class="home-cover">
          <img src="<?php echo cabrio_asset_href('assets/img/home-cover.jpg'); ?>" alt="CabrioRide — клуб кабриолетов" width="640" height="360">
          <span class="home-cover-veil" aria-hidden="true"></span>
          <figcaption class="home-cover-text">
            <h1 class="home-hello">CabrioRide</h1>
            <p class="home-lead">Клуб кабриолетов. Приложение для своих.</p>
          </figcaption>
        </figure>

        <article class="card home-club">
          <p>Приложение открывается из Telegram: кнопка у бота @CabrioRideBot или ссылка в чате клуба. В браузере списки людей и машин не показываем.</p>
        </article>

        <a class="btn-primary" href="<?php echo htmlspecialchars(cabrio_chat_invite(), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Перейти в чат клуба</a>
        <p class="home-hint">Ещё не в клубе — заявку оставляют в чате.</p>
      </div>
    </main>
  </body>
</html>
