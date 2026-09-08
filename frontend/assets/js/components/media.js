// Общие картинки и заглушки для карточек.
// Если фото нет или оно не открылось — сразу видна красивая подложка, без ожидания сети.

function escapeHtml(str){
  return String(str||'').replace(/[&<>"']/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
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
  const srcset = orig && orig !== main ? ` srcset="${escapeHtml(main)} 1x, ${escapeHtml(orig)} 2x"` : ''
  return `src="${escapeHtml(main)}"${srcset}`
}

function phBox(kind, urlAttrs, inner, eager, extraClass = ''){
  const load = eager ? '' : ' loading="lazy"'
  const img = urlAttrs
    ? `<img class="ph-img" ${urlAttrs} alt="" decoding="async"${load} onerror="this.onerror=null;this.remove()">`
    : ''
  const extra = extraClass ? ` ${extraClass}` : ''
  return `<div class="ph ph-${kind}${extra}">${img}<span class="ph-fallback" aria-hidden="true">${inner}</span></div>`
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
