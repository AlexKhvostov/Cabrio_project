// profile_view.js — инициализация и логика страницы профиля

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
  const tgUser = readTelegramUser()
  const qp = new URLSearchParams()
  Object.entries(tgUser).forEach(([k,v])=>{ if (v !== undefined) qp.append(k, v) })
  const url = `${getApiRoot()}/routes/api.php?route=${encodeURIComponent(route)}${qp.toString() ? ('&' + qp.toString()) : ''}`
  const res = await fetch(url, { headers: {} })
  const data = await res.json().catch(()=>null)
  if (res.status === 401 || res.status === 403) return { __httpStatus: res.status, ...(data||{}) }
  return data
}

async function apiPost(route, payload) {
  if (window.CabrioAPI?.apiPost) return window.CabrioAPI.apiPost(route, payload)
  const tgUser = readTelegramUser()
  const url = `${getApiRoot()}/routes/api.php?route=${encodeURIComponent(route)}`
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(Object.assign({}, payload || {}, tgUser))
  })
  const data = await res.json().catch(()=>null)
  if (res.status === 401 || res.status === 403) return { __httpStatus: res.status, ...(data||{}) }
  return data
}

export async function initProfilePage() {
  const placeholder = document.getElementById('me')
  const profile = document.getElementById('profile')
  const roleEl = document.getElementById('role')
  const carsSection = document.getElementById('my-cars')
  const carsListEl = document.getElementById('cars-list')
  const dbg = document.getElementById('debug')

  // ===== Диагностика сети и окружения: привязка кнопки сразу =====
  try {
    const btn = document.getElementById('runNetTestBtn')
    const out = document.getElementById('netDebug')
    if (btn && out && !btn.__bound) {
      btn.__bound = true
      btn.addEventListener('click', async () => {
        const tg = window.Telegram?.WebApp
        const u = tg?.initDataUnsafe?.user
        const tgUser = readTelegramUser()
        const qp = new URLSearchParams(); Object.entries(tgUser).forEach(([k,v])=>{ if (v!==undefined) qp.append(k,v) })
        const apiRoot = getApiRoot()
        const tests = []
        const push = (name, value) => tests.push({ name, value })
        const fetchWithTimeout = async (url, options, timeoutMs) => {
          const controller = (typeof AbortController !== 'undefined') ? new AbortController() : null
          const id = controller ? setTimeout(() => { try{ controller.abort() }catch{} }, Math.max(1000, timeoutMs||8000)) : null
          try {
            const opts = Object.assign({}, options || {})
            if (controller) opts.signal = controller.signal
            return await fetch(url, opts)
          } finally { if (id) { try{ clearTimeout(id) }catch{} } }
        }
        const tryFetch = async (route, options, timeoutMs) => {
          const url = `${apiRoot}/routes/api.php?route=${encodeURIComponent(route)}${qp.toString()?('&'+qp.toString()):''}`
          const started = Date.now()
          try {
            const opts = Object.assign({ headers: {} }, options || {})
            // если POST без body — добавим tgUser
            if ((opts.method||'GET').toUpperCase()==='POST' && !opts.body) {
              opts.headers['Content-Type'] = 'application/json'
              opts.body = JSON.stringify(tgUser)
            }
            const res = await fetchWithTimeout(url, opts, timeoutMs || 8000)
            const ms = Date.now() - started
            let json = null
            try { json = await res.clone().json() } catch {}
            return { ok: res.ok, status: res.status, ms, json }
          } catch (e) {
            return { ok: false, error: e && (e.name + ': ' + e.message) }
          }
        }

        try { btn.disabled = true; btn.textContent = 'Тест идёт…' } catch {}
        try { out.textContent = 'Запускаю тест…' } catch {}

        // Системная информация
        push('time', new Date().toISOString())
        push('location', window.location.href)
        push('apiRoot', apiRoot)
        push('userAgent', navigator.userAgent)
        push('platform', navigator.platform)
        push('language', navigator.language)
        push('online', navigator.onLine)
        push('tg_present', !!(window.Telegram && window.Telegram.WebApp))
        push('tg_user_present', !!u)
        push('tg_initData_len', (tg?.initData ? String(tg.initData).length : 0))
        push('headers_sent', [])

        const [r1, r2, r3, r4] = await Promise.all([
          tryFetch('/api/health', {}, 6000),
          tryFetch('/api/users/profile', {}, 8000),
          tryFetch('/api/user-locations', {}, 8000),
          tryFetch('/api/users/profile', {}, 6000)
        ])
        push('health', r1)
        push('profile', r2)
        push('userLocations', r3)
        push('profile_no_headers', r4)

        try {
          out.textContent = JSON.stringify(tests, null, 2)
        } catch {
          out.textContent = String(tests)
        }
        try { btn.disabled = false; btn.textContent = 'Тест соединения' } catch {}
      })
    }
  } catch {}

  try {
    const tg = window.Telegram?.WebApp
    const u = tg?.initDataUnsafe?.user
    const clientInfo = {
      telegram_present: !!u,
      telegram_user: u ? { id: u.id, username: u.username, first_name: u.first_name, last_name: u.last_name } : null,
    }

    const json = await apiGet('/api/users/profile')
    if (!json) {
      if (placeholder) placeholder.textContent = 'Нет ответа от сервера'
      if (dbg) dbg.textContent = JSON.stringify({ httpStatus: 0, success: false, clientInfo }, null, 2)
      return
    }

    if (json.__httpStatus === 401) {
      if (placeholder) placeholder.textContent = 'Не авторизован'
    } else if (json.__httpStatus === 403) {
      if (placeholder) placeholder.textContent = 'Недостаточно прав'
    }

    if (json.success) {
      if (placeholder) placeholder.style.display = 'none'
      if (profile) profile.style.display = 'block'
      const d = json.data || {}
      if (roleEl) roleEl.textContent = `${d.role?.name || d.role?.code || ''}`
      const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = (val ?? '') }
      set('first_name_app', d.first_name_app)
      set('last_name_app', d.last_name_app)
      set('city', d.city)
      set('country', d.country)
      set('email', d.email)
      set('phone', d.phone)
      set('about', d.about)
      set('notes', d.notes)
      set('tg_id', d.telegram_id)
      set('tg_username', d.username)
      set('first_name_tg', d.first_name_tg || d.first_name)
      set('last_name_tg', d.last_name_tg || d.last_name)

      // Заголовок профиля (имя, username)
      const name = (d.first_name_app || d.first_name || d.first_name_tg || '') + ((d.last_name_app || d.last_name || d.last_name_tg) ? ' ' + (d.last_name_app || d.last_name || d.last_name_tg) : '')
      const titleNameEl = document.getElementById('profileName')
      const titleUserEl = document.getElementById('profileUsername')
      if (titleNameEl) titleNameEl.textContent = name.trim() || 'Профиль'
      if (titleUserEl) titleUserEl.textContent = d.username ? '@'+d.username : ''
      // Аватар + загрузка
      const avatarEl = document.getElementById('profileAvatar')
      const uploadBtn = document.getElementById('userUploadBtn')
      if (avatarEl) {
        avatarEl.innerHTML = ''
        const url = d.photo?.urls?.medium || d.photo?.url
        if (url) {
          const img = document.createElement('img')
          img.src = url
          img.alt = ''
          avatarEl.appendChild(img)
        } else {
          avatarEl.textContent = (d.first_name_app?.[0] || d.first_name || d.first_name_tg || '?').toString().slice(0,1).toUpperCase()
        }
      }

      // Автомобили
      const cars = d.cars || []
      if (cars.length && carsSection && carsListEl) {
        carsSection.style.display = 'block'
        carsListEl.innerHTML = cars.map(c => window.CabrioComponents.renderCarCard({ ...c }, { showOwner: false })).join('')
        // Клик по карточке авто → модалка авто
        carsListEl.addEventListener('click', async (e)=>{
          const card = e.target.closest('.car-card-compact')
          if (!card) return
          const id = Number(card.getAttribute('data-id'))
          const car = cars.find(x=>Number(x.id)===id)
          if (!car) return
          try {
            if (!(window.CabrioModals && typeof window.CabrioModals.openCarModal === 'function')) {
              const v = String(Date.now())
              await import(`/app/frontend/assets/js/modals/car_modal.js?v=${v}`)
            }
          } catch {}
          if (window.CabrioModals && typeof window.CabrioModals.openCarModal === 'function') {
            window.CabrioModals.openCarModal(car)
            return
          }
          if (window.CabrioComponents && typeof window.CabrioComponents.openCarModal === 'function') {
            window.CabrioComponents.openCarModal(car)
          }
        })
      }

      // Редактирование/Сохранение
      const editBtn = document.getElementById('editBtn')
      const saveBtn = document.getElementById('saveBtn')
      const profileBtn = document.getElementById('profileBtn')
      const editableIds = ['first_name_app','last_name_app','city','country','email','phone','about']
      const setDisabled = (disabled) => {
        editableIds.forEach(id => {
          const el = document.getElementById(id)
          if (el) el.disabled = disabled
        })
      }
      const snapshot = () => {
        const obj = {}
        editableIds.forEach(id => { const el = document.getElementById(id); obj[id] = (el?.value||'').trim() })
        return obj
      }
      let initialData = snapshot()
      setDisabled(true)

      if (uploadBtn) {
        let input = document.getElementById('userPhotoInput')
        if (!input) {
          input = document.createElement('input')
          input.type = 'file'
          input.id = 'userPhotoInput'
          input.accept = 'image/*'
          input.style.display = 'none'
          document.body.appendChild(input)
        }
        uploadBtn.addEventListener('click', ()=> input.click())
        input.onchange = async ()=>{
          const file = input.files && input.files[0]
          if (!file) return
          try {
            const base = getApiRoot()
            const url = `${base}/routes/api.php?route=${encodeURIComponent('/api/photos')}`
            const fd = new FormData()
            fd.append('entity_type','user')
            fd.append('entity_id', String(d.id))
            fd.append('photo', file)
            const tgUser = readTelegramUser()
            Object.entries(tgUser).forEach(([k,v])=>{ if(v!==undefined) fd.append(k, v) })
            const res = await fetch(url, { method:'POST', body: fd }).then(r=>r.json().catch(()=>null))
            if (!res || res.success === false) { alert((res && res.error && res.error.message) || 'Не удалось загрузить'); return }
            const newPhoto = res.data
            if (avatarEl) {
              avatarEl.innerHTML = ''
              const img = document.createElement('img')
              img.src = newPhoto.urls?.medium || newPhoto.url
              avatarEl.appendChild(img)
            }
          } catch { alert('Ошибка загрузки') }
          finally { input.value = '' }
        }
      }

      if (editBtn && saveBtn) {
        editBtn.addEventListener('click', () => {
          const isEditing = editBtn.dataset.mode === 'editing'
          if (!isEditing) {
            editBtn.dataset.mode = 'editing'
            editBtn.textContent = 'Отменить'
            saveBtn.style.display = 'inline-flex'
            if (uploadBtn) uploadBtn.style.display = 'inline-flex'
            setDisabled(false)
          } else {
            // Отмена
            Object.entries(initialData).forEach(([k,v])=>{ const el = document.getElementById(k); if (el) el.value = v })
            editBtn.dataset.mode = ''
            editBtn.textContent = 'Редактировать'
            saveBtn.style.display = 'none'
            if (uploadBtn) uploadBtn.style.display = 'none'
            setDisabled(true)
          }
        })

        saveBtn.addEventListener('click', async () => {
          const payload = snapshot()
          const res = await apiPost('/api/users/profile', payload)
          if (!res || res.__httpStatus === 401) {
            alert('Не авторизован')
            return
          }
          if (res.__httpStatus === 403 || res.success === false) {
            alert((res.error && res.error.message) || 'Не удалось сохранить')
            return
          }
          // Успех: обновляем initialData и выключаем режим редактирования
          initialData = snapshot()
          editBtn.dataset.mode = ''
          editBtn.textContent = 'Редактировать'
          saveBtn.style.display = 'none'
          if (uploadBtn) uploadBtn.style.display = 'none'
          setDisabled(true)
        })
      }
    }

    if (dbg) dbg.textContent = JSON.stringify({ httpStatus: json.__httpStatus || 200, success: json.success, error: json.error || null, clientInfo }, null, 2)
  } catch (e) {
    const dbg = document.getElementById('debug')
    if (dbg) dbg.textContent = String(e)
  }
}


