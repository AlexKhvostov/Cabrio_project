// Высота экрана и отступ сверху. Считаем сразу, даже если Telegram ещё не ответил —
// иначе страница получается нулевой высоты и контент «пропадает».
function readCssPx(name) {
  const n = parseFloat(getComputedStyle(document.documentElement).getPropertyValue(name))
  return Number.isFinite(n) ? n : 0
}

function setAppHeight() {
  const tg = window.Telegram && window.Telegram.WebApp
  const rawH = (tg && (Number(tg.viewportStableHeight) || Number(tg.viewportHeight)))
    || window.innerHeight
    || document.documentElement.clientHeight
    || 700
  const height = Math.max(240, Math.round(rawH))
  document.documentElement.style.setProperty('--app-height', height + 'px')

  const inTelegram = !!(tg && (String(tg.initData || '').length || tg.initDataUnsafe?.user))
  const isFullscreen = !!(tg && tg.isFullscreen)
  document.documentElement.classList.toggle('tg-fs', isFullscreen)
  document.documentElement.classList.toggle('tg-webapp', inTelegram)

  // 1) часы и вырез экрана  2) кнопки Telegram «свернуть / закрыть»
  let statusTop = Number(tg?.safeAreaInset?.top) || readCssPx('--tg-safe-area-inset-top') || 0
  let headerTop = Number(tg?.contentSafeAreaInset?.top) || readCssPx('--tg-content-safe-area-inset-top') || 0

  // В Mini App место под шапку нужно всегда, не только после fullscreen.
  // Иначе приветствие и списки заезжают под системные кнопки.
  if (inTelegram) {
    if ((statusTop + headerTop) < 72) {
      if (statusTop < 16) statusTop = 36
      if (headerTop < 36) headerTop = 48
    }
  } else {
    statusTop = 0
    headerTop = 44
  }

  document.documentElement.style.setProperty('--safe-status', Math.round(statusTop) + 'px')
  document.documentElement.style.setProperty('--safe-header', Math.round(headerTop) + 'px')
  document.documentElement.style.setProperty('--safe-top', Math.round(statusTop + headerTop) + 'px')
}

setAppHeight()
window.CabrioUI = window.CabrioUI || {}
window.CabrioUI.updateAppHeight = setAppHeight

// Клавиатура: двигаем только когда поле в фокусе. Иначе Telegram WebView даёт ложный «зазор» и карточка схлопывается.
function applyKeyboardInset(){
  const el = document.activeElement
  const focused = !!(el && el.matches?.('input,textarea,select'))
  let kb = 0
  if (focused) {
    try {
      const vv = window.visualViewport
      if (vv) kb = Math.max(0, Math.round(window.innerHeight - vv.height - vv.offsetTop))
    } catch {}
    if (kb < 80) kb = 0
  }
  document.documentElement.style.setProperty('--kb', kb + 'px')
  if (focused && kb && el) {
    try { el.scrollIntoView({ block: 'nearest', inline: 'nearest' }) } catch {}
  }
}
try {
  window.visualViewport?.addEventListener('resize', applyKeyboardInset)
  window.visualViewport?.addEventListener('scroll', applyKeyboardInset)
} catch {}
document.addEventListener('focusin', (e)=>{
  const t = e.target
  if (!t || !t.matches?.('input,textarea,select')) return
  t.classList.add('field-focus')
  setTimeout(applyKeyboardInset, 50)
  setTimeout(applyKeyboardInset, 320)
})
document.addEventListener('focusout', (e)=>{ e.target?.classList?.remove('field-focus') })

