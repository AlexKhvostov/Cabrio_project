// Общие картинки и заглушки для карточек.
// Если своё фото не открылось — пробуем следующее (оригинал, Telegram), потом подложку.

function escapeHtml(str){
  return String(str||'').replace(/[&<>"']/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

export function telegramPhotoUrl(src){
  if (!src || typeof src === 'string') return ''
  return String(src.telegram_photo_url || src.photo_url || '').trim()
}

export function liveTelegramPhotoUrl(){
  try { return String(window.Telegram?.WebApp?.initDataUnsafe?.user?.photo_url || '').trim() } catch { return '' }
}

// Какое фото показать. Mini 50px мылит аватарки — для экрана берём medium/оригинал.
export function photoUrl(src, size = 'medium'){
  if (!src) return ''
  if (typeof src === 'string') return src
  const p = src.photo || src
  if (typeof p === 'string') return p
  if (!p || typeof p !== 'object') {
    return src.telegram_photo_url || src.photo_url || ''
  }
  const urls = p.urls || {}
  const orig = p.url || ''
  if (size === 'mini') return urls.medium || urls.mini || orig || src.telegram_photo_url || ''
  if (size === 'orig') return orig || urls.orig || urls.medium || src.telegram_photo_url || src.photo_url || ''
  return urls[size] || urls.medium || orig || urls.mini || src.telegram_photo_url || src.photo_url || ''
}

function fallbackChain(src, main, size){
  if (!src || typeof src !== 'object') return []
  const p = src.photo || src
  const urls = (p && p.urls) || {}
  const orig = (urls.orig || p.url || '')
  const tg = telegramPhotoUrl(src)
  const list = []
  const add = (u) => {
    const s = String(u || '').trim()
    if (!s || s === main || list.includes(s)) return
    list.push(s)
  }
  add(urls.medium)
  add(orig)
  add(urls.mini)
  add(tg)
  const extra = Array.isArray(src._fallbacks) ? src._fallbacks : []
  extra.forEach(add)
  return list
}

export function selfAvatarFallbacks(){
  const out = []
  try {
    const img = document.getElementById('navProfileAvatar')
    if (img && img.naturalWidth > 0) out.push(img.currentSrc || img.src)
  } catch {}
  try {
    const raw = localStorage.getItem('cr:v1:me_avatar_mini')
    const u = raw ? JSON.parse(raw).url : ''
    if (u) out.push(u)
  } catch {}
  const live = liveTelegramPhotoUrl()
  if (live) out.push(live)
  return out
}

/* Человек без фото: нейтральная иллюстрация без лица, волос и гендерных признаков */
const USER_PH_SRC = new URL('../../img/ph-user.png?v=2', import.meta.url).href
const USER_PH = `<img class="ph-draw ph-user-art" src="${USER_PH_SRC}" alt="" decoding="async">`

/* Заглушка авто: иллюстрация «кабриолет под чехлом на выставке», не фото конкретной машины */
const CAR_PH_SRC = new URL('../../img/ph-car.png', import.meta.url).href
const CAR_PH = `<img class="ph-draw" src="${CAR_PH_SRC}" alt="" decoding="async">`

function photoSrcAttrs(src, size = 'medium'){
  const main = photoUrl(src, size)
  if (!main) return ''
  const p = (src && typeof src === 'object' && (src.photo || src)) || {}
  const orig = (p && p.urls && p.urls.orig) || p.url || ''
  // srcset 2x на битом orig в WebView глушит onerror — для аватара один src надёжнее
  const next = fallbackChain(src, main, size)
  const fb = next.length ? ` data-fallbacks="${escapeHtml(next.join('|'))}"` : ''
  return `src="${escapeHtml(main)}"${fb}`
}

function phBox(kind, urlAttrs, inner, eager, extraClass = ''){
  const load = eager ? '' : ' loading="lazy"'
  const img = urlAttrs
    ? `<img class="ph-img" ${urlAttrs} alt="" decoding="async"${load} onload="this.classList.add('ph-ok')" onerror="window.CabrioPh?window.CabrioPh.fail(this):(this.onerror=null,this.remove())">`
    : ''
  const extra = extraClass ? ` ${extraClass}` : ''
  return `<div class="ph ph-${kind}${extra}">${img}<span class="ph-fallback" aria-hidden="true">${inner}</span></div>`
}

window.CabrioPh = {
  fail(el){
    if (!el) return
    el.removeAttribute('srcset')
    const rest = String(el.getAttribute('data-fallbacks') || '').split('|').map(s => s.trim()).filter(Boolean)
    const next = rest.shift()
    if (next) {
      el.setAttribute('data-fallbacks', rest.join('|'))
      el.classList.remove('ph-ok')
      el.src = next
      return
    }
    el.onerror = null
    el.remove()
  }
}

// Человек без фото: градиент клуба + силуэт, поверх инициалы если есть имя
export function phUser(src, initials = '', size = 'medium', eager = false){
  const ini = escapeHtml(String(initials || '').replace(/\s+/g,'').slice(0, 2).toUpperCase())
  const inner = `${USER_PH}${ini ? `<span class="ph-ini">${ini}</span>` : ''}`
  return phBox('user', photoSrcAttrs(src, size), inner, eager, ini ? 'ph-has-ini' : '')
}

// Авто без фото: иллюстрация-заглушка (открытый верх виден по провисшему чехлу)
export function phCar(src, size = 'medium', eager = false){
  return phBox('car', photoSrcAttrs(src, size), CAR_PH, eager)
}
