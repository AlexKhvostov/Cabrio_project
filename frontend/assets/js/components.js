// Простые компоненты карточек и модалок (адаптация из Vue-компонентов)

function escapeHtml(str){
  return String(str||'').replace(/[&<>"']/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

// Импорт карточек как локальные символы и далее публикуем в window
import { renderUserCard as renderMemberCard } from '/app/frontend/assets/js/components/cards/user_card.js?v=3'

export function openMemberModal(member){
  const overlay = document.createElement('div')
  overlay.className = 'modal-overlay'
  const photoUrl = member.photo?.urls?.medium || member.photo?.url || member.photo_url || ''
  overlay.innerHTML = `
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Профиль</div>
        <button class="modal-close" aria-label="close">×</button>
      </div>
      <div class="modal-body">
        <div class="member-profile" style="display:flex;align-items:center;gap:12px;">
          <div class="profile-avatar-compact">
            ${photoUrl ? `<img src="${escapeHtml(photoUrl)}" class="avatar-image" alt="${escapeHtml(member.first_name)}"/>` : `<span class=\"avatar-initials\">${escapeHtml((member.first_name?.[0]||'').toUpperCase())}${escapeHtml((member.last_name?.[0]||'').toUpperCase())}</span>`}
          </div>
          <div class="profile-info-compact" style="display:flex;flex-direction:column;gap:6px;min-width:0;flex:1;">
            <div style="display:flex;align-items:center;gap:8px;min-width:0;">
              <h3 class="profile-name-compact" style="margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escapeHtml(`${member.first_name||''} ${member.last_name||''}`.trim())}</h3>
              <span class="role-badge">${escapeHtml(member.role?.name||member.role?.code||'')}</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              ${member.username ? `<p class="profile-nickname-compact" style="margin:0;">@${escapeHtml(member.username)}</p>` : ''}
            </div>
          </div>
        </div>
      </div>
    </div>`
  function close(){ overlay.remove() }
  overlay.addEventListener('click', (e)=>{ if(e.target===overlay) close() })
  overlay.querySelector('.modal-close')?.addEventListener('click', close)
  document.body.appendChild(overlay)
}

import { renderCarCard } from '/app/frontend/assets/js/components/cards/car_card.js'

export function openCarModal(car){
  const overlay = document.createElement('div')
  overlay.className = 'modal-overlay'
  const title = `${car.brand?.name||''} ${car.model||''}`.trim()
  const ownerAvatar = car.owner?.photo?.urls?.medium || car.owner?.photo?.url || ''
  // Фото: поддержка массива car.photos (объекты/строки) и одиночного car.photo
  const rawPhotos = Array.isArray(car.photos) && car.photos.length
    ? car.photos
    : (car.photo?.urls?.medium ? [car.photo] : (car.photo?.url ? [car.photo] : []))
  const photos = rawPhotos.map(p=> {
    if (typeof p === 'string') return { url: p }
    const best = p.urls?.medium || p.url
    return { url: best }
  })

  // Метки для полей БД (cars c.*)
  const fieldLabels = {
    id: 'ID',
    reg_number: 'Гос. номер',
    show_reg_number: 'Показывать номер',
    car_brand_id: 'ID марки',
    model: 'Модель',
    color: 'Цвет',
    year: 'Год выпуска',
    engine_power: 'Мощность',
    engine_volume: 'Объем двигателя',
    vin: 'VIN',
    roof_type: 'Тип крыши',
    description: 'Описание',
    notes: 'Заметки',
    owner_user_id: 'ID владельца',
    status_id: 'ID статуса',
    created_at: 'Создано',
    updated_at: 'Обновлено'
  }

  // Берём только поля из БД (примитивы), исключая развёрнутые объекты
  const excludedKeys = new Set(['brand','owner','status','photo'])
  const dbEntries = Object.entries(car).filter(([k,v]) => !excludedKeys.has(k) && (v===null || typeof v !== 'object'))

  const detailsHtml = dbEntries.map(([k,v])=>{
    const label = fieldLabels[k] || k
    const val = (v===null || v===undefined || v==='') ? 'не задано' : String(v)
    return `<div class="detail-item-compact"><span class="detail-label">${escapeHtml(label)}</span><span class="detail-value">${escapeHtml(val)}</span></div>`
  }).join('')
  overlay.innerHTML = `
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">${escapeHtml(title)}</div>
        <button class="modal-close" aria-label="close">×</button>
      </div>
      <div class="modal-body">
        ${photos.length ? `<div class=\"main-photo-compact\"><img src=\"${escapeHtml(photos[0].url)}\" class=\"main-image\" alt=\"${escapeHtml(title)}\"/></div>` : ''}
        ${photos.length > 1 ? `<div class=\"photo-thumbnails-compact\">${photos.map((p,i)=>`<img src=\\\"${escapeHtml(p.url)}\\\" class=\\\"thumbnail-compact${i===0?' active':''}\\\" data-index=\\\"${i}\\\" alt=\\\"thumb\\\"/>`).join('')}</div>` : ''}
        <div class="detail-item-compact" style="display:flex;align-items:center;gap:8px">
          <div class="owner-avatar-small">${ownerAvatar ? `<img src=\"${escapeHtml(ownerAvatar)}\" class=\"avatar-image\" alt=\"@${escapeHtml(car.owner?.username||'')}\"/>` : ''}</div>
          <div><span class="detail-label">Владелец:</span> <span class="detail-value">@${escapeHtml(car.owner?.username||'') || 'не задано'}</span></div>
        </div>
        <div class="detail-grid-compact">${detailsHtml}</div>

        ${Array.isArray(car.second_pilots) && car.second_pilots.length ? `
        <div class=\"pilots-section-compact\">
          <h4>Дополнительные пилоты (${car.second_pilots.length})</h4>
          <div class=\"pilots-list-compact\">
          ${car.second_pilots.map(p=>`
            <div class=\"pilot-item-compact\"> 
              <div class=\"pilot-avatar-compact\">${p.photo?.urls?.medium?`<img src=\\\"${escapeHtml(p.photo.urls.medium)}\\\" class=\\\"avatar-image\\\" alt=\\\"@${escapeHtml(p.username||'')}\\\"/>`:(p.photo?.url?`<img src=\\\"${escapeHtml(p.photo.url)}\\\" class=\\\"avatar-image\\\" alt=\\\"@${escapeHtml(p.username||'')}\\\"/>`:'')}</div>
              <div class=\"pilot-info-compact\"> 
                <span class=\"pilot-name-compact\">${escapeHtml(`${p.first_name||''} ${p.last_name||''}`.trim())||'—'}</span>
                ${p.username?`<span class=\"pilot-nickname-compact\">@${escapeHtml(p.username)}</span>`:''}
              </div>
            </div>`).join('')}
          </div>
        </div>` : ''}
      </div>
    </div>`
  function close(){ overlay.remove() }
  overlay.addEventListener('click', (e)=>{ if(e.target===overlay) close() })
  overlay.querySelector('.modal-close')?.addEventListener('click', close)
  document.body.appendChild(overlay)

  // Навешиваем поведение на миниатюры
  if (photos.length > 1) {
    const main = overlay.querySelector('.main-photo-compact .main-image')
    overlay.querySelectorAll('.thumbnail-compact').forEach(el=>{
      el.addEventListener('click', ()=>{
        overlay.querySelectorAll('.thumbnail-compact').forEach(t=>t.classList.remove('active'))
        el.classList.add('active')
        const idx = Number(el.getAttribute('data-index'))
        if (main && photos[idx]) main.setAttribute('src', photos[idx].url)
      })
    })
  }
}

// Глобально
window.CabrioComponents = { renderMemberCard, openMemberModal, renderCarCard, openCarModal }