let busyCount = 0
function ensureBusyEl(){
  let el = document.getElementById('pageBusy')
  if (el) return el
  el = document.createElement('div')
  el.id = 'pageBusy'
  el.className = 'page-busy'
  el.hidden = true
  el.innerHTML = '<div class="spinner"></div><span>Загрузка…</span>'
  document.body.appendChild(el)
  return el
}
window.CabrioUI.kickModalLayout = function(root){
  const body = root?.querySelector?.('.modal-body') || root
  if (!body) return
  try {
    body.style.minHeight = '120px'
    body.scrollTop = 1
    window.CabrioUI.updateAppHeight?.()
    requestAnimationFrame(() => { try { body.scrollTop = 0 } catch {} })
  } catch {}
}
window.CabrioBusy = {
  show(){
    busyCount++
    const el = ensureBusyEl()
    el.hidden = false
  },
  hide(){
    busyCount = Math.max(0, busyCount - 1)
    if (busyCount === 0) {
      const el = document.getElementById('pageBusy')
      if (el) el.hidden = true
    }
  }
}

// Подсказка раздела: кнопка i в шапке, окно поверх экрана, список не сдвигается
function bindSectionIntros(){
  const btn = document.querySelector('.app-hint-btn')
  const panel = document.getElementById('appHintPanel')
  if (!btn || !panel) return
  const apply = (open)=>{
    panel.hidden = !open
    btn.setAttribute('aria-expanded', open ? 'true' : 'false')
  }
  apply(false)
  btn.addEventListener('click', (e)=>{
    e.preventDefault()
    e.stopPropagation()
    apply(panel.hidden)
  })
  panel.querySelector('.app-hint-scrim')?.addEventListener('click', ()=> apply(false))
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bindSectionIntros)
} else {
  bindSectionIntros()
}

try {
  const tg = window.Telegram?.WebApp
  tg?.ready()
  tg?.expand()
  tg?.disableVerticalSwipes?.()
  try { tg?.setHeaderColor?.('#070b12') } catch {}
  try { tg?.setBackgroundColor?.('#070b12') } catch {}
  try { tg?.setBottomBarColor?.('#070b12') } catch {}

  const askFullscreen = () => {
    try {
      if (typeof tg.requestFullscreen === 'function' && !tg.isFullscreen) {
        tg.requestFullscreen()
      }
    } catch {}
  }
  askFullscreen()
  tg?.onEvent?.('viewportChanged', setAppHeight)
  tg?.onEvent?.('fullscreenChanged', setAppHeight)
  tg?.onEvent?.('safeAreaChanged', setAppHeight)
  tg?.onEvent?.('contentSafeAreaChanged', setAppHeight)
  document.addEventListener('touchend', askFullscreen, { once: true, passive: true })
  document.addEventListener('click', askFullscreen, { once: true })
} catch {}

// Хелпер для загрузки данных с backend (с Telegram заголовками)
// Ожидается, что window.__API_URL указывает на корень backend, например: https://<host>/app/backend
const API_ROOT = (window.__API_URL || (window.location.origin + '/app/backend')).replace(/\/$/, '')

function readTelegramUser(){
  try {
    const tg = window.Telegram?.WebApp
    const u = tg?.initDataUnsafe?.user || {}
    return {
      telegram_id: u?.id ? String(u.id) : undefined,
      first_name: u?.first_name ? String(u.first_name) : undefined,
      last_name: u?.last_name ? String(u.last_name) : undefined,
      username: u?.username ? String(u.username) : undefined,
    }
  } catch { return {} }
}

async function apiGet(route){
  const tgUser = readTelegramUser()
  const qp = new URLSearchParams()
  Object.entries(tgUser).forEach(([k,v])=>{ if (v !== undefined) qp.append(k, v) })
  const url = `${API_ROOT}/routes/api.php?route=${encodeURIComponent(route)}${qp.toString() ? ('&' + qp.toString()) : ''}`
  const res = await fetch(url, { headers: {} })
  const data = await res.json().catch(()=>null)
  if (res.status === 401 || res.status === 403) return { __httpStatus: res.status, ...(data||{}) }
  return data
}
  function getCached(key){ try{ return window.CabrioCache?.getWithTTL(key) }catch{ return null } }
  function setCached(key,val,ttl){ try{ return window.CabrioCache?.setWithTTL(key,val,ttl) }catch{ return null } }
  function readNavBarAvatarCache(){
    try{
      const raw = localStorage.getItem('cr:v1:me_avatar_mini');
      if (!raw) return null;
      const o = JSON.parse(raw);
      if (o && o.exp && Date.now() > o.exp) { try{ localStorage.removeItem('cr:v1:me_avatar_mini') }catch{}; return null }
      return (o && typeof o.url === 'string') ? o.url : null;
    }catch{ return null }
  }
  function getSelfAvatarUrl(){
    // 1) Используем тот же кэш, что и навбар
    const fromNav = readNavBarAvatarCache();
    if (fromNav) return fromNav;
    // 2) Резервный наш общий кэш
    const fromCache = getCached('photo:user:me:mini');
    if (fromCache) return fromCache;
    // 3) Фолбэк Telegram
    try{ const tgPhoto = window.Telegram?.WebApp?.initDataUnsafe?.user?.photo_url; if (tgPhoto) return String(tgPhoto) }catch{}
    return '';
  }
  window.getSelfAvatarUrl = getSelfAvatarUrl;
