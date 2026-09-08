<?php
// Простая точка входа. Рендерим главную страницу.
require __DIR__ . '/partials/meta.php';
?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('CabrioRide'); ?>
    <link rel="stylesheet" href="<?php echo cabrio_asset_href('assets/css/styles.css'); ?>" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
  </head>
  <body>
    <?php include __DIR__ . '/components/header.php'; ?>
    <?php include __DIR__ . '/components/nav.php'; ?>
    <main class="page">
      <section class="home">
        <!-- Приветствие: имя из Telegram подставляется скриптом -->
        <header class="home-hero">
          <p class="home-kicker" id="statsTitle">CabrioRide</p>
          <h1 class="home-hello" id="welcome">Привет</h1>
          <p class="home-lead">Крыша открыта — можно заходить</p>
        </header>

        <div class="home-stats" id="stats">
          <div class="home-stat">
            <div class="stat-value" id="stat-members">—</div>
            <div class="stat-label">в клубе</div>
          </div>
          <div class="home-stat">
            <div class="stat-value" id="stat-cars">—</div>
            <div class="stat-label">кабриолетов</div>
          </div>
          <div class="home-stat">
            <div class="stat-value" id="stat-events">—</div>
            <div class="stat-label">встреч</div>
          </div>
          <div class="home-stat">
            <div class="stat-value" id="stat-cities">—</div>
            <div class="stat-label">городов</div>
          </div>
        </div>
        <p class="home-onmap" id="stat-onmap" hidden></p>

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

        <article class="card home-club home-club--story">
          <p class="home-club-kicker">про нас</p>
          <h2>Люди с поехавшей крышей</h2>
          <p class="home-club-lead">CabrioRide — клуб владельцев кабриолетов. Не лента для всех, а свои: кто на чём ездит, где сейчас катаются и когда следующая встреча.</p>
          <p>Минск и вся Беларусь. Ветер, маршруты, проверенные мойки и люди, которым можно махнуть фарами на трассе.</p>
          <div class="home-outs">
            <a class="home-out" href="https://cabrioride.by" target="_blank" rel="noopener">
              Сайт клуба
              <small>cabrioride.by</small>
            </a>
            <?php
              $invite = trim((string)(getenv('CHAT_INVITE_LINK') ?: 'https://t.me/Cabrio_Ride'));
              if ($invite !== '' && !preg_match('#^https?://#i', $invite)) {
                  $invite = 'https://' . ltrim($invite, '/');
              }
            ?>
            <a class="home-out" href="<?php echo htmlspecialchars($invite, ENT_QUOTES); ?>" target="_blank" rel="noopener">
              Чат в Telegram
              <small>живой разговор</small>
            </a>
          </div>
        </article>

        <p class="home-hint">В профиле добавь фото — в списке своих тебя сразу узнают.</p>
      </section>
    </main>
    <?php include __DIR__ . '/components/footer.php'; ?>
    <script type="module" src="<?php echo cabrio_asset_href('assets/js/app.js'); ?>"></script>
    <script type="module">
      import '<?php echo cabrio_asset_href('assets/js/app.js'); ?>'
      CabrioAPI.apiGet('/api/stats').then((s)=>{
        if (!s || s.success === false) return
        const d = s.data || {}
        const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = (v ?? '—') }
        set('stat-members', d.users)
        set('stat-cars', d.cars_active)
        set('stat-events', d.events)
        set('stat-cities', d.cities)
        const onMap = Number(d.on_map || 0)
        const onEl = document.getElementById('stat-onmap')
        if (onEl) {
          if (onMap > 0) {
            onEl.hidden = false
            onEl.textContent = onMap === 1 ? 'Сейчас на карте один свой' : `Сейчас на карте ${onMap} своих`
          } else {
            onEl.hidden = true
          }
        }
      }).catch(()=>{})

      // Приветствие по имени из Telegram (если доступно)
      try {
        const u = window.Telegram?.WebApp?.initDataUnsafe?.user
        if (u?.first_name) {
          const el = document.getElementById('welcome')
          if (el) el.textContent = `Привет, ${u.first_name}`
        }
      } catch {}

      document.querySelectorAll('.home-out').forEach((a)=>{
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


