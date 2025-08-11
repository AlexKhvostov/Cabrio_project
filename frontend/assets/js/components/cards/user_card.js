// user_card.js — компактная карточка пользователя

function escapeHtml(str){
  return String(str||'').replace(/[&<>"']/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

export function renderUserCard(member, options = {}){
  const showCars = options.showCars !== false
  const firstName = member.first_name_app || member.first_name || member.first_name_tg || ''
  const lastName = member.last_name_app || member.last_name || member.last_name_tg || ''
  const initials = (firstName?.[0]||'').toUpperCase() + (lastName?.[0]||'').toUpperCase()
  const cars = Array.isArray(member.cars) ? member.cars : []
  const carsCount = cars.length
  const photoUrl = member.photo?.url || member.photo_url || ''
  const fullName = (`${firstName} ${lastName}`).trim() || 'не задано'

  const carsListHtml = carsCount ? `
    <div class="cars-mini-list">
      ${cars.slice(0,3).map(c=>{
        const carPhoto = c.photo?.urls?.mini || c.photo?.url || ''
        const plate = (c.reg_number===null || c.reg_number===undefined || String(c.reg_number)==='') ? '—' : String(c.reg_number)
        return `
          <div class="cars-mini-item">
            <div class="car-photo-mini">${carPhoto ? `<img src="${escapeHtml(carPhoto)}" class="car-mini-image" alt="car"/>` : ''}</div>
            <span class="car-info">${escapeHtml(plate)}</span>
          </div>`
      }).join('')}
      ${carsCount>3 ? `<span class="cars-count">+${carsCount-3}</span>`: ''}
    </div>
  ` : `<div class="member-car no-car"><div class="car-photo-mini no-car-icon"></div><span class="car-info">Нет автомобиля</span></div>`

  return `
  <div class="member-card" data-id="${member.id}">
    <div class="member-avatar">
      ${photoUrl ? `<img src="${escapeHtml(photoUrl)}" class="avatar-image" alt="${escapeHtml(firstName||'')}"/>` : `<span class=\"avatar-initials\">${escapeHtml(initials)}</span>`}
    </div>
    <div class="member-info">
      <div class="member-main">
        <h3 class="member-name">${escapeHtml(fullName)}</h3>
        ${member.username ? `<span class="member-nickname">@${escapeHtml(member.username)}</span>` : ''}
      </div>
      <div class="member-details">
        ${showCars ? carsListHtml : ''}
      </div>
    </div>
    <div class="member-actions">›</div>
  </div>`
}