let mePromise = null
function getMe(){
  if (!mePromise) mePromise = apiGet('/api/users/profile')
  return mePromise
}
function invalidateMe(){
  mePromise = null
}

async function apiPost(route, payload){
  const tgUser = readTelegramUser()
  const url = `${API_ROOT}/routes/api.php?route=${encodeURIComponent(route)}`
  const body = Object.assign({}, payload || {}, tgUser)
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body)
  })
  const data = await res.json().catch(()=>null)
  if (res.status === 401 || res.status === 403) return { __httpStatus: res.status, ...(data||{}) }
  return data
}

// Снять себя с карты: тот же JSON с telegram_id, что и у POST
async function apiDelete(route, payload){
  const tgUser = readTelegramUser()
  const url = `${API_ROOT}/routes/api.php?route=${encodeURIComponent(route)}`
  const body = Object.assign({}, payload || {}, tgUser)
  const res = await fetch(url, {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body)
  })
  const data = await res.json().catch(()=>null)
  if (res.status === 401 || res.status === 403) return { __httpStatus: res.status, ...(data||{}) }
  return data
}

// Справочники для фронта (можно расширять)
window.CabrioData = window.CabrioData || {}
window.CabrioAPI = { apiGet, apiPost, apiDelete, getMe, invalidateMe }

// Переходы между полноценными карточками: человек ↔ его авто
async function ensureModalScript(kind){
  if (kind === 'car' && window.CabrioModals && typeof window.CabrioModals.openCarModal === 'function') return
  if (kind === 'user' && window.CabrioModals && typeof window.CabrioModals.openUserModal === 'function') return
  const file = kind === 'car' ? 'car_modal.js' : 'user_modal.js'
  const front = String(window.__FRONT_URL || '/app/frontend').replace(/\/$/, '')
  await import(`${front}/assets/js/modals/${file}?v=write2`)
}

window.CabrioNav = {
  closeModals(){
    document.querySelectorAll('.modal-overlay').forEach((el)=>{ try { el.remove() } catch {} })
  },
  async openCar(id){
    const n = Number(id)
    if (!n) return
    window.CabrioBusy?.show()
    try {
    const res = await apiGet('/api/cars/' + n)
    if (!res || res.success === false || !res.data) {
      alert((res && res.error && res.error.message) || 'Не удалось открыть автомобиль')
      return
    }
    await ensureModalScript('car')
    this.closeModals()
    if (window.CabrioModals && typeof window.CabrioModals.openCarModal === 'function') {
      window.CabrioModals.openCarModal(res.data)
    }
    } finally { window.CabrioBusy?.hide() }
  },
  async openUser(id){
    const n = Number(id)
    if (!n) return
    window.CabrioBusy?.show()
    try {
    const res = await apiGet('/api/users/' + n)
    if (!res || res.success === false || !res.data) {
      alert((res && res.error && res.error.message) || 'Не удалось открыть профиль')
      return
    }
    await ensureModalScript('user')
    this.closeModals()
    if (window.CabrioModals && typeof window.CabrioModals.openUserModal === 'function') {
      window.CabrioModals.openUserModal(res.data)
    }
    } finally { window.CabrioBusy?.hide() }
  }
}

