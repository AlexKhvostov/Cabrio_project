// Общие куски больших карточек: поля, пустые значения, ссылка на связанный объект

import { phCar, phUser } from './media.js?v=cabrio18'

export function escapeHtml(str){
  return String(str||'').replace(/[&<>"']/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

export function filled(v){
  return v!==null && v!==undefined && String(v).trim()!==''
}

export function emptyMark(){
  return `<span class="sheet-empty">не указано</span>`
}

export function viewVal(v){
  return filled(v) ? `<span class="sheet-value">${escapeHtml(String(v))}</span>` : emptyMark()
}

export function sheetField(label, inner, extra=''){
  return `<div class="sheet-field ${extra}"><span class="sheet-label">${escapeHtml(label)}</span>${inner}</div>`
}

export function personName(u){
  if (!u) return ''
  const first = u.first_name_app || u.first_name_tg || u.first_name || ''
  const last = u.last_name_app || u.last_name_tg || u.last_name || ''
  return `${first} ${last}`.trim()
}

export function personIni(u){
  const first = u?.first_name_app || u?.first_name_tg || u?.first_name || ''
  const last = u?.last_name_app || u?.last_name_tg || u?.last_name || ''
  return (first?.[0]||'') + (last?.[0]||'')
}

export function carTitle(car){
  const brand = (car?.brand?.name || car?.brand_name || '').trim()
  const model = (typeof car?.model === 'string' ? car.model : '').trim()
  return `${brand} ${model}`.trim() || 'Автомобиль'
}

// Строка-ссылка на хозяина внутри карточки авто
export function renderPersonLink(user){
  if (!user || !user.id) return ''
  const name = personName(user) || 'Участник'
  const meta = [user.username ? '@'+user.username : '', user.city || ''].filter(Boolean).join(' · ') || 'открыть карточку'
  return `<button type="button" class="rel-link" data-user-id="${escapeHtml(user.id)}">
    ${phUser(user, personIni(user), 'orig', true)}
    <span class="rel-link-text">
      <span class="rel-link-title">${escapeHtml(name)}</span>
      <span class="rel-link-meta">${escapeHtml(meta)}</span>
    </span>
    <span class="rel-link-go" aria-hidden="true">›</span>
  </button>`
}

// Строка-ссылка на авто внутри карточки человека (не плитка из списка)
export function renderCarLink(car){
  if (!car || !car.id) return ''
  const title = carTitle(car)
  const meta = [car.year, car.color].filter(v => filled(v)).join(' · ') || 'открыть карточку'
  return `<button type="button" class="rel-link" data-car-id="${escapeHtml(car.id)}">
    ${phCar(car, 'orig', true)}
    <span class="rel-link-text">
      <span class="rel-link-title">${escapeHtml(title)}</span>
      <span class="rel-link-meta">${escapeHtml(String(meta))}</span>
    </span>
    <span class="rel-link-go" aria-hidden="true">›</span>
  </button>`
}

export function bindRelLinks(root){
  if (!root) return
  root.addEventListener('click', (e)=>{
    const carBtn = e.target.closest('[data-car-id]')
    if (carBtn && root.contains(carBtn)) {
      e.preventDefault(); e.stopPropagation()
      const id = carBtn.getAttribute('data-car-id')
      if (id && window.CabrioNav?.openCar) window.CabrioNav.openCar(id)
      return
    }
    const userBtn = e.target.closest('[data-user-id]')
    if (userBtn && root.contains(userBtn)) {
      e.preventDefault(); e.stopPropagation()
      const id = userBtn.getAttribute('data-user-id')
      if (id && window.CabrioNav?.openUser) window.CabrioNav.openUser(id)
    }
  })
}

export function headerActions({ canEdit, editing, withClose = true }){
  const close = withClose ? `<button class="modal-close" type="button" aria-label="close">×</button>` : ''
  if (!canEdit) return close
  if (editing) {
    return `<button type="button" class="btn-ghost" data-sheet-cancel>Отмена</button>
      <button type="button" class="btn-primary" data-sheet-save>Сохранить</button>
      ${close}`
  }
  return `<button type="button" class="btn-ghost" data-sheet-edit>Изменить</button>${close}`
}

// Telegram открывает личный чат только по публичному @username (не по числовому id)
export function tgUsername(user){
  const raw = String(user?.username || '').replace(/^@/, '').trim()
  if (!/^[A-Za-z0-9_]{3,32}$/.test(raw)) return ''
  return raw
}

export function isSameTelegramUser(member){
  try {
    const myTg = window.Telegram?.WebApp?.initDataUnsafe?.user?.id
    if (myTg && member?.telegram_id && String(member.telegram_id) === String(myTg)) return true
  } catch {}
  return false
}

export function openTelegramDialog(user){
  const name = tgUsername(user)
  if (!name) return false
  const url = 'https://t.me/' + name
  try {
    const tg = window.Telegram?.WebApp
    if (tg && typeof tg.openTelegramLink === 'function') {
      tg.openTelegramLink(url)
      return true
    }
  } catch {}
  try { window.open(url, '_blank', 'noopener') } catch {}
  return true
}

// Полноэкранный просмотр. Крестик ниже шапки Telegram.
// Клик по фото или тёмному фону закрывает. Стрелки только листают.
export function openPhotoViewer(photos, startIndex = 0){
  const urls = (Array.isArray(photos) ? photos : [photos]).map(p => {
    if (!p) return ''
    if (typeof p === 'string') return p
    return p.url || ''
  }).filter(Boolean)
  if (!urls.length) return
  let index = Math.min(Math.max(0, startIndex), urls.length - 1)
  const ov = document.createElement('div')
  ov.className = 'photo-viewer-overlay'
  const many = urls.length > 1
  ov.innerHTML = `
    <div class="photo-viewer-content">
      <button class="photo-viewer-close" type="button" aria-label="Закрыть">×</button>
      ${many ? `<button class="photo-viewer-nav photo-viewer-prev" type="button" aria-label="Назад">‹</button>` : ''}
      <img class="photo-viewer-img" src="${escapeHtml(urls[index])}" alt="фото"/>
      ${many ? `<button class="photo-viewer-nav photo-viewer-next" type="button" aria-label="Дальше">›</button>
        <div class="photo-viewer-counter">${index+1} / ${urls.length}</div>` : ''}
    </div>`
  document.body.appendChild(ov)
  const imgEl = ov.querySelector('.photo-viewer-img')
  const counterEl = ov.querySelector('.photo-viewer-counter')
  const closePv = ()=> ov.remove()
  const apply = ()=>{
    if (imgEl) imgEl.src = urls[index]
    if (counterEl) counterEl.textContent = `${index+1} / ${urls.length}`
  }
  ov.addEventListener('click', (e)=>{
    if (e.target.closest('.photo-viewer-nav')) return
    closePv()
  })
  ov.querySelector('.photo-viewer-prev')?.addEventListener('click', (e)=>{
    e.stopPropagation()
    index = (index - 1 + urls.length) % urls.length
    apply()
  })
  ov.querySelector('.photo-viewer-next')?.addEventListener('click', (e)=>{
    e.stopPropagation()
    index = (index + 1) % urls.length
    apply()
  })
}
