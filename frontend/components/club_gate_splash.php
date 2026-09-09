<?php
/**
 * Первый кадр Mini App: тёмный фон клуба, обложка и спиннер.
 * Пока JS не спросил Telegram, белого экрана нет.
 */
if (!function_exists('cabrio_frontend_url')) {
    require_once dirname(__DIR__) . '/partials/urls.php';
}
$cover = cabrio_asset_href('assets/img/home-cover.jpg');
?>
<div id="club-gate" class="club-gate" role="status" aria-live="polite" aria-busy="true">
  <div class="club-gate-card">
    <figure class="home-cover">
      <img src="<?php echo $cover; ?>" alt="" width="640" height="360">
      <span class="home-cover-veil" aria-hidden="true"></span>
    </figure>
    <div class="club-gate-body">
      <p class="home-club-kicker">CabrioRide</p>
      <h2>Открываем клуб</h2>
      <p>Проверяем, что вы в клубном чате.</p>
      <div class="club-gate-loader" aria-hidden="true"></div>
    </div>
  </div>
</div>
<script>
(function(){
  /* Кто уже прошёл проверку чата — сразу снимаем заставку, не ждём app.js */
  try {
    var raw = localStorage.getItem('cr:v5:club_member') || sessionStorage.getItem('cr:v5:club_member');
    if (!raw) return;
    var o = JSON.parse(raw);
    if (!o || !o.isMember) return;
    if (o.exp && Date.now() > o.exp) return;
    var box = document.getElementById('club-gate');
    if (box) box.remove();
  } catch (e) {}
})();
</script>
