<?php
// Простая точка входа. Рендерим главную страницу.
require __DIR__ . '/partials/meta.php';
require_once __DIR__ . '/components/club_info.php';
?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('CabrioRide'); ?>
    <link rel="stylesheet" href="<?php echo cabrio_asset_href('assets/css/styles.css'); ?>" />
  </head>
  <body>
    <?php include __DIR__ . '/components/header.php'; ?>
    <?php include __DIR__ . '/components/nav.php'; ?>
    <main class="page">
      <section class="home">
        <!-- Обложка клуба. Тап по фото — отладка только у роли admin -->
        <figure class="home-cover" id="statsTitle">
          <img src="<?php echo cabrio_asset_href('assets/img/home-cover.jpg'); ?>" alt="CabrioRide — клуб кабриолетов" width="640" height="360">
          <span class="home-cover-veil" aria-hidden="true"></span>
          <figcaption class="home-cover-text">
            <h1 class="home-hello" id="welcome">Привет</h1>
            <p class="home-lead">Клуб владельцев кабриолетов</p>
          </figcaption>
        </figure>

        <div class="home-stats" id="stats">
          <a class="home-stat" href="<?php echo htmlspecialchars(cabrio_frontend_url('pages/users.php'), ENT_QUOTES); ?>">
            <div class="stat-value" id="stat-members">—</div>
            <div class="stat-label">в клубе</div>
          </a>
          <a class="home-stat" href="<?php echo htmlspecialchars(cabrio_frontend_url('pages/cars.php'), ENT_QUOTES); ?>">
            <div class="stat-value" id="stat-cars">—</div>
            <div class="stat-label">кабриолетов</div>
          </a>
          <a class="home-stat" href="<?php echo htmlspecialchars(cabrio_frontend_url('pages/events.php'), ENT_QUOTES); ?>">
            <div class="stat-value" id="stat-events">—</div>
            <div class="stat-label">встреч</div>
          </a>
          <a class="home-stat" href="<?php echo htmlspecialchars(cabrio_frontend_url('pages/services.php'), ENT_QUOTES); ?>">
            <div class="stat-value" id="stat-reviews">—</div>
            <div class="stat-label">отзывов</div>
          </a>
          <a class="home-stat" href="<?php echo htmlspecialchars(cabrio_frontend_url('pages/users.php'), ENT_QUOTES); ?>">
            <div class="stat-value" id="stat-cities">—</div>
            <div class="stat-label">городов</div>
          </a>
          <a class="home-stat" href="<?php echo htmlspecialchars(cabrio_frontend_url('pages/map.php'), ENT_QUOTES); ?>">
            <div class="stat-value" id="stat-onmap">—</div>
            <div class="stat-label">на карте</div>
          </a>
        </div>

        <!-- Скрытая панель отладки. Админ: тап по слову CabrioRide -->
        <div id="debugPanel" class="card" style="display:none; margin-top:12px; padding:0;">
          <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border-bottom:1px solid rgba(255,255,255,0.08);">
            <div style="font-weight:600;">Debug</div>
            <div>
              <button id="dbgCopyBtn" class="btn-secondary" type="button" style="margin-right:8px;">Скопировать</button>
              <button id="dbgClearBtn" class="btn-secondary" type="button" style="margin-right:8px;">Очистить</button>
              <button id="dbgCloseBtn" class="btn-secondary" type="button">Свернуть</button>
            </div>
          </div>
          <pre id="debugLog" style="margin:0; padding:10px 12px; max-height:240px; overflow:auto; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; font-size:12px; line-height:1.4; white-space:pre-wrap; word-break:break-word; background:rgba(0,0,0,0.35);"></pre>
        </div>

        <article class="card home-club">
          <p class="home-club-kicker">про нас</p>
          <h2>Люди с поехавшей крышей</h2>
          <p class="home-club-lead">Не лента для всех, а свои: кто на чём ездит, где катаются и когда следующая встреча. Минск и вся Беларусь.</p>
          <div class="home-outs">
            <a class="home-out" href="https://cabrioride.by" target="_blank" rel="noopener">
              Сайт клуба
              <small>cabrioride.by</small>
            </a>
            <a class="home-out" href="<?php echo htmlspecialchars(cabrio_chat_invite(), ENT_QUOTES); ?>" target="_blank" rel="noopener">
              Чат в Telegram
              <small>живой разговор</small>
            </a>
          </div>
        </article>

        <div class="home-hints">
          <p class="home-hint">Своё фото в профиле — и в списке участников вас сразу узнают.</p>
          <p class="home-hint" id="homeHintCar">
            <a href="<?php echo htmlspecialchars(cabrio_frontend_url('pages/me.php'), ENT_QUOTES); ?>">В профиле можно добавить свой авто, если его там ещё нет.</a>
          </p>
        </div>
        <?php cabrio_home_info_block(); ?>
      </section>
    </main>
    <?php include __DIR__ . '/components/footer.php'; ?>
    <?php cabrio_club_info_store(); ?>
    <script type="module" src="<?php echo cabrio_asset_href('assets/js/app.js'); ?>"></script>
    <script type="module">
      import '<?php echo cabrio_asset_href('assets/js/app.js'); ?>'
      import { bindClubInfoTiles } from '<?php echo cabrio_asset_href('assets/js/modals/club_info_modal.js'); ?>'
      bindClubInfoTiles(document)
      CabrioAPI.apiGet('/api/stats').then((s)=>{
        if (!s || s.success === false) return
        const d = s.data || {}
        const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = (v ?? '—') }
        set('stat-members', d.users)
        set('stat-cars', d.cars_active)
        set('stat-events', d.events)
        set('stat-reviews', d.reviews)
        set('stat-cities', d.cities)
        set('stat-onmap', d.on_map ?? 0)
      }).catch(()=>{})

      // Строка про авто — только если в профиле ещё нет машины
      CabrioAPI.getMe().then((me)=>{
        const cars = me && me.data && me.data.cars
        if (Array.isArray(cars) && cars.length) {
          document.getElementById('homeHintCar')?.setAttribute('hidden', '')
        }
      }).catch(()=>{})
      try {
        const u = window.Telegram?.WebApp?.initDataUnsafe?.user
        if (u?.first_name) {
          const el = document.getElementById('welcome')
          if (el) el.textContent = `Привет, ${u.first_name}`
        }
      } catch {}

      document.querySelectorAll('a.home-out').forEach((a)=>{
        a.addEventListener('click', (e)=>{
          const href = a.getAttribute('href')
          if (!href) return
          const tg = window.Telegram?.WebApp
          try {
            if (/t\.me\//i.test(href) && tg?.openTelegramLink) { e.preventDefault(); tg.openTelegramLink(href); return }
            if (tg?.openLink) { e.preventDefault(); tg.openLink(href) }
          } catch {}
        })
      })

      // Панель отладки только для администратора (роль admin)
      (function(){
        try{
          const panel = document.getElementById('debugPanel')
          const logEl = document.getElementById('debugLog')
          const title = document.getElementById('statsTitle')
          const btnClose = document.getElementById('dbgCloseBtn')
          const btnClear = document.getElementById('dbgClearBtn')
          const btnCopy = document.getElementById('dbgCopyBtn')
          if (!panel || !logEl || !title) return

          const enableDebug = () => {
            title.style.cursor = 'pointer'
            title.title = 'Отладка (только админ)'

            const ts = () => new Date().toISOString().replace('T',' ').replace('Z','')
            const write = (level, args) => {
              try{
                const line = `[${ts()}] ${level}: ` + args.map(a=>{
                  try{
                    if (typeof a === 'string') return a
                    return JSON.stringify(a)
                  }catch{ return String(a) }
                }).join(' ')
                logEl.textContent += (logEl.textContent ? '\n' : '') + line
                logEl.scrollTop = logEl.scrollHeight
              }catch{}
            }

            const orig = { log: console.log, warn: console.warn, error: console.error, info: console.info }
            console.log = (...a)=>{ try{ orig.log.apply(console, a) }catch{} write('log', a) }
            console.warn = (...a)=>{ try{ orig.warn.apply(console, a) }catch{} write('warn', a) }
            console.error = (...a)=>{ try{ orig.error.apply(console, a) }catch{} write('error', a) }
            console.info = (...a)=>{ try{ orig.info.apply(console, a) }catch{} write('info', a) }

            window.CabrioDebug = {
              show(){ try{ panel.style.display = '' }catch{} },
              hide(){ try{ panel.style.display = 'none' }catch{} },
              toggle(){ try{ panel.style.display = (panel.style.display==='none' || !panel.style.display)? '' : 'none' }catch{} },
              log: (...a)=>write('log', a)
            }

            title.addEventListener('click', ()=> window.CabrioDebug?.toggle?.())
            btnClose?.addEventListener('click', ()=> window.CabrioDebug?.hide?.())
            btnClear?.addEventListener('click', ()=>{ try{ logEl.textContent = '' }catch{} })
            btnCopy?.addEventListener('click', async ()=>{
              try{
                const txt = logEl.textContent || ''
                if (!txt) { write('info', ['Copy','nothing to copy']); return }
                if (navigator.clipboard?.writeText) {
                  await navigator.clipboard.writeText(txt)
                  write('info', ['Copied to clipboard'])
                } else {
                  const ta = document.createElement('textarea')
                  ta.value = txt
                  ta.style.position = 'fixed'
                  ta.style.top = '-1000px'
                  document.body.appendChild(ta)
                  ta.focus(); ta.select()
                  try { document.execCommand('copy'); write('info', ['Copied to clipboard (fallback)']) } finally { document.body.removeChild(ta) }
                }
              }catch(e){ write('error', ['Copy failed', String(e)]) }
            })

            try{
              const tg = window.Telegram?.WebApp
              const user = tg?.initDataUnsafe?.user || null
              write('info', ['Env', {
                location: window.location.href,
                api_url: window.__API_URL || null,
                base_url: window.__BASE_URL || null,
                tg_present: !!user,
                tg_user_id: user?.id || null,
                tg_username: user?.username || null,
                in_iframe: window.self !== window.top
              }])
            }catch{}
          }

          CabrioAPI.apiGet('/api/users/profile').then((me)=>{
            const role = (me?.data?.role && (me.data.role.code || me.data.role)) || ''
            if (String(role).toLowerCase() === 'admin') enableDebug()
          }).catch(()=>{})
        }catch{}
      })()
    </script>
  </body>
  </html>


