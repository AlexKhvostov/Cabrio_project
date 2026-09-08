// Карточка авто в сетке: фото или контур кабриолета, название на снимке, владелец снизу

import { phUser, phCar } from '../media.js?v=cabrio14'

function escapeHtml(str){
  return String(str||'').replace(/[&<>"']/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

export function renderCarCard(car, options = {}){
  const showOwner = (options.showOwner !== false) && !!car.owner
  const brandName = (car.brand?.name && String(car.brand.name).trim() !== '') ? car.brand.name : ''
  const modelName = (car.model && String(car.model).trim() !== '') ? car.model : ''
  const title = `${brandName} ${modelName}`.trim() || 'Автомобиль'
  const ownerFirst = car.owner?.first_name_app || car.owner?.first_name_tg || car.owner?.first_name || ''
  const ownerLast = car.owner?.last_name_app || car.owner?.last_name_tg || car.owner?.last_name || ''
  const ownerName = `${(ownerFirst||'').trim()} ${(ownerLast||'').trim()}`.trim()
  const ownerIni = (ownerFirst?.[0]||'') + (ownerLast?.[0]||'')
  const statusCode = (car.status?.code || '').toString().toLowerCase()
  const statusName = (car.status?.name || '').toString().toLowerCase().trim()
  const isActive = statusCode === 'active' || statusName === 'активен'
  const statusText = (!isActive && (car.status?.name || car.status?.code)) ? (car.status.name || car.status.code) : ''
  const yearText = car.year ? String(car.year) : ''

  return `
  <div class="car-card-compact" data-id="${car.id}">
    <div class="car-image-container">
      ${statusText ? `<div class="car-status-badge">${escapeHtml(statusText)}</div>` : ''}
      ${phCar(car, 'medium')}
      <div class="car-overlay-info">
        <div class="car-title-overlay">${escapeHtml(title)}</div>
        ${yearText ? `<div class="car-specs-overlay">${escapeHtml(yearText)}</div>` : ''}
      </div>
    </div>
    ${showOwner ? `
    <div class="car-owner-compact" data-owner-id="${escapeHtml(car.owner.id)}" role="button" title="Открыть владельца">
      ${phUser(car.owner, ownerIni, 'medium')}
      <span class="owner-name-compact">${escapeHtml(ownerName || 'Владелец не указан')}</span>
    </div>` : ''}
  </div>`
}
