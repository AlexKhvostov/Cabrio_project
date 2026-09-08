// Большая карточка участника. Пустые поля на месте. Авто — ссылки на полную карточку, не плитки списка.

import { phUser } from '../components/media.js?v=cabrio20'
import {
  escapeHtml, viewVal, sheetField, personName, personIni, renderCarLink, renderAddMyCarButton, bindRelLinks, headerActions, openPhotoViewer,
  tgUsername, isSameTelegramUser, openTelegramDialog
} from '../components/sheet.js?v=write2'

function readTelegramUser(){
  try{
    const tg = window.Telegram?.WebApp
    const u = tg?.initDataUnsafe?.user || {}
    return {
      telegram_id: u?.id ? String(u.id) : undefined,
      first_name: u?.first_name ? String(u.first_name) : undefined,
      last_name: u?.last_name ? String(u.last_name) : undefined,
      username: u?.username ? String(u.username) : undefined,
    }
  }catch{ return {} }
}

export async function openUserModal(member){
  const overlay = document.createElement('div')
  overlay.className = 'modal-overlay modal-above-nav'
  const cars = Array.isArray(member.cars) ? member.cars : []
  const fullName = personName(member) || 'Без имени'
  const roleLabel = member.role?.name || member.role?.code || ''
  const meta = [member.username ? '@'+member.username : '', member.city || ''].filter(Boolean).join(' · ')
  const photoUrl = member.photo?.urls?.orig || member.photo?.urls?.medium || member.photo?.url || member.photo_url || ''
  let isEditing = false
  const canEdit = !!(member.permissions?.canEdit)
  const showPrivate = canEdit || member.email != null || member.phone != null
  let meId = null

  const canWrite = () => {
    if (isEditing) return false
    if (!tgUsername(member)) return false
    if (isSameTelegramUser(member)) return false
    if (meId && Number(meId) === Number(member.id)) return false
    return true
  }

  const paintWrite = () => {
    const slot = overlay.querySelector('#userWriteSlot')
    if (!slot) return
    if (!canWrite()) { slot.innerHTML = ''; return }
    slot.innerHTML = `<button type="button" class="btn-ghost btn-write" data-sheet-write>Написать</button>`
    slot.querySelector('[data-sheet-write]')?.addEventListener('click', () => { openTelegramDialog(member) })
  }

  const paintHeader = () => {
    const actions = overlay.querySelector('#userHeaderActions')
    if (!actions) return
    actions.innerHTML = headerActions({ canEdit, editing: isEditing, withClose: true })
    actions.querySelector('.modal-close')?.addEventListener('click', close)
    actions.querySelector('[data-sheet-edit]')?.addEventListener('click', () => { isEditing = true; paintHeader(); renderFields(); ensureUpload(); window.CabrioUI?.kickModalLayout?.(overlay) })
    actions.querySelector('[data-sheet-cancel]')?.addEventListener('click', () => { isEditing = false; overlay.querySelector('#userUploadFab')?.remove(); paintHeader(); renderFields() })
    actions.querySelector('[data-sheet-save]')?.addEventListener('click', saveEdit)
    paintWrite()
  }

  overlay.innerHTML = `
    <div class="modal-content modal-compact sheet-card">
      <div class="modal-header">
        <div class="modal-title">Участник</div>
        <div id="userHeaderActions" class="sheet-actions"></div>
      </div>
      <div class="modal-body">
        <div class="sheet-hero-wrap">
          <div class="sheet-hero">
            <div class="sheet-hero-photo">${phUser(member, personIni(member), 'orig', true)}</div>
            <div class="sheet-hero-text">
              <div class="sheet-hero-name-row">
                <div class="sheet-hero-name">${escapeHtml(fullName)}</div>
                <span id="userWriteSlot" class="sheet-hero-write"></span>
              </div>
              ${meta ? `<div class="sheet-hero-meta">${escapeHtml(meta)}</div>` : `<div class="sheet-hero-meta sheet-empty">город не указан</div>`}
              ${roleLabel ? `<span class="role-badge" id="userRoleBadge">${escapeHtml(roleLabel)}</span>` : `<span id="userRoleBadge" class="role-badge" hidden></span>`}
            </div>
          </div>
        </div>
        <div class="sheet-section-title">Основная информация</div>
        <div class="sheet-grid" id="userFields"></div>
        <div id="roleEditor" class="sheet-field" style="display:none;">
          <span class="sheet-label">Роль</span>
          <div class="sheet-role-row">
            <select id="roleSelect" class="filter-select">
              <option value="external">external</option>
              <option value="guest">guest</option>
              <option value="user">user</option>
              <option value="member">member</option>
              <option value="moderator">moderator</option>
              <option value="admin">admin</option>
            </select>
            <button id="roleSaveBtn" class="btn-primary" type="button" disabled>Сохранить</button>
          </div>
        </div>
        <div class="sheet-section-title">Автомобили</div>
        <div class="sheet-links" id="userCars"></div>
      </div>
    </div>`

  function close(){ overlay.remove() }
  overlay.addEventListener('click', (e)=>{ if(e.target===overlay) close() })
  document.body.appendChild(overlay)
  paintHeader()
  bindRelLinks(overlay)

  const carsBox = overlay.querySelector('#userCars')
  const isOwnProfile = () => (meId && Number(meId) === Number(member.id)) || isSameTelegramUser(member)
  const paintCars = (list) => {
    const items = Array.isArray(list) ? list : []
    const emptyHint = items.length ? '' : `<p class="sheet-empty profile-no-cars">${isOwnProfile() ? 'Пока нет автомобилей — добавьте свой кабриолет' : 'Автомобилей пока нет'}</p>`
    carsBox.innerHTML = emptyHint + items.map(c => renderCarLink(c)).join('') + (isOwnProfile() ? renderAddMyCarButton('btn-add-my-car-modal') : '')
    carsBox.querySelector('#btn-add-my-car-modal')?.addEventListener('click', async () => {
      if (!window.CabrioModals?.openCarModal) {
        const front = String(window.__FRONT_URL || '/app/frontend').replace(/\/$/, '')
        await import(`${front}/assets/js/modals/car_modal.js?v=create-car1`)
      }
      window.CabrioModals?.openCarModal?.({}, {
        onChanged: async () => {
          try { window.CabrioAPI?.invalidateMe?.() } catch {}
          const me = await window.CabrioAPI?.getMe?.()
          member.cars = me?.data?.cars || member.cars
          paintCars(member.cars)
        }
      })
    })
  }
  paintCars(cars)

  overlay.querySelector('.sheet-hero .ph')?.addEventListener('click', () => {
    const url = (member.photo && (member.photo.url || member.photo.urls?.orig || member.photo.urls?.medium)) || photoUrl
    if (!url || isEditing) return
    openPhotoViewer(url)
  })

  const shown = (...vals) => {
    for (const v of vals) {
      if (v !== null && v !== undefined && String(v).trim() !== '') return String(v)
    }
    return ''
  }
  const input = (key, val) => `<input data-edit-key="${key}" class="filter-input" value="${escapeHtml(val !== undefined ? val : (member[key]??''))}" />`

  function renderFields(){
    const first = shown(member.first_name_app, member.first_name, member.first_name_tg)
    const last = shown(member.last_name_app, member.last_name, member.last_name_tg)
    const aboutInner = isEditing
      ? `<textarea data-edit-key="about" class="filter-input" rows="2">${escapeHtml(member.about||'')}</textarea>`
      : viewVal(member.about)
    const rows = [
      sheetField('Имя', isEditing ? input('first_name_app', first) : viewVal(first)),
      sheetField('Фамилия', isEditing ? input('last_name_app', last) : viewVal(last)),
      sheetField('Город', isEditing ? input('city') : viewVal(member.city)),
      sheetField('Страна', isEditing ? input('country') : viewVal(member.country)),
    ]
    if (showPrivate) {
      rows.push(sheetField('Телефон', isEditing ? input('phone') : viewVal(member.phone)))
      rows.push(sheetField('Почта', isEditing ? input('email') : viewVal(member.email)))
    }
    rows.push(sheetField('О себе', aboutInner, 'full'))
    overlay.querySelector('#userFields').innerHTML = rows.join('')
    overlay.querySelector('.sheet-card')?.classList.toggle('editing', isEditing)
  }

  function ensureUpload(){
    const wrap = overlay.querySelector('.sheet-hero-photo')
    if (!wrap || wrap.querySelector('#userUploadFab')) return
    const inputEl = document.createElement('input')
    inputEl.type = 'file'
    inputEl.accept = 'image/*'
    inputEl.hidden = true
    wrap.appendChild(inputEl)
    const fab = document.createElement('button')
    fab.id = 'userUploadFab'
    fab.type = 'button'
    fab.className = 'photo-upload-fab'
    fab.innerHTML = '<span>📷</span><span>Фото</span>'
    wrap.appendChild(fab)
    fab.addEventListener('click', ()=> inputEl.click())
    inputEl.addEventListener('change', async ()=>{
      const file = inputEl.files && inputEl.files[0]
      if (!file) return
      try {
        const base = (window.__API_URL || (window.location.origin + '/app/backend')).replace(/\/$/, '')
        const fd = new FormData()
        fd.append('entity_type','user')
        fd.append('entity_id', String(member.id))
        fd.append('photo', file)
        Object.entries(readTelegramUser()).forEach(([k,v])=>{ if (v!==undefined) fd.append(k, v) })
        const res = await fetch(`${base}/routes/api.php?route=${encodeURIComponent('/api/photos')}`, { method:'POST', body: fd }).then(r=>r.json().catch(()=>null))
        if (!res || res.success === false) { alert((res && res.error && res.error.message) || 'Не удалось загрузить'); return }
        member.photo = res.data
        try { window.CabrioAPI?.invalidateMe?.() } catch {}
        if (meId && Number(meId) === Number(member.id)) {
          const navUrl = res.data?.urls?.medium || res.data?.url
          try { window.CabrioUI?.setNavAvatar?.(navUrl) } catch {}
        }
        overlay.remove()
        openUserModal(member)
      } catch { alert('Ошибка загрузки') }
    })
  }

  async function saveEdit(){
    const get = (key) => overlay.querySelector(`[data-edit-key="${key}"]`)?.value ?? member[key]
    const payload = {
      first_name_app: get('first_name_app'),
      last_name_app: get('last_name_app'),
      city: get('city'),
      country: get('country'),
      phone: get('phone'),
      email: get('email'),
      about: get('about'),
    }
    if (!(meId && Number(meId) === Number(member.id))) payload.id = member.id
    const res = await window.CabrioAPI.apiPost('/api/users/profile', payload)
    if (!res || res.success === false || res.__httpStatus === 401 || res.__httpStatus === 403) {
      alert((res && res.error && res.error.message) || 'Не удалось сохранить')
      return
    }
    overlay.remove()
    if (window.CabrioNav?.openUser) window.CabrioNav.openUser(member.id)
  }

  renderFields()

  ;(async () => {
    try {
      const me = await (window.CabrioAPI?.getMe ? window.CabrioAPI.getMe() : null)
      meId = me?.data?.id || null
      paintHeader()
      paintCars(member.cars || [])
      const myRole = me?.data?.role?.code || ''
      const isStaff = ['moderator','admin'].includes(String(myRole).toLowerCase())
      if (!(isStaff && Number(meId) !== Number(member.id))) return
    } catch { return }
    const editor = overlay.querySelector('#roleEditor')
    const select = overlay.querySelector('#roleSelect')
    const badge = overlay.querySelector('#userRoleBadge')
    if (!editor || !select) return
    editor.style.display = ''
    if (badge) badge.hidden = false
    let originalRole = member.role?.code || 'guest'
    const saveBtn = overlay.querySelector('#roleSaveBtn')
    select.value = originalRole
    if (saveBtn) saveBtn.disabled = true
    select.addEventListener('change', ()=>{ if (saveBtn) saveBtn.disabled = (select.value === originalRole) })
    saveBtn?.addEventListener('click', async ()=>{
      const role = select.value
      if (role === originalRole) return
      const res = await window.CabrioAPI.apiPost(`/api/users/${member.id}/role`, { role })
      if (!res || res.success === false) {
        alert((res && res.error && res.error.message) || 'Не удалось сохранить роль')
        return
      }
      originalRole = role
      saveBtn.disabled = true
      if (badge) badge.textContent = res.data?.role?.name || res.data?.role?.code || role
    })
  })()
}

window.CabrioModals = window.CabrioModals || {}
window.CabrioModals.openUserModal = openUserModal
