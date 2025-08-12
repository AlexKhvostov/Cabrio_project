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
const BASE = (window.VITE_BACKEND_API_URL || (window.location.origin + '/app')).replace(/\/$/, '')

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
  const url = `${BASE}/backend/routes/api.php?route=${encodeURIComponent(route)}`
  const res = await fetch(url, { headers: buildTelegramHeaders() })
  const data = await res.json().catch(()=>null)
  if (res.status === 401 || res.status === 403) return { __httpStatus: res.status, ...(data||{}) }
  return data
}

async function apiPost(route, payload){
  const url = `${BASE}/backend/routes/api.php?route=${encodeURIComponent(route)}`
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

