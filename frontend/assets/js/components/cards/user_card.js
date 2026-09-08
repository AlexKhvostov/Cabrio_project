// Карточка участника: слева человек, справа стопка его машин (до 3, с нахлёстом)



import { phUser, phCar, liveTelegramPhotoUrl, selfAvatarFallbacks } from '../media.js?v=cabrio18'



function escapeHtml(str){

  return String(str||'').replace(/[&<>"']/g, s=>({

    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'

  }[s]))

}



/** 1–2 авто показываем отдельно; нахлёст нужен только для 3+, когда места уже мало. */

export function renderMemberCarStack(cars){

  if (!cars.length) return ''



  const visible = cars.slice(0, 3)

  const extra = cars.length - visible.length

  const count = visible.length



  const items = visible.map((car, i) => {

    const brand = car.brand?.name_ru || car.brand?.name || car.brand_name || ''

    const isFront = i === 0

    const title = brand || 'Открыть авто'

    return `<span class="member-car-stack-item" style="--stack-i:${i}" data-car-id="${escapeHtml(car.id)}" role="button" title="${escapeHtml(title)}">

      ${phCar(car, 'medium')}

      ${brand ? `<span class="member-car-stack-cap">${escapeHtml(brand)}</span>` : ''}

      ${isFront && extra > 0 ? `<span class="member-car-stack-more">+${extra}</span>` : ''}

    </span>`

  }).join('')



  return `<div class="member-cars"><div class="member-car-stack" data-count="${count}">${items}</div></div>`

}



export function renderUserCard(member, options = {}){

  const showCars = options.showCars !== false

  const firstName = member.first_name_app || member.first_name || member.first_name_tg || ''

  const lastName = member.last_name_app || member.last_name || member.last_name_tg || ''

  const initials = (firstName?.[0]||'') + (lastName?.[0]||'')

  const cars = Array.isArray(member.cars) ? member.cars : []

  const fullName = (`${firstName} ${lastName}`).trim() || 'Без имени'

  const roleLabel = (member.role && (member.role.name || member.role.code)) ? (member.role.name || member.role.code) : ''

  const city = (member.city && String(member.city).trim()) ? String(member.city).trim() : ''



  const carsHtml = (showCars && cars.length) ? renderMemberCarStack(cars) : ''
  const visibleBrands = cars.slice(0, 3)
    .map(car => car.brand?.name_ru || car.brand?.name || car.brand_name || '')
    .filter(Boolean)
  const carsSummary = (showCars && cars.length >= 3 && visibleBrands.length)
    ? `${visibleBrands.join(' · ')}${cars.length > 3 ? ` · +${cars.length - 3}` : ''}`
    : ''



  const metaBits = []

  if (member.username) metaBits.push('@' + member.username)

  if (city) metaBits.push(city)



  let avatarSrc = member
  try {
    const myTg = window.Telegram?.WebApp?.initDataUnsafe?.user?.id
    if (myTg && member.telegram_id && String(member.telegram_id) === String(myTg)) {
      avatarSrc = {
        ...member,
        telegram_photo_url: liveTelegramPhotoUrl() || member.telegram_photo_url,
        _fallbacks: selfAvatarFallbacks()
      }
    }
  } catch {}

  return `

  <div class="member-card" data-id="${member.id}">

    ${phUser(avatarSrc, initials, 'medium', true)}

    <div class="member-info">

      <div class="member-main">

        <h3 class="member-name">${escapeHtml(fullName)}</h3>

        ${roleLabel ? `<span class="role-badge">${escapeHtml(roleLabel)}</span>` : ''}

      </div>

      ${metaBits.length ? `<div class="member-meta">${escapeHtml(metaBits.join(' · '))}</div>` : ''}
      ${carsSummary ? `<div class="member-car-brands">${escapeHtml(carsSummary)}</div>` : ''}

    </div>

    ${carsHtml}

  </div>`

}


