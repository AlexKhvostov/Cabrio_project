// Высота экрана и отступ сверху. Считаем сразу, даже если Telegram ещё не ответил —
// иначе страница получается нулевой высоты и контент «пропадает».
import { bindHintPops } from './components/hints.js?v=tip2'

function readCssPx(name) {
  const n = parseFloat(getComputedStyle(document.documentElement).getPropertyValue(name))
  return Number.isFinite(n) ? n : 0
}

let lastFullHeight = 0

function isTypingField() {
  const el = document.activeElement
  return !!(el && el.matches?.('input,textarea,select'))
}

function lockPageScroll() {
  try { window.scrollTo(0, 0) } catch {}
  try {
    document.documentElement.scrollTop = 0
    document.body.scrollTop = 0
  } catch {}
}

function setAppHeight() {
  const tg = window.Telegram && window.Telegram.WebApp
  const stable = Number(tg?.viewportStableHeight) || 0
  const vis = Number(tg?.viewportHeight) || 0
  const win = window.innerHeight || document.documentElement.clientHeight || 700
  // Берём устойчивую высоту Mini App, не ту что Telegram даёт при открытой клавиатуре
  let height = Math.round(stable > 240 ? stable : Math.max(vis, win, 240))
  const typing = isTypingField()
  if (typing && lastFullHeight >= 240) height = lastFullHeight
  else if (lastFullHeight >= 240 && height < lastFullHeight - 80 && typing) height = lastFullHeight
  else lastFullHeight = height
  document.documentElement.style.setProperty('--app-height', height + 'px')

  const inTelegram = !!(tg && (String(tg.initData || '').length || tg.initDataUnsafe?.user))
  const isFullscreen = !!(tg && tg.isFullscreen)
  document.documentElement.classList.toggle('tg-fs', isFullscreen)
  document.documentElement.classList.toggle('tg-webapp', inTelegram)
  document.documentElement.classList.toggle('kb-open', typing)

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

// Клавиатура: приложение не сжимаем. Поле прокручиваем в верхнюю часть видимого окна.
function scrollFieldUp(el){
  if (!el || typeof el.getBoundingClientRect !== 'function') return
  try {
    const vv = window.visualViewport
    const visTop = vv ? vv.offsetTop : 0
    const visH = vv ? vv.height : window.innerHeight
    const want = visTop + Math.round(Math.max(48, visH * 0.16))
    const top = el.getBoundingClientRect().top
    const delta = top - want
    const box = el.closest('.modal-body') || el.closest('.page')
    if (box && Math.abs(delta) > 6) box.scrollTop += delta
  } catch {}
}

function applyKeyboardInset(){
  const focused = isTypingField()
  let pad = 0
  try {
    const vv = window.visualViewport
    if (focused && vv && lastFullHeight >= 240) {
      pad = Math.max(0, Math.round(lastFullHeight - vv.height - (vv.offsetTop || 0)))
    }
  } catch {}
  document.documentElement.style.setProperty('--kb', '0px')
  document.documentElement.style.setProperty('--kb-scroll-pad', pad + 'px')
  document.documentElement.classList.toggle('kb-open', focused)
  if (focused) lockPageScroll()
}
try {
  window.visualViewport?.addEventListener('resize', () => {
    applyKeyboardInset()
    if (isTypingField()) scrollFieldUp(document.activeElement)
  })
} catch {}
document.addEventListener('focusin', (e)=>{
  const t = e.target
  if (!t || !t.matches?.('input,textarea,select')) return
  t.classList.add('field-focus')
  setTimeout(() => { applyKeyboardInset(); scrollFieldUp(t) }, 50)
  setTimeout(() => { applyKeyboardInset(); scrollFieldUp(t) }, 320)
})
document.addEventListener('focusout', (e)=>{
  e.target?.classList?.remove('field-focus')
  setTimeout(() => { applyKeyboardInset(); setAppHeight() }, 60)
})

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
window.CabrioUI.busy = {
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
window.CabrioBusy = window.CabrioUI.busy

// Подсказка i: всплывашка у кнопки, экран не разъезжается
function bindSectionIntros(){
  bindHintPops(document)
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bindSectionIntros)
} else {
  bindSectionIntros()
}

try {
  const tg = window.Telegram?.WebApp
  try { window.__cabrioTgPaint?.() } catch {}
  tg?.disableVerticalSwipes?.()

  const askFullscreen = () => {
    try {
      const w = window.Telegram?.WebApp
      if (typeof w?.requestFullscreen === 'function' && !w.isFullscreen) {
        w.requestFullscreen()
      }
    } catch {}
  }
  // Не в первый кадр: иначе Telegram на секунду гасит экран серым.
  setTimeout(askFullscreen, 1400)
  tg?.onEvent?.('viewportChanged', () => {
    if (isTypingField()) {
      applyKeyboardInset()
      scrollFieldUp(document.activeElement)
      return
    }
    applyKeyboardInset()
    setAppHeight()
  })
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

async function membershipAllows(route){
  if (route === '/api/membership/check' || route === '/api/audit/client') return true
  return window.CabrioClubBlocked !== true
}

async function apiGet(route, extra = {}){
  if (!(await membershipAllows(route))) {
    return { __httpStatus: 403, success: false, error: { code: 'NOT_CLUB_MEMBER', message: 'Нет доступа к приложению' } }
  }
  const tgUser = readTelegramUser()
  const qp = new URLSearchParams()
  Object.entries(tgUser).forEach(([k,v])=>{ if (v !== undefined) qp.append(k, v) })
  Object.entries(extra || {}).forEach(([k,v])=>{ if (v !== undefined && v !== '') qp.append(k, String(v)) })
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
  if (!(await membershipAllows(route))) {
    return { __httpStatus: 403, success: false, error: { code: 'NOT_CLUB_MEMBER', message: 'Нет доступа к приложению' } }
  }
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

async function apiPatch(route, payload){
  if (!(await membershipAllows(route))) {
    return { __httpStatus: 403, success: false, error: { code: 'NOT_CLUB_MEMBER', message: 'Нет доступа к приложению' } }
  }
  const tgUser = readTelegramUser()
  const url = `${API_ROOT}/routes/api.php?route=${encodeURIComponent(route)}`
  const body = Object.assign({}, payload || {}, tgUser)
  const res = await fetch(url, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body)
  })
  const data = await res.json().catch(()=>null)
  if (res.status === 401 || res.status === 403) return { __httpStatus: res.status, ...(data||{}) }
  return data
}

// Снять себя с карты: тот же JSON с telegram_id, что и у POST
async function apiDelete(route, payload){
  if (!(await membershipAllows(route))) {
    return { __httpStatus: 403, success: false, error: { code: 'NOT_CLUB_MEMBER', message: 'Нет доступа к приложению' } }
  }
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
window.CabrioAPI = { apiGet, apiPost, apiPatch, apiDelete, getMe, invalidateMe }

// Журнал поведения в Mini App: открыл приложение и какой раздел. Каталог UI Kit не пишем.
function auditClientPage(){
  try {
    const path = String(location.pathname || '').replace(/\\/g, '/').toLowerCase()
    if (path.includes('ui_kit') || path.includes('landing')) return
    let section = 'home'
    if (path.includes('/pages/users')) section = 'users'
    else if (path.includes('/pages/cars')) section = 'cars'
    else if (path.includes('/pages/map')) section = 'map'
    else if (path.includes('/pages/events')) section = 'events'
    else if (path.includes('/pages/services')) section = 'guide'
    else if (path.includes('/pages/me')) section = 'me'
    let kind = 'view'
    try {
      if (!sessionStorage.getItem('cr:v1:audit_login')) {
        sessionStorage.setItem('cr:v1:audit_login', '1')
        kind = 'login'
      }
    } catch {}
    apiPost('/api/audit/client', { kind, section }).catch(() => {})
    if (kind === 'login') {
      apiPost('/api/audit/client', { kind: 'view', section }).catch(() => {})
    }
  } catch {}
}
setTimeout(auditClientPage, 1200)

// Переходы между полноценными карточками: человек ↔ его авто
async function ensureModalScript(kind){
  if (kind === 'car' && window.CabrioModals && typeof window.CabrioModals.openCarModal === 'function') return
  if (kind === 'user' && window.CabrioModals && typeof window.CabrioModals.openUserModal === 'function') return
  if (kind === 'event' && window.CabrioModals && typeof window.CabrioModals.openEventModal === 'function') return
  if (kind === 'guide' && window.CabrioModals && typeof window.CabrioModals.openGuideModal === 'function') return
  const file = ({ car:'car_modal.js', user:'user_modal.js', event:'event_modal.js', guide:'guide_modal.js' })[kind]
  const front = String(window.__FRONT_URL || '/app/frontend').replace(/\/$/, '')
  try {
    await import(`${front}/assets/js/modals/${file}?v=event72live`)
  } catch (err) {
    console.error('modal import', kind, err)
  }
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
  },
  async openEvent(id, onChanged){
    const n = Number(id)
    if (!n) return
    window.CabrioBusy?.show()
    try {
      const res = await apiGet('/api/events/' + n)
      if (!res || res.success === false || !res.data) {
        alert((res && res.error && res.error.message) || 'Не удалось открыть событие')
        return
      }
      await ensureModalScript('event')
      this.closeModals()
      if (window.CabrioModals && typeof window.CabrioModals.openEventModal === 'function') {
        window.CabrioModals.openEventModal(res.data, { onChanged })
      } else {
        alert('Карточка события не загрузилась. Обновите экран.')
      }
    } finally { window.CabrioBusy?.hide() }
  },
  async openGuide(id, onChanged){
    const n = Number(id)
    if (!n) return
    window.CabrioBusy?.show()
    try {
      const res = await apiGet('/api/guide-objects/' + n)
      if (!res || res.success === false || !res.data) {
        alert((res && res.error && res.error.message) || 'Не удалось открыть карточку')
        return
      }
      await ensureModalScript('guide')
      this.closeModals()
      if (window.CabrioModals && typeof window.CabrioModals.openGuideModal === 'function') {
        window.CabrioModals.openGuideModal(res.data, { onChanged })
      } else {
        alert('Карточка не загрузилась. Обновите экран.')
      }
    } finally { window.CabrioBusy?.hide() }
  }
}

// Заглушка landing — только если человек реально не в Mini App.
// Главная часто приходит с tgWebAppData в адресе, пункты меню — уже без него.
// Раньше async-скрипт Telegram не успевал, и меню ошибочно считалось «браузером».
;(function(){
  try{
    const isLanding = /\/frontend\/pages\/landing\.php$/.test(window.location.pathname)
    const isUiKit = /\/frontend\/pages\/ui_kit2?\.php$/.test(window.location.pathname)
    if (isLanding || isUiKit) return

    const front = String(window.__FRONT_URL || '/app/frontend').replace(/\/$/, '')
    const goLanding = () => { window.location.replace(front + '/pages/landing.php') }
    const hasUser = () => {
      try {
        const u = window.Telegram?.WebApp?.initDataUnsafe?.user
        return !!(u && u.id)
      } catch { return false }
    }
    const markInTg = () => { try { sessionStorage.setItem('cr:v1:in_tg', '1') } catch {} }
    const remembered = () => { try { return sessionStorage.getItem('cr:v1:in_tg') === '1' } catch { return false } }
    const fromTgUrl = /tgWebApp(Data|Version|Platform)=/.test(String(location.search) + String(location.hash))

    if (hasUser() || fromTgUrl) { markInTg(); return }
    if (remembered()) return

    let n = 0
    const tick = () => {
      if (hasUser() || fromTgUrl) { markInTg(); return }
      if (remembered()) return
      n += 1
      // SDK уже на странице, пользователя нет — открыли в обычном браузере
      if (window.Telegram?.WebApp && n >= 4 && !fromTgUrl) {
        goLanding()
        return
      }
      if (n > 40) {
        goLanding()
        return
      }
      setTimeout(tick, 50)
    }
    tick()
  }catch{}
})()

// Старт: на экране уже тёмный кадр клуба со спиннером (HTML). После проверки — приложение или заглушка.
;(function(){
  const path = window.location.pathname || ''
  if (/landing\.php$/.test(path) || /ui_kit2?\.php$/.test(path)) return

  const CACHE_KEY = 'cr:v5:club_member'
  const CACHE_MS = 30 * 60 * 1000
  const tgId = () => {
    try { return String(window.Telegram?.WebApp?.initDataUnsafe?.user?.id || '') } catch { return '' }
  }
  const readCache = () => {
    try {
      const raw = localStorage.getItem(CACHE_KEY) || sessionStorage.getItem(CACHE_KEY)
      if (!raw) return null
      const o = JSON.parse(raw)
      if (!o || String(o.telegramId) !== tgId()) return null
      if (o.exp && Date.now() > o.exp) return null
      return o
    } catch { return null }
  }
  const writeCache = (isMember) => {
    const pack = JSON.stringify({ telegramId: tgId(), isMember: !!isMember, exp: Date.now() + CACHE_MS })
    try { localStorage.setItem(CACHE_KEY, pack) } catch {}
    try { sessionStorage.setItem(CACHE_KEY, pack) } catch {}
  }
  const clearCache = () => {
    try { localStorage.removeItem(CACHE_KEY) } catch {}
    try { sessionStorage.removeItem(CACHE_KEY) } catch {}
  }

  const invite = String(window.__CHAT_INVITE || 'https://t.me/Cabrio_Ride').replace(/["<>]/g, '')
  const cover = String(window.__HOME_COVER || '').replace(/["<>]/g, '')
  let box = document.getElementById('club-gate')
  if (!box) {
    box = document.createElement('div')
    box.id = 'club-gate'
    box.className = 'club-gate'
    document.body.appendChild(box)
  }
  box.setAttribute('role', 'dialog')
  box.setAttribute('aria-modal', 'true')

  function ensureBox(){
    if (!box.parentNode) document.body.appendChild(box)
  }

  function bindInvite(){
    const link = box.querySelector('[data-club-invite]')
    link?.addEventListener('click', (e) => {
      const href = link.getAttribute('href')
      if (!href) return
      try {
        const tg = window.Telegram?.WebApp
        if (tg?.openTelegramLink) {
          e.preventDefault()
          tg.openTelegramLink(href)
        }
      } catch {}
    })
  }

  function card(kicker, title, text, actions){
    ensureBox()
    box.removeAttribute('aria-busy')
    box.innerHTML =
      '<div class="club-gate-card">' +
        (cover
          ? '<figure class="home-cover"><img src="' + cover + '" alt="" width="640" height="360"><span class="home-cover-veil" aria-hidden="true"></span></figure>'
          : '') +
        '<div class="club-gate-body">' +
          '<p class="home-club-kicker">' + kicker + '</p>' +
          '<h2>' + title + '</h2>' +
          '<p>' + text + '</p>' +
          actions +
        '</div>' +
      '</div>'
    bindInvite()
  }

  function showChecking(){
    card(
      'CabrioRide',
      'Открываем клуб',
      'Проверяем, что вы в клубном чате.',
      '<div class="club-gate-loader" aria-hidden="true"></div>'
    )
    box.setAttribute('aria-busy', 'true')
    box.setAttribute('role', 'status')
  }

  function openApp(){
    window.CabrioClubBlocked = false
    box.remove()
  }

  function showNotMember(){
    window.CabrioClubBlocked = true
    card(
      'клуб закрыт',
      'Для вас доступ закрыт',
      'Разделы открыты только участникам клубной группы. Вступите в чат и затем нажмите «Проверить снова».',
      '<div class="club-gate-actions">' +
        '<a class="btn-primary" data-club-invite href="' + invite + '" rel="noopener">Вступить в чат клуба</a>' +
        '<button class="btn-ghost" type="button" data-membership-retry>Проверить снова</button>' +
      '</div>'
    )
    box.querySelector('[data-membership-retry]')?.addEventListener('click', () => {
      clearCache()
      window.CabrioMembershipReady = checkMembership(true)
    })
  }

  function showRetry(){
    window.CabrioClubBlocked = true
    card(
      'проверка недоступна',
      'Не удалось проверить участие',
      'Не получили ответ от Telegram. Это не значит, что вас нет в группе — бот мог не достучаться до чата. Нажмите «Проверить снова».',
      '<button class="btn-primary" type="button" data-membership-retry>Проверить снова</button>'
    )
    box.querySelector('[data-membership-retry]')?.addEventListener('click', () => {
      clearCache()
      window.CabrioMembershipReady = checkMembership(true)
    })
  }

  async function checkMembership(force = false){
    if (force || !box.querySelector('.club-gate-loader')) showChecking()
    try {
      const result = await apiGet('/api/membership/check', force ? { force: '1' } : {})
      if (result?.success && result?.data?.is_member) {
        writeCache(true)
        openApp()
        return true
      }
      if (result?.success) {
        writeCache(false)
        showNotMember()
        return false
      }
      showRetry()
      return false
    } catch {
      showRetry()
      return false
    }
  }

  const cached = readCache()
  if (cached && cached.isMember) {
    openApp()
    window.CabrioMembershipReady = Promise.resolve(true)
    return
  }
  if (cached && cached.isMember === false) {
    showNotMember()
    window.CabrioMembershipReady = Promise.resolve(false)
    return
  }

  window.CabrioClubBlocked = false
  window.CabrioMembershipReady = checkMembership()
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

// Аватар в меню: сначала фото клуба, если не открылось — фото из Telegram
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
    const clearCache = () => { try { localStorage.removeItem(KEY) } catch {} }
    const liveTgPhoto = () => {
      try { return String(window.Telegram?.WebApp?.initDataUnsafe?.user?.photo_url || '') } catch { return '' }
    }
    const uniqUrls = (list) => {
      const out = []
      const seen = {}
      list.forEach((u) => {
        const s = String(u || '').trim()
        if (!s || seen[s]) return
        seen[s] = true
        out.push(s)
      })
      return out
    }
    const showPlaceholder = () => {
      try { if (emojiEl) emojiEl.style.display = ''; if (wrapEl) wrapEl.style.display = 'none' } catch {}
    }

    let queue = []
    let qIndex = 0
    let gen = 0

    const tryNext = (myGen) => {
      if (myGen !== gen) return
      if (qIndex >= queue.length) { showPlaceholder(); return }
      const url = queue[qIndex++]
      imgEl.onload = () => {
        if (myGen !== gen) return
        try { wrapEl.style.display = ''; if (emojiEl) emojiEl.style.display = 'none' } catch {}
        writeCache(url, 6*60*60*1000)
      }
      imgEl.onerror = () => {
        if (myGen !== gen) return
        if (readCache() === url) clearCache()
        tryNext(myGen)
      }
      imgEl.src = url
    }

    const loadQueue = (urls) => {
      const myGen = ++gen
      queue = uniqUrls(urls)
      qIndex = 0
      if (!queue.length) { showPlaceholder(); return }
      tryNext(myGen)
    }

    const urlsFromMe = (me) => {
      const d = me?.data || me || {}
      const p = d.photo || {}
      const u = p.urls || {}
      return [u.medium, u.orig, p.url, u.mini, d.telegram_photo_url, liveTgPhoto()]
    }

    window.CabrioUI = window.CabrioUI || {}
    window.CabrioUI.setNavAvatar = (url) => { loadQueue([url, liveTgPhoto()]) }

    const cached = readCache()
    if (cached) loadQueue([cached, liveTgPhoto()])
    else showPlaceholder()

    ;(async () => {
      try {
        const me = await (window.CabrioAPI?.getMe ? window.CabrioAPI.getMe() : Promise.reject())
        loadQueue(urlsFromMe(me))
      } catch {
        const tg = liveTgPhoto()
        if (tg) loadQueue([tg])
      }
    })()
  } catch {}
})()