// Если Telegram WebApp недоступен — перенаправим на заглушку (кроме самой заглушки)
;(function(){
  try{
    const inTg = !!(window.Telegram && window.Telegram.WebApp && window.Telegram.WebApp.initDataUnsafe && window.Telegram.WebApp.initDataUnsafe.user)
    const isLanding = /\/frontend\/pages\/landing\.php$/.test(window.location.pathname)
    const isUiKit = /\/frontend\/pages\/ui_kit\.php$/.test(window.location.pathname)
    if (!inTg && !isLanding && !isUiKit) {
      const front = String(window.__FRONT_URL || '/app/frontend').replace(/\/$/, '')
      window.location.replace(front + '/pages/landing.php')
    }
  }catch{}
})()

// Навигации активный пункт
function setActiveNav(){
  const path = window.location.pathname
  document.querySelectorAll('.bottom-nav .nav-item').forEach(a=>{
    const href = a.getAttribute('href')||''
    if (href && path.endsWith(href.replace(/^.*\//,''))) {
      a.classList.add('active')
    } else if (href && path.includes(href)) {
      a.classList.add('active')
    }
  })
}
setActiveNav()

// Аккуратная подстановка круглого аватара в навбаре с локальным кэшем (TTL)
;(function(){
  try {
    const wrapEl = document.querySelector('.nav-icon .nav-avatar-wrap')
    const imgEl = document.getElementById('navProfileAvatar')
    const emojiEl = document.getElementById('navProfileEmoji')
    if (!wrapEl || !imgEl) return

    const KEY = 'cr:v1:me_avatar_mini'
    const now = () => Date.now()
    const readCache = () => {
      try { const raw = localStorage.getItem(KEY); if(!raw) return null; const o = JSON.parse(raw); if (o && o.exp && now()>o.exp) { localStorage.removeItem(KEY); return null } return o?.url || null } catch { return null }
    }
    const writeCache = (url, ttlMs) => { try { localStorage.setItem(KEY, JSON.stringify({ url, exp: ttlMs ? now()+ttlMs : null })) } catch {} }
    const show = (url) => {
      if (!url) {
        try { if (emojiEl) { emojiEl.style.display = ''; } if (wrapEl) { wrapEl.style.display = 'none'; } } catch {}
        return
      }
      if (imgEl.src === url) return
      imgEl.onload = () => { try { wrapEl.style.display = ''; if (emojiEl) emojiEl.style.display = 'none' } catch {} }
      imgEl.onerror = () => { try { if (emojiEl) { emojiEl.style.display = ''; } if (wrapEl) { wrapEl.style.display = 'none'; } } catch {} }
      imgEl.src = url
    }

    window.CabrioUI = window.CabrioUI || {}
    window.CabrioUI.setNavAvatar = (url) => { show(url); writeCache(url, 6*60*60*1000) }

    // 0) Мгновенно из локального кэша (если есть)
    const cached = readCache()
    if (cached) {
      try { wrapEl.style.display = ''; if (emojiEl) emojiEl.style.display = 'none' } catch {}
      imgEl.src = cached
    } else {
      // Нет кэша — сразу показываем силуэт
      try { if (emojiEl) emojiEl.style.display = ''; if (wrapEl) wrapEl.style.display = 'none' } catch {}
    }

    // 1) Обновляем из backend профиля (и обновляем кэш)
    ;(async () => {
      try {
        const me = await (window.CabrioAPI?.getMe ? window.CabrioAPI.getMe() : Promise.reject())
        const url = me?.data?.photo?.urls?.medium || me?.data?.photo?.urls?.mini || me?.data?.photo?.url || me?.data?.telegram_photo_url || null
        if (url) { show(url); writeCache(url, 6*60*60*1000) }
      } catch {
        // 2) Фолбэк: Telegram avatar (часовой TTL)
        try { const tg = window.Telegram?.WebApp?.initDataUnsafe?.user?.photo_url; if (tg && !cached) { const u = String(tg); show(u); writeCache(u, 60*60*1000) } } catch {}
      }
    })()
  } catch {}
})()

