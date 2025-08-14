// user_modal.js — модальное окно профиля пользователя

function escapeHtml(str){
  return String(str||'').replace(/[&<>"]|'/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

export async function openUserModal(member){
  const overlay = document.createElement('div')
  overlay.className = 'modal-overlay'
  const photoUrl = member.photo?.urls?.medium || member.photo?.url || member.photo_url || ''
  const cars = Array.isArray(member.cars) ? member.cars : []
  const firstName = member.first_name_app || member.first_name || member.first_name_tg || ''
  const lastName = member.last_name_app || member.last_name || member.last_name_tg || ''
  const initials = (firstName?.[0]||'').toUpperCase() + (lastName?.[0]||'').toUpperCase()
  overlay.innerHTML = `
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Профиль</div>
        <button class="modal-close" aria-label="close">×</button>
      </div>
      <div class="modal-body">
        <div class="member-profile" style="display:flex;align-items:center;gap:12px;">
          <div class="profile-avatar-compact">
            ${photoUrl ? `<img src="${escapeHtml(photoUrl)}" class="avatar-image" alt="${escapeHtml(firstName)}"/>` : `<span class=\"avatar-initials\">${escapeHtml(initials)}</span>`}
          </div>
          <div class="profile-info-compact" style="display:flex;flex-direction:column;gap:6px;min-width:0;flex:1;">
            <div style="display:flex;align-items:center;gap:8px;min-width:0;">
              <h3 class="profile-name-compact" style="margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escapeHtml(`${firstName} ${lastName}`.trim())}</h3>
              <span class="role-badge" id="userRoleBadge">${escapeHtml(member.role?.name||member.role?.code||'')}</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              ${member.username ? `<p class="profile-nickname-compact" style="margin:0;">@${escapeHtml(member.username)}</p>` : ''}
            </div>
          </div>
        </div>

        <div class="detail-grid-compact" style="margin-top:8px;">
          ${(() => {
            const rows = []
            const val = (v) => (v===null || v===undefined || String(v).trim()==='') ? 'не указано' : String(v)
            // Поля профиля приложения (которые можно вносить)
            rows.push(`<div class=\"detail-item-compact\"><span class=\"detail-label\">Имя (приложение):</span><span class=\"detail-value\">${escapeHtml(val(member.first_name_app))}</span></div>`)
            rows.push(`<div class=\"detail-item-compact\"><span class=\"detail-label\">Фамилия (приложение):</span><span class=\"detail-value\">${escapeHtml(val(member.last_name_app))}</span></div>`)
            rows.push(`<div class=\"detail-item-compact\"><span class=\"detail-label\">Email:</span><span class=\"detail-value\">${escapeHtml(val(member.email))}</span></div>`)
            rows.push(`<div class=\"detail-item-compact\"><span class=\"detail-label\">Телефон:</span><span class=\"detail-value\">${escapeHtml(val(member.phone))}</span></div>`)
            rows.push(`<div class=\"detail-item-compact\"><span class=\"detail-label\">Город:</span><span class=\"detail-value\">${escapeHtml(val(member.city))}</span></div>`)
            rows.push(`<div class=\"detail-item-compact\"><span class=\"detail-label\">Страна:</span><span class=\"detail-value\">${escapeHtml(val(member.country))}</span></div>`)
            rows.push(`<div class=\"detail-item-compact\"><span class=\"detail-label\">О себе:</span><span class=\"detail-value\">${escapeHtml(val(member.about))}</span></div>`)
            // Telegram данные (read-only)
            rows.push(`<div class=\"detail-item-compact\"><span class=\"detail-label\">Telegram ID:</span><span class=\"detail-value\">${escapeHtml(val(member.telegram_id))}</span></div>`)
            rows.push(`<div class=\"detail-item-compact\"><span class=\"detail-label\">Username (TG):</span><span class=\"detail-value\">${escapeHtml(val(member.username))}</span></div>`)
            const fnTg = member.first_name_tg || member.first_name || ''
            const lnTg = member.last_name_tg || member.last_name || ''
            rows.push(`<div class=\"detail-item-compact\"><span class=\"detail-label\">Имя (TG):</span><span class=\"detail-value\">${escapeHtml(val(fnTg))}</span></div>`)
            rows.push(`<div class=\"detail-item-compact\"><span class=\"detail-label\">Фамилия (TG):</span><span class=\"detail-value\">${escapeHtml(val(lnTg))}</span></div>`)
            // Кол-во авто
            rows.push(`<div class=\"detail-item-compact\"><span class=\"detail-label\">Автомобили:</span><span class=\"detail-value\">${escapeHtml(String((member.cars||[]).length))}</span></div>`)
            return rows.join('')
          })()}
        </div>

        <div id="roleEditor" style="display:none; margin-top:8px;">
          <label style="display:flex;align-items:center;gap:8px;">
            <span class="detail-label">Изменить роль:</span>
            <select id="roleSelect" class="filter-select">
              <option value="external">external</option>
              <option value="guest">guest</option>
              <option value="user">user</option>
              <option value="member">member</option>
              <option value="moderator">moderator</option>
              <option value="admin">admin</option>
            </select>
            <button id="roleSaveBtn" class="btn-success">Сохранить</button>
          </label>
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

  // Просмотр большого фото пользователя по клику на аватар
  const avatarEl = overlay.querySelector('.profile-avatar-compact img')
  const openUserPhoto = () => {
    const url = (member.photo && (member.photo.url || member.photo.urls?.medium)) || photoUrl
    if (!url) return
    const ov = document.createElement('div')
    ov.className = 'photo-viewer-overlay'
    ov.innerHTML = `
      <div class="photo-viewer-content">
        <button class="photo-viewer-close" aria-label="close">×</button>
        <img class="photo-viewer-img" src="${escapeHtml(url)}" alt="avatar"/>
      </div>`
    document.body.appendChild(ov)
    const close = ()=> ov.remove()
    ov.addEventListener('click', (e)=>{ if (e.target === ov) close() })
    ov.querySelector('.photo-viewer-close')?.addEventListener('click', close)
  }
  if (avatarEl) {
    avatarEl.style.cursor = 'zoom-in'
    avatarEl.addEventListener('click', openUserPhoto)
  }

  // Разрешаем редактирование роли только модераторам+
  try {
    const me = await CabrioAPI.apiGet('/api/users/profile')
    const myRole = me?.data?.role?.code
    const allowed = ['moderator','admin']
    if (allowed.includes(myRole)) {
      const editor = overlay.querySelector('#roleEditor')
      const select = overlay.querySelector('#roleSelect')
      const badge = overlay.querySelector('#userRoleBadge')
      if (editor && select && badge) {
        editor.style.display = 'block'
        select.value = (member.role?.code || member.role?.name || 'guest')
        overlay.querySelector('#roleSaveBtn')?.addEventListener('click', async ()=>{
          const role = select.value
          const res = await CabrioAPI.apiPost(`/api/users/${member.id}/role`, { role })
          if (!res || res.success === false || res.__httpStatus===401 || res.__httpStatus===403) {
            alert((res && res.error && res.error.message) || 'Не удалось сохранить роль')
            return
          }
          const updated = res.data
          badge.textContent = updated?.role?.name || updated?.role?.code || role
          alert('Роль обновлена')
        })
      }
    }
  } catch {}
}

// Глобально для удобства
window.CabrioModals = window.CabrioModals || {}
window.CabrioModals.openUserModal = openUserModal


