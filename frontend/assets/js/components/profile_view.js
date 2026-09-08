// Страница «Профиль» — та же карточка человека, те же кнопки Изменить / Отмена / Сохранить

import { phUser } from './media.js?v=cabrio15'
import {
  escapeHtml, viewVal, sheetField, personName, personIni, renderCarLink, bindRelLinks, headerActions, openPhotoViewer
} from './sheet.js?v=write1'

function getApiRoot() {
  return (window.__API_URL || (window.location.origin + '/app/backend')).replace(/\/$/, '')
}

function readTelegramUser() {
  try {
    const tg = window.Telegram?.WebApp
    const u = tg?.initDataUnsafe?.user || {}
    return {
      telegram_id: u?.id ? String(u.id) : undefined,
      first_name: u?.first_name ? String(u.first_name) : undefined,
      last_name: u?.last_name ? String(u.last_name) : undefined,
      username: u?.username ? String(u.username) : undefined,
    }
  } catch { return {} }
}

async function apiGet(route) {
  if (window.CabrioAPI?.apiGet) return window.CabrioAPI.apiGet(route)
  const qp = new URLSearchParams()
  Object.entries(readTelegramUser()).forEach(([k,v])=>{ if (v !== undefined) qp.append(k, v) })
  const url = `${getApiRoot()}/routes/api.php?route=${encodeURIComponent(route)}${qp.toString() ? ('&' + qp.toString()) : ''}`
  const res = await fetch(url)
  const data = await res.json().catch(()=>null)
  if (res.status === 401 || res.status === 403) return { __httpStatus: res.status, ...(data||{}) }
  return data
}

async function apiPost(route, payload) {
  if (window.CabrioAPI?.apiPost) return window.CabrioAPI.apiPost(route, payload)
  const url = `${getApiRoot()}/routes/api.php?route=${encodeURIComponent(route)}`
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(Object.assign({}, payload || {}, readTelegramUser()))
  })
  const data = await res.json().catch(()=>null)
  if (res.status === 401 || res.status === 403) return { __httpStatus: res.status, ...(data||{}) }
  return data
}

