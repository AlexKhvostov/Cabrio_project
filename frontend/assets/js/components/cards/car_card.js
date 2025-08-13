// car_card.js — компактная карточка автомобиля

function escapeHtml(str){
  return String(str||'').replace(/[&<>"']/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

export function renderCarCard(car, options = {}){
  const showOwner = (options.showOwner !== false) && !!car.owner
  const brandName = (car.brand?.name && String(car.brand.name).trim() !== '') ? car.brand.name : 'марка'
  const modelName = (car.model && String(car.model).trim() !== '') ? car.model : 'модель'
  const title = `${brandName} ${modelName}`.trim()
  const photoUrl = car.photo?.urls?.medium || car.photo?.url || ''
  const ownerAvatar = car.owner?.photo?.urls?.mini || car.owner?.photo?.url || ''
  const ownerFirst = car.owner?.first_name_app || car.owner?.first_name || car.owner?.first_name_tg || ''
  const ownerLast = car.owner?.last_name_app || car.owner?.last_name || car.owner?.last_name_tg || ''
  const ownerName = `${(ownerFirst||'').trim()} ${(ownerLast||'').trim()}`.trim()
  const ownerUsername = car.owner?.username ? `@${car.owner.username}` : ''
  const statusText = car.status?.name || car.status?.code || ''
  const roofTypeName = (code) => {
    switch ((code || '').toString()) {
      case 'soft': return 'Мягкая'
      case 'hard': return 'Жёсткая'
      case 'targa': return 'Тарга'
      case 'none': return 'Нет'
      default: return code || ''
    }
  }
  const yearText = (car.year ? String(car.year) : 'не задано')
  const roofText = (car.roof_type ? roofTypeName(car.roof_type) : '')
  const volText = (car.engine_volume ? String(car.engine_volume) : '')
  const powerText = (car.engine_power ? String(car.engine_power) : '')
  const specs = [yearText, roofText, volText, powerText].filter(Boolean).map(escapeHtml).join(' • ')
  return `
  <div class="car-card-compact" data-id="${car.id}">
    <div class="car-image-container">
      ${statusText ? `<div class="car-status-badge">${escapeHtml(statusText)}</div>` : ''}
      ${photoUrl ? `<img src="${escapeHtml(photoUrl)}" class="car-image" alt="${escapeHtml(title)}"/>` : `<div class="car-placeholder">🚗</div>`}
      <div class="car-overlay-info">
        <div class="car-title-overlay">${escapeHtml(title)}</div>
        <div class="car-specs-overlay">${specs}</div>
      </div>
    </div>
    ${showOwner ? `
    <div class="car-owner-compact">
      <div class="owner-avatar-small">${ownerAvatar ? `<img src="${escapeHtml(ownerAvatar)}" class="avatar-image" alt="${escapeHtml(ownerFirst)}"/>` : ''}</div>
      <span class="owner-name-compact">${escapeHtml(ownerName || '')}${ownerUsername ? ` <span class="username">(${escapeHtml(ownerUsername)})</span>` : (!ownerName ? (ownerUsername || 'не задано') : '')}</span>
    </div>` : ''}
  </div>`
}


