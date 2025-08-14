// Инициализация Telegram WebApp (если доступен)
try {
  const tg = window.Telegram?.WebApp
  tg?.ready()
  tg?.expand()
  // Отключаем свайп-сворачивание Telegram, скролл оставляем внутри приложения
  tg?.disableVerticalSwipes?.()
  // Устанавливаем системные цвета для эффекта нативного приложения
  tg?.setHeaderColor?.('secondary_bg_color')
  tg?.setBackgroundColor?.('bg_color')

  // Фиксируем реальную высоту в CSS-переменной, чтобы занять весь экран
  const setAppHeight = (preferStable = true) => {
    const h = preferStable ? (tg?.viewportStableHeight || 0) : 0
    const height = (h || tg?.viewportHeight || window.innerHeight)
    document.documentElement.style.setProperty('--app-height', `${Math.round(height)}px`)
  }
  setAppHeight(true)
  tg?.onEvent?.('viewportChanged', ({ isStateStable }) => setAppHeight(isStateStable))
  // Экспорт обновления высоты для ручного вызова (например, после expand())
  window.CabrioUI = window.CabrioUI || {}
  window.CabrioUI.updateAppHeight = () => setAppHeight(true)
} catch {}

// Хелпер для загрузки данных с backend (с Telegram заголовками)
// Ожидается, что window.__API_URL указывает на корень backend, например: https://<host>/app/backend
const API_ROOT = (window.__API_URL || (window.location.origin + '/app/backend')).replace(/\/$/, '')

function buildTelegramHeaders(){
  const headers = {}
  try {
    const tg = window.Telegram?.WebApp
    const u = tg?.initDataUnsafe?.user || {}
    if (u.id) headers['X-Telegram-User-Id'] = String(u.id)
    if (u.first_name) headers['X-Telegram-First-Name'] = String(u.first_name)
    if (u.last_name) headers['X-Telegram-Last-Name'] = String(u.last_name)
    if (u.username) headers['X-Telegram-Username'] = String(u.username)
    if (tg?.initData) headers['X-Telegram-Init-Data'] = String(tg.initData)
  } catch {}
  return headers
}

async function apiGet(route){
  const url = `${API_ROOT}/routes/api.php?route=${encodeURIComponent(route)}`
  const res = await fetch(url, { headers: buildTelegramHeaders() })
  const data = await res.json().catch(()=>null)
  if (res.status === 401 || res.status === 403) return { __httpStatus: res.status, ...(data||{}) }
  return data
}

async function apiPost(route, payload){
  const url = `${API_ROOT}/routes/api.php?route=${encodeURIComponent(route)}`
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', ...buildTelegramHeaders() },
    body: JSON.stringify(payload || {})
  })
  const data = await res.json().catch(()=>null)
  if (res.status === 401 || res.status === 403) return { __httpStatus: res.status, ...(data||{}) }
  return data
}

// Справочники для фронта (можно расширять)
window.CabrioData = window.CabrioData || {}
// Простейший список брендов до подключения API справочников
async function loadRefCarBrands(){
  try{
    const res = await apiGet('/api/ref/car-brands')
    if (res && res.success && Array.isArray(res.data)) {
      window.CabrioData.carBrands = res.data
    }
  }catch{}
}
loadRefCarBrands()

window.CabrioAPI = { apiGet, apiPost }

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
      if (!url) return
      if (imgEl.src === url) return
      imgEl.onload = () => { try { wrapEl.style.display = ''; if (emojiEl) emojiEl.style.display = 'none' } catch {} }
      imgEl.src = url
    }

    // 0) Мгновенно из локального кэша (если есть)
    const cached = readCache()
    if (cached) {
      try { wrapEl.style.display = ''; if (emojiEl) emojiEl.style.display = 'none' } catch {}
      imgEl.src = cached
    } else {
      // Нет кэша — пока ничего не показываем (без заглушки), чтобы избежать мигания
      try { if (emojiEl) emojiEl.style.display = 'none' } catch {}
    }

    // 1) Обновляем из backend профиля (и обновляем кэш)
    ;(async () => {
      try {
        const me = await (window.CabrioAPI?.apiGet ? window.CabrioAPI.apiGet('/api/users/profile') : Promise.reject())
        const url = me?.data?.photo?.urls?.mini || me?.data?.photo?.url || null
        if (url && url !== cached) { show(url); writeCache(url, 6*60*60*1000) }
      } catch {
        // 2) Фолбэк: Telegram avatar (часовой TTL)
        try { const tg = window.Telegram?.WebApp?.initDataUnsafe?.user?.photo_url; if (tg && !cached) { const u = String(tg); show(u); writeCache(u, 60*60*1000) } } catch {}
      }
    })()
  } catch {}
})()