export async function initProfilePage() {
  const placeholder = document.getElementById('me')
  const profile = document.getElementById('profile')
  const dbg = document.getElementById('debug')
  const debugWrap = document.getElementById('debug-wrap')
  window.CabrioBusy?.show()

  try {
    const json = await (window.CabrioAPI?.getMe ? window.CabrioAPI.getMe() : apiGet('/api/users/profile'))
    if (!json) {
      if (placeholder) { placeholder.style.display = ''; placeholder.textContent = 'Нет ответа от сервера' }
      return
    }
    if (json.__httpStatus === 401) {
      if (placeholder) { placeholder.style.display = ''; placeholder.textContent = 'Не авторизован' }
      return
    }
    if (json.__httpStatus === 403) {
      if (placeholder) { placeholder.style.display = ''; placeholder.textContent = 'Недостаточно прав' }
      return
    }
    if (!json.success) {
      if (placeholder) { placeholder.style.display = ''; placeholder.textContent = (json.error && json.error.message) || 'Ошибка' }
      return
    }

    const d = json.data || {}
    if (placeholder) placeholder.style.display = 'none'
    if (profile) profile.style.display = ''
    if (debugWrap && String(d.role?.code || '').toLowerCase() === 'admin') debugWrap.hidden = false

    const name = personName(d) || 'Профиль'
    const meta = [d.username ? '@'+d.username : '', d.city || ''].filter(Boolean).join(' · ')
    document.getElementById('profileName').textContent = name
    document.getElementById('profileMeta').innerHTML = meta ? escapeHtml(meta) : '<span class="sheet-empty">город не указан</span>'
    const roleEl = document.getElementById('role')
    if (roleEl) roleEl.textContent = d.role?.name || d.role?.code || ''
    const avatarEl = document.getElementById('profileAvatar')
    let isEditing = false
    if (avatarEl) {
      avatarEl.innerHTML = phUser(d, personIni(d), 'orig', true)
      avatarEl.addEventListener('click', () => {
        if (isEditing) return
        const url = d.photo?.urls?.medium || d.photo?.url || ''
        if (url) openPhotoViewer(url)
      })
    }

    const cars = d.cars || []
    const carsListEl = document.getElementById('cars-list')
    if (carsListEl) {
      carsListEl.innerHTML = cars.length
        ? cars.map(c => renderCarLink(c)).join('')
        : `<p class="sheet-empty" style="margin:0">Автомобилей пока нет</p>`
      bindRelLinks(carsListEl)
    }

    const fieldsRoot = document.getElementById('profileFields')
    const actions = document.getElementById('profileActions')

    const shown = (...vals) => {
      for (const v of vals) {
        if (v !== null && v !== undefined && String(v).trim() !== '') return String(v)
      }
      return ''
    }
    const input = (key, val) => `<input data-edit-key="${key}" class="filter-input" value="${escapeHtml(val !== undefined ? val : (d[key]??''))}" />`

    const renderFields = () => {
      const first = shown(d.first_name_app, d.first_name, d.first_name_tg)
      const last = shown(d.last_name_app, d.last_name, d.last_name_tg)
      const aboutInner = isEditing
        ? `<textarea data-edit-key="about" class="filter-input" rows="2">${escapeHtml(d.about||'')}</textarea>`
        : viewVal(d.about)
      fieldsRoot.innerHTML = [
        sheetField('Имя', isEditing ? input('first_name_app', first) : viewVal(first)),
        sheetField('Фамилия', isEditing ? input('last_name_app', last) : viewVal(last)),
        sheetField('Город', isEditing ? input('city') : viewVal(d.city)),
        sheetField('Страна', isEditing ? input('country') : viewVal(d.country)),
        sheetField('Телефон', isEditing ? input('phone') : viewVal(d.phone)),
        sheetField('Почта', isEditing ? input('email') : viewVal(d.email)),
        sheetField('О себе', aboutInner, 'full'),
      ].join('')
      profile.classList.toggle('editing', isEditing)
    }

    const paintHeader = () => {
      actions.innerHTML = headerActions({ canEdit: true, editing: isEditing, withClose: false })
      actions.querySelector('[data-sheet-edit]')?.addEventListener('click', enterEdit)
      actions.querySelector('[data-sheet-cancel]')?.addEventListener('click', exitEdit)
      actions.querySelector('[data-sheet-save]')?.addEventListener('click', saveEdit)
    }

    const ensureUpload = () => {
      const wrap = document.getElementById('profileAvatar')
      if (!wrap || wrap.querySelector('#userUploadFab')) return
      const fileInput = document.createElement('input')
      fileInput.type = 'file'
      fileInput.accept = 'image/*'
      fileInput.hidden = true
      wrap.appendChild(fileInput)
      const fab = document.createElement('button')
      fab.id = 'userUploadFab'
      fab.type = 'button'
      fab.className = 'photo-upload-fab'
      fab.innerHTML = '<span>📷</span><span>Фото</span>'
      wrap.appendChild(fab)
      fab.addEventListener('click', ()=> fileInput.click())
      fileInput.addEventListener('change', async ()=>{
        const file = fileInput.files && fileInput.files[0]
        if (!file) return
        try {
          const fd = new FormData()
          fd.append('entity_type','user')
          fd.append('entity_id', String(d.id))
          fd.append('photo', file)
          Object.entries(readTelegramUser()).forEach(([k,v])=>{ if(v!==undefined) fd.append(k, v) })
          const res = await fetch(`${getApiRoot()}/routes/api.php?route=${encodeURIComponent('/api/photos')}`, { method:'POST', body: fd }).then(r=>r.json().catch(()=>null))
          if (!res || res.success === false) { alert((res && res.error && res.error.message) || 'Не удалось загрузить'); return }
          if (avatarEl) avatarEl.innerHTML = phUser({ photo: res.data }, personIni(d), 'medium', true)
          d.photo = res.data
          try { window.CabrioAPI?.invalidateMe?.() } catch {}
          try { window.CabrioUI?.setNavAvatar?.(res.data?.urls?.medium || res.data?.url) } catch {}
        } catch { alert('Ошибка загрузки') }
        finally { fileInput.value = '' }
      })
    }

    function enterEdit(){
      isEditing = true
      paintHeader()
      renderFields()
      ensureUpload()
      window.CabrioUI?.kickModalLayout?.(profile)
    }
    function exitEdit(){
      isEditing = false
      document.getElementById('userUploadFab')?.remove()
      paintHeader()
      renderFields()
    }
    async function saveEdit(){
      const get = (key) => fieldsRoot.querySelector(`[data-edit-key="${key}"]`)?.value ?? ''
      const payload = {
        first_name_app: get('first_name_app'),
        last_name_app: get('last_name_app'),
        city: get('city'),
        country: get('country'),
        email: get('email'),
        phone: get('phone'),
        about: get('about'),
      }
      const res = await apiPost('/api/users/profile', payload)
      if (!res || res.__httpStatus === 401) { alert('Не авторизован'); return }
      if (res.__httpStatus === 403 || res.success === false) {
        alert((res.error && res.error.message) || 'Не удалось сохранить')
        return
      }
      Object.assign(d, payload)
      document.getElementById('profileName').textContent = personName(d) || 'Профиль'
      document.getElementById('profileMeta').textContent = [d.username ? '@'+d.username : '', d.city || ''].filter(Boolean).join(' · ')
      exitEdit()
    }

    paintHeader()
    renderFields()

    if (dbg) dbg.textContent = JSON.stringify({ success: true, id: d.id, role: d.role }, null, 2)
  } catch (e) {
    if (placeholder) { placeholder.style.display = ''; placeholder.textContent = String(e) }
  } finally {
    window.CabrioBusy?.hide()
  }

  // Диагностика для админа — как раньше
  try {
    const btn = document.getElementById('runNetTestBtn')
    const out = document.getElementById('netDebug')
    if (btn && out && !btn.__bound) {
      btn.__bound = true
      btn.addEventListener('click', async () => {
        out.textContent = 'Запускаю тест…'
        const r = await apiGet('/api/health')
        out.textContent = JSON.stringify(r, null, 2)
      })
    }
  } catch {}
}
