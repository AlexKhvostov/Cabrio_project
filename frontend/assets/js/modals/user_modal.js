// user_modal.js — модальное окно профиля пользователя

function escapeHtml(str){
  return String(str||'').replace(/[&<>"]|'/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

export function openUserModal(member){
  const overlay = document.createElement('div')
  overlay.className = 'modal-overlay'
  const photoUrl = member.photo?.url || member.photo_url || ''
  const cars = Array.isArray(member.cars) ? member.cars : []
  const firstName = member.first_name_app || member.first_name || member.first_name_tg || ''
  const lastName = member.last_name_app || member.last_name || member.last_name_tg || ''
  const initials = (firstName?.[0]||'').toUpperCase() + (lastName?.[0]||'').toUpperCase()
  overlay.innerHTML = `
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">${escapeHtml(firstName)} ${escapeHtml(lastName)}</div>
        <button class="modal-close" aria-label="close">×</button>
      </div>
      <div class="modal-body">
        <div class="member-profile">
          <div class="profile-avatar-compact">
            ${photoUrl ? `<img src="${escapeHtml(photoUrl)}" class="avatar-image" alt="${escapeHtml(firstName)}"/>` : `<span class=\"avatar-initials\">${escapeHtml(initials)}</span>`}
          </div>
          <div class="profile-info-compact">
            <h3 class="profile-name-compact">${escapeHtml(firstName)} ${escapeHtml(lastName)}</h3>
            ${member.username ? `<p class="profile-nickname-compact">@${escapeHtml(member.username)}</p>` : ''}
            <div class="detail-grid-compact">
              <div class="detail-item-compact"><span class="detail-label">Telegram ID:</span><span class="detail-value">${escapeHtml(member.telegram_id||'')}</span></div>
              <div class="detail-item-compact"><span class="detail-label">Город:</span><span class="detail-value">${escapeHtml(member.city||'')}</span></div>
              <div class="detail-item-compact"><span class="detail-label">Роль:</span><span class="detail-value">${escapeHtml(member.role?.name||member.role?.code||'')}</span></div>
              <div class="detail-item-compact"><span class="detail-label">Автомобили:</span><span class="detail-value">${(member.cars||[]).length}</span></div>
            </div>
          </div>
        </div>

        ${cars.length ? `
        <div class=\"detail-section-compact\">
          <h4>Автомобили (${cars.length})</h4>
          <div class=\"cars-grid\">
            ${cars.map(c => window.CabrioComponents.renderCarCard({ ...c }, { showOwner: false })).join('')}
          </div>
        </div>` : ''}
      </div>
    </div>`
  function close(){ overlay.remove() }
  overlay.addEventListener('click', (e)=>{ if(e.target===overlay) close() })
  overlay.querySelector('.modal-close')?.addEventListener('click', close)
  document.body.appendChild(overlay)
}

// Глобально для удобства
window.CabrioModals = window.CabrioModals || {}
window.CabrioModals.openUserModal = openUserModal


