// car_card.js — компактная карточка автомобиля

function escapeHtml(str){
  return String(str||'').replace(/[&<>"']/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

export function renderCarCard(car, options = {}){
  const showOwner = (options.showOwner !== false) && !!car.owner
  const title = `${car.brand?.name||''} ${car.model||''}`.trim()
  const photoUrl = car.photo?.url || ''
  const ownerAvatar = car.owner?.photo?.url || ''
  const ownerFirst = car.owner?.first_name_app || car.owner?.first_name || car.owner?.first_name_tg || ''
  const ownerLast = car.owner?.last_name_app || car.owner?.last_name || car.owner?.last_name_tg || ''
  const statusText = car.status?.name || car.status?.code || ''
  return `
  <div class="car-card-compact" data-id="${car.id}">
    <div class="car-image-container">
      ${photoUrl ? `<img src="${escapeHtml(photoUrl)}" class="car-image" alt="${escapeHtml(title)}"/>` : `<div class="car-placeholder">🚗</div>`}
    </div>
    <div class="car-info-compact">
      <h3 class="car-title-compact">${escapeHtml(title)}</h3>
      <div class="car-specs-compact">
        <span>${escapeHtml(String(car.year||'не задано'))}</span>
        <span> • ${escapeHtml(car.model || 'не задано')}</span>
      </div>
      ${statusText ? `<div class=\"car-status\" style=\"font-size:12px;color:#aaa\">Статус: ${escapeHtml(statusText)}</div>` : ''}
      ${showOwner ? `
      <div class="car-owner-compact">
        <div class="owner-avatar-small">${ownerAvatar ? `<img src="${escapeHtml(ownerAvatar)}" class="avatar-image" alt="${escapeHtml(ownerFirst)}"/>` : ''}</div>
        <span class="owner-name-compact">${car.owner?.username?`@${escapeHtml(car.owner.username)}`:'не задано'}</span>
      </div>` : ''}
    </div>
  </div>`
}


