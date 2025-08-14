// car_modal.js — модальное окно автомобиля

function escapeHtml(str){
  return String(str||'').replace(/[&<>"]|'/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

export function openCarModal(car){
  const overlay = document.createElement('div')
  overlay.className = 'modal-overlay'
  const rawBrandName = (car.brand && typeof car.brand.name === 'string') ? car.brand.name.trim() : ''
  const rawModelName = (typeof car.model === 'string') ? car.model.trim() : ''
  const titleBrand = rawBrandName || 'марка'
  const titleModel = rawModelName || 'модель'
  const title = `${titleBrand} ${titleModel}`.trim()
  const ownerAvatar = car.owner?.photo?.urls?.medium || car.owner?.photo?.url || ''
  const rawPhotos = Array.isArray(car.photos) && car.photos.length ? car.photos : (car.photo?.url ? [car.photo] : [])
  const photos = rawPhotos.map(p=>{
    if (typeof p === 'string') return { url: p }
    const best = p.urls?.medium || p.url
    return { url: best }
  })

  const fieldLabels = {
    brand: 'Марка',
    model: 'Модель',
    color: 'Цвет',
    year: 'Год выпуска',
    roof_type: 'Тип крыши',
    engine_power: 'Мощность',
    engine_volume: 'Объем двигателя',
    vin: 'VIN',
    reg_number: 'Гос. номер',
    description: 'Описание',
    notes: 'Заметки',
    created_at: 'Создано',
    updated_at: 'Обновлено'
  }

  // Локальный маппер для отображения названий типа крыши на русском
  const roofTypeName = (code) => {
    switch ((code || '').toString()) {
      case 'soft': return 'Мягкая';
      case 'hard': return 'Жёсткая';
      case 'targa': return 'Тарга';
      case 'none': return 'Нет';
      default: return code || 'не задано';
    }
  }

  // Хелпер рендера пары
  const renderItem = (key, value) => {
    const label = fieldLabels[key] || key
    const val = (value===null || value===undefined || value==='') ? 'не задано' : String(value)
    return `<div class="detail-item-compact"><span class="detail-label">${escapeHtml(label)}</span><span class="detail-value">${escapeHtml(val)}</span></div>`
  }

  // Группы полей (технические id исключены)
  const brandName = car.brand?.name || ''
  const groupBasic = [ ['brand', brandName], ['model', car.model], ['color', car.color], ['year', car.year], ['roof_type', roofTypeName(car.roof_type)] ]
  const groupEngine = [ ['engine_power', car.engine_power], ['engine_volume', car.engine_volume] ]
  const groupReg = [ ['vin', car.vin] ]
  const groupOther = [ ['description', car.description], ['notes', car.notes] ]
  const groupMeta = [ ['created_at', car.created_at], ['updated_at', car.updated_at] ]

  const basicHtml = groupBasic.map(([k,v])=>renderItem(k,v)).join('')
  const engineHtml = groupEngine.map(([k,v])=>renderItem(k,v)).join('')
  const regHtml = groupReg.map(([k,v])=>renderItem(k,v)).join('')
  const otherHtml = groupOther.map(([k,v])=>renderItem(k,v)).join('')
  const metaHtml = groupMeta.map(([k,v])=>renderItem(k,v)).join('')

  const statusLabel = car.status?.name || car.status?.code || '—'
  // Определяем, владелец ли текущий пользователь (для отображения реального номера)
  const currentUserId = window?.Telegram?.WebApp?.initDataUnsafe?.user?.id
  const isOwner = !!(car.owner && Number(car.owner.id) && Number(car.owner.id) === Number(window?.AppContext?.userId || 0))

  const canEdit = !!(car.permissions && car.permissions.canEdit)
  overlay.innerHTML = `
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">${escapeHtml(title)}</div>
        <div id="carHeaderActions" style="display:flex;gap:6px;align-items:center;">
          ${canEdit ? `<button class=\"btn-warning\" id=\"carEditBtn\" style=\"padding:6px 10px;font-size:13px\">Редактировать</button>` : ''}
          <button class="modal-close" aria-label="close">×</button>
        </div>
      </div>
      <div class="modal-body">
        <div class=\"main-photo-compact\">
          ${photos.length ? `<img src=\"${escapeHtml(photos[0].url)}\" class=\"main-image\" alt=\"${escapeHtml(title)}\" id=\"carMainPhoto\"/>` : `<div class=\"car-placeholder\" id=\"carMainPhotoPlaceholder\" style=\"width:100%;aspect-ratio:16/9;background:linear-gradient(135deg,var(--border-color) 0%, rgba(255,255,255,.08) 100%);display:flex;align-items:center;justify-content:center;color:#aaa;border-radius:10px;\">Нет фото</div>`}
          <div class=\"photo-upload-overlay\" id=\"carUploadOverlay\" style=\"display:none\"> <div class=\"spinner\"></div> <span>Загрузка…</span> </div>
        </div>
        

        <div class="car-info-section-compact">
          <div class="car-header-compact">
          <div class="car-title-compact">
            <span class="status-badge status-primary">${escapeHtml(statusLabel)}</span>
          </div>
            ${(car.reg_number!==undefined && car.reg_number!==null && String(car.reg_number)!=='') ? `
              <div class=\"car-number-compact\"><span class=\"detail-label\">Номер:</span> <span class=\"detail-value\">${escapeHtml(String(car.reg_number))}</span></div>
            ` : ''}
          </div>
        </div>

        ${car.owner ? (()=>{ 
          const o = car.owner || {}
          const first = o.first_name_app || o.first_name || o.first_name_tg || ''
          const last = o.last_name_app || o.last_name || o.last_name_tg || ''
          const normalized = { ...o, first_name: first, last_name: last }
          return `<div class=\\\"member-card-container\\\">${window.CabrioComponents.renderMemberCard(normalized, { showCars: false })}</div>`
        })() : ''}

        <div class="detail-grid-compact" id="carDetailsBasic"></div>
        <div class="detail-section-compact"><h4>Двигатель</h4><div class="detail-grid-compact" id="carDetailsEngine"></div></div>
        <div class="detail-section-compact"><h4>Регистрация</h4><div class="detail-grid-compact" id="carDetailsReg"></div></div>
        <div class="detail-section-compact"><h4>Прочее</h4><div class="detail-grid-compact" id="carDetailsOther"></div></div>
        ${metaHtml ? `<div class=\"detail-section-compact\"><h4>Метаданные</h4><div class=\"detail-grid-compact\">${metaHtml}</div></div>` : ''}
      </div>
    </div>`
  function close(){ overlay.remove() }
  overlay.addEventListener('click', (e)=>{ if(e.target===overlay) close() })
  overlay.querySelector('.modal-close')?.addEventListener('click', close)
  document.body.appendChild(overlay)

  // Миниатюры отключены по требованиям UX
  // Просмотр фото на весь экран (слайды) + подгрузка всех фото авто
  const openPhotoViewer = async (startIndex = 0) => {
    // Подгружаем полный список фото при необходимости
    let fullList = Array.isArray(car._allPhotos) && car._allPhotos.length ? car._allPhotos : null
    if (!fullList) {
      try {
        if (window.CabrioAPI?.apiGet) {
          const res = await window.CabrioAPI.apiGet(`/api/photos?entity_type=car&entity_id=${encodeURIComponent(String(car.id))}`)
          if (res && res.success !== false && Array.isArray(res.data)) fullList = res.data
        } else {
          const base = (window.__API_URL || (window.location.origin + '/app/backend')).replace(/\/$/, '')
          const url = `${base}/routes/api.php?route=${encodeURIComponent('/api/photos')}\u0026entity_type=car\u0026entity_id=${encodeURIComponent(String(car.id))}`
          const headers = (()=>{ try{ const tg=window.Telegram?.WebApp; const u=tg?.initDataUnsafe?.user||{}; const h={}; if(u.id) h['X-Telegram-User-Id']=String(u.id); if(u.first_name) h['X-Telegram-First-Name']=String(u.first_name); if(u.last_name) h['X-Telegram-Last-Name']=String(u.last_name); if(u.username) h['X-Telegram-Username']=String(u.username); if(tg?.initData) h['X-Telegram-Init-Data']=String(tg.initData); return h }catch{return {}} })()
          const res = await fetch(url, { headers }).then(r=>r.json().catch(()=>null))
          if (res && res.success !== false && Array.isArray(res.data)) fullList = res.data
        }
      } catch {}
      // Fallback: если получили слишком мало фото, пробуем принудительно второй способ
      try {
        if (!fullList || fullList.length < 2) {
          const base = (window.__API_URL || (window.location.origin + '/app/backend')).replace(/\/$/, '')
          const url = `${base}/routes/api.php?route=${encodeURIComponent('/api/photos')}\u0026entity_type=car\u0026entity_id=${encodeURIComponent(String(car.id))}`
          const headers = (()=>{ try{ const tg=window.Telegram?.WebApp; const u=tg?.initDataUnsafe?.user||{}; const h={}; if(u.id) h['X-Telegram-User-Id']=String(u.id); if(u.first_name) h['X-Telegram-First-Name']=String(u.first_name); if(u.last_name) h['X-Telegram-Last-Name']=String(u.last_name); if(u.username) h['X-Telegram-Username']=String(u.username); if(tg?.initData) h['X-Telegram-Init-Data']=String(tg.initData); return h }catch{return {}} })()
          const res2 = await fetch(url, { headers }).then(r=>r.json().catch(()=>null))
          if (res2 && res2.success !== false && Array.isArray(res2.data)) fullList = res2.data
        }
      } catch {}
      // Фоллбек на то, что есть
      if (!fullList || !fullList.length) {
        fullList = rawPhotos
      }
      car._allPhotos = fullList
    }
    // Преобразуем в список URL (предпочитаем medium)
    const viewerPhotos = fullList.map(p => {
      if (typeof p === 'string') return { url: p }
      const best = p.urls?.medium || p.url
      return { url: best }
    }).filter(p=>p && p.url)
    if (!viewerPhotos.length) return
    let index = Math.min(Math.max(0, startIndex), viewerPhotos.length - 1)
    const ov = document.createElement('div')
    ov.className = 'photo-viewer-overlay'
    ov.innerHTML = `
      <div class="photo-viewer-content">
        <button class="photo-viewer-close" aria-label="close">×</button>
        <button class="photo-viewer-nav photo-viewer-prev" aria-label="prev">‹</button>
        <img class="photo-viewer-img" src="${escapeHtml(viewerPhotos[index].url)}" alt="photo"/>
        <button class="photo-viewer-nav photo-viewer-next" aria-label="next">›</button>
        <div class="photo-viewer-counter" id="pvCounter">${index+1} / ${viewerPhotos.length}</div>
      </div>`
    document.body.appendChild(ov)
    const close = ()=> ov.remove()
    ov.addEventListener('click', (e)=>{ if (e.target === ov) close() })
    ov.querySelector('.photo-viewer-close')?.addEventListener('click', close)
    const imgEl = ov.querySelector('.photo-viewer-img')
    const counterEl = ov.querySelector('#pvCounter')
    const apply = ()=>{ if (imgEl) imgEl.src = viewerPhotos[index].url; if (counterEl) counterEl.textContent = `${index+1} / ${viewerPhotos.length}` }
    ov.querySelector('.photo-viewer-prev')?.addEventListener('click', ()=>{ index = (index - 1 + viewerPhotos.length) % viewerPhotos.length; apply() })
    ov.querySelector('.photo-viewer-next')?.addEventListener('click', ()=>{ index = (index + 1) % viewerPhotos.length; apply() })
    // свайп жесты (простая версия)
    let sx = 0
    ov.addEventListener('touchstart', (e)=>{ sx = e.touches[0].clientX })
    ov.addEventListener('touchend', (e)=>{ const dx = e.changedTouches[0].clientX - sx; if (Math.abs(dx) > 40){ if (dx < 0) index = (index + 1) % viewerPhotos.length; else index = (index - 1 + viewerPhotos.length) % viewerPhotos.length; apply() } })
  }
  overlay.querySelector('#carMainPhoto')?.addEventListener('click', ()=> openPhotoViewer(0))

  // Клик по карточке владельца внутри модалки → открыть модалку профиля
  if (car.owner) {
    overlay.querySelector('.member-card-container')?.addEventListener('click', ()=>{
      if (window.CabrioModals?.openUserModal) window.CabrioModals.openUserModal(car.owner)
    })
  }

  // Единая отрисовка полей: всегда инпуты, в чтении disabled
  const renderFields = (isEditing) => {
    const disAttr = isEditing ? '' : 'disabled'
    // Basic
    const basic = overlay.querySelector('#carDetailsBasic')
    if (basic) {
      const brandName = car.brand?.name || ''
      const brandValue = isEditing ? brandName : (brandName || 'марка')
      const modelPlaceholder = 'модель'
      basic.innerHTML = `
        <div class="detail-item-compact"><span class="detail-label">${escapeHtml(fieldLabels['brand'])}</span><span class="detail-value"><div class="combo"><input id="brandSearchInput" class="combo-input" type="text" placeholder="${isEditing ? 'Начните ввод...' : ''}" value="${escapeHtml(brandValue)}" autocomplete="off" ${disAttr} /><input type="hidden" data-edit-key="car_brand_id" value="${car.car_brand_id?Number(car.car_brand_id):''}"><div class="combo-list" id="brandSuggestList"></div></div></span></div>
        <div class="detail-item-compact"><span class="detail-label">${escapeHtml(fieldLabels['model'])}</span><span class="detail-value"><input data-edit-key="model" class="filter-input" value="${escapeHtml(car.model||'')}" placeholder="${escapeHtml(modelPlaceholder)}" ${disAttr} /></span></div>
        <div class="detail-item-compact"><span class="detail-label">${escapeHtml(fieldLabels['color'])}</span><span class="detail-value"><input data-edit-key="color" class="filter-input" value="${escapeHtml(car.color||'')}" ${disAttr} /></span></div>
        <div class="detail-item-compact"><span class="detail-label">${escapeHtml(fieldLabels['year'])}</span><span class="detail-value"><input data-edit-key="year" class="filter-input" inputmode="numeric" value="${escapeHtml(car.year||'')}" ${disAttr} /></span></div>
        <div class="detail-item-compact"><span class="detail-label">${escapeHtml(fieldLabels['roof_type'])}</span><span class="detail-value"><select data-edit-key="roof_type" class="filter-input" ${disAttr}><option value="soft" ${car.roof_type==='soft'?'selected':''}>Мягкая</option><option value="hard" ${car.roof_type==='hard'?'selected':''}>Жёсткая</option><option value="targa" ${car.roof_type==='targa'?'selected':''}>Тарга</option><option value="none" ${car.roof_type==='none'?'selected':''}>Нет</option></select></span></div>
      `
      if (isEditing) {
        // по бренду — только в редактировании показываем подсказки
        try{
          const brands = (window.CabrioData?.carBrands || [])
          const input = overlay.querySelector('#brandSearchInput')
          const hidden = overlay.querySelector('[data-edit-key="car_brand_id"]')
          const list = overlay.querySelector('#brandSuggestList')
          const renderList = ()=>{
            const q = (input.value||'').toLowerCase().trim()
            const items = brands.filter(b=>!q || String(b.name||'').toLowerCase().includes(q)).slice(0,50)
            if (!items.length) { list.innerHTML=''; list.style.display='none'; return }
            list.innerHTML = items.map(b=>`<div class=\"combo-item\" data-id=\"${Number(b.id)}\">${escapeHtml(b.name||'')}</div>`).join('')
            list.style.display='block'
          }
          input.addEventListener('focus', renderList)
          input.addEventListener('input', ()=>{ hidden.value=''; renderList() })
          list.addEventListener('click', (e)=>{ const it=e.target.closest('.combo-item'); if(!it) return; hidden.value=it.getAttribute('data-id'); input.value=it.textContent||''; list.style.display='none' })
          document.addEventListener('click', (e)=>{ const combo = overlay.querySelector('.combo'); if(combo && !combo.contains(e.target)) list.style.display='none' })
        }catch{}
      }
    }
    // Engine
    const eng = overlay.querySelector('#carDetailsEngine')
    if (eng) {
      eng.innerHTML = `
        <div class="detail-item-compact"><span class="detail-label">${escapeHtml(fieldLabels['engine_power'])}</span><span class="detail-value"><input data-edit-key="engine_power" class="filter-input" value="${escapeHtml(car.engine_power||'')}" ${disAttr} /></span></div>
        <div class="detail-item-compact"><span class="detail-label">${escapeHtml(fieldLabels['engine_volume'])}</span><span class="detail-value"><input data-edit-key="engine_volume" class="filter-input" value="${escapeHtml(car.engine_volume||'')}" ${disAttr} /></span></div>
      `
    }
    // Registration
    const reg = overlay.querySelector('#carDetailsReg')
    if (reg) {
      reg.innerHTML = `
        <div class="detail-item-compact"><span class="detail-label">${escapeHtml(fieldLabels['vin'])}</span><span class="detail-value"><input data-edit-key="vin" class="filter-input" value="${escapeHtml(car.vin||'')}" ${disAttr} /></span></div>
        <div class="detail-item-compact"><span class="detail-label">${escapeHtml(fieldLabels['reg_number'])}</span><span class="detail-value"><div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><input data-edit-key="reg_number" class="filter-input" value="${escapeHtml(car.reg_number||'')}" ${disAttr} /> <label style="display:flex;align-items:center;gap:6px"><input type="checkbox" data-edit-key="show_reg_number" ${car.show_reg_number? 'checked':''} ${disAttr}/> <span>Показать рег номер участникам</span></label></div></span></div>
      `
    }
    // Other
    const other = overlay.querySelector('#carDetailsOther')
    if (other) {
      other.innerHTML = `
        <div class="detail-item-compact"><span class="detail-label">${escapeHtml(fieldLabels['description'])}</span><span class="detail-value"><textarea data-edit-key="description" class="filter-input" rows="3" ${disAttr}>${escapeHtml(car.description||'')}</textarea></span></div>
      `
    }
    // Toggle visual style
    const content = overlay.querySelector('.modal-content')
    if (content) content.classList.toggle('editing', !!isEditing)
  }

  // Начальная отрисовка — read-only
  renderFields(false)

  // Функция подключения логики редактирования (можно вызывать и динамически)
  function attachEditingControls(){
    const actions = overlay.querySelector('#carHeaderActions')
    if (!actions) return

    // Гарантируем наличие кнопки (если прав не было на момент рендера)
    let editBtn = actions.querySelector('#carEditBtn')
    if (!editBtn) {
      editBtn = document.createElement('button')
      editBtn.id = 'carEditBtn'
      editBtn.className = 'btn-warning'
      editBtn.style.cssText = 'padding:6px 10px;font-size:13px'
      editBtn.textContent = 'Редактировать'
      const closeBtn = actions.querySelector('.modal-close')
      if (closeBtn && closeBtn.parentNode === actions) {
        actions.insertBefore(editBtn, closeBtn)
      } else {
        actions.appendChild(editBtn)
      }
    }
    if (editBtn.dataset.bound === '1') return

    const editableKeys = ['car_brand_id','model','color','year','roof_type','engine_power','engine_volume','vin','description','reg_number','show_reg_number']
    const getValue = (key) => {
      const el = overlay.querySelector(`[data-edit-key="${key}"]`)
      if (!el) return car[key]
      if (el.type === 'checkbox') return !!el.checked
      return el.value
    }
    const snapshot = () => {
      const obj = {}
      editableKeys.forEach(k=>{ obj[k] = car[k] })
      return obj
    }
    let initial = snapshot()
    let isEditing = false

    const renderEditable = () => {
      // Блок основной
      const basic = overlay.querySelector('#carDetailsBasic')
      if (basic) {
        basic.innerHTML = `
          <div class=\"detail-item-compact\"><span class=\"detail-label\">${escapeHtml(fieldLabels['brand'])}</span><span class=\"detail-value\"><div class=\"combo\"><input id=\"brandSearchInput\" class=\"combo-input\" type=\"text\" placeholder=\"Начните ввод...\" value=\"${escapeHtml(car.brand?.name||'')}\" autocomplete=\"off\" /><input type=\"hidden\" data-edit-key=\"car_brand_id\" value=\"${car.car_brand_id?Number(car.car_brand_id):''}\"><div class=\"combo-list\" id=\"brandSuggestList\"></div></div></span></div>
          <div class=\"detail-item-compact\"><span class=\"detail-label\">${escapeHtml(fieldLabels['model'])}</span><span class=\"detail-value\"><input data-edit-key=\"model\" class=\"filter-input\" value=\"${escapeHtml(car.model||'')}\" /></span></div>
          <div class=\"detail-item-compact\"><span class=\"detail-label\">${escapeHtml(fieldLabels['color'])}</span><span class=\"detail-value\"><input data-edit-key=\"color\" class=\"filter-input\" value=\"${escapeHtml(car.color||'')}\" /></span></div>
          <div class=\"detail-item-compact\"><span class=\"detail-label\">${escapeHtml(fieldLabels['year'])}</span><span class=\"detail-value\"><input data-edit-key=\"year\" class=\"filter-input\" inputmode=\"numeric\" value=\"${escapeHtml(car.year||'')}\" /></span></div>
          <div class=\"detail-item-compact\"><span class=\"detail-label\">${escapeHtml(fieldLabels['roof_type'])}</span><span class=\"detail-value\"><select data-edit-key=\"roof_type\" class=\"filter-input\"><option value=\"soft\" ${car.roof_type==='soft'?'selected':''}>Мягкая</option><option value=\"hard\" ${car.roof_type==='hard'?'selected':''}>Жёсткая</option><option value=\"targa\" ${car.roof_type==='targa'?'selected':''}>Тарга</option><option value=\"none\" ${car.roof_type==='none'?'selected':''}>Нет</option></select></span></div>
        `
      }
      // Двигатель
      const eng = overlay.querySelector('#carDetailsEngine')
      if (eng) {
        eng.innerHTML = [
          ['engine_power', `<input data-edit-key=\"engine_power\" class=\"filter-input\" value=\"${escapeHtml(car.engine_power||'')}\" />`],
          ['engine_volume', `<input data-edit-key=\"engine_volume\" class=\"filter-input\" value=\"${escapeHtml(car.engine_volume||'')}\" />`]
        ].map(([k,v])=>`<div class=\"detail-item-compact\"><span class=\"detail-label\">${escapeHtml(fieldLabels[k]||k)}</span><span class=\"detail-value\">${v}</span></div>`).join('')
      }
      // Регистрация
      const reg = overlay.querySelector('#carDetailsReg')
      if (reg) {
        reg.innerHTML = [
          ['vin', `<input data-edit-key=\"vin\" class=\"filter-input\" value=\"${escapeHtml(car.vin||'')}\" />`],
          ['reg_number', `<div style=\"display:flex;align-items:center;gap:10px;flex-wrap:wrap;\"><input data-edit-key=\"reg_number\" class=\"filter-input\" value=\"${escapeHtml(car.reg_number||'')}\" /> <label style=\"display:flex;align-items:center;gap:6px\"><input type=\"checkbox\" data-edit-key=\"show_reg_number\" ${car.show_reg_number? 'checked':''}/> <span>Показать рег номер участникам</span></label></div>`]
        ].map(([k,v])=>`<div class=\"detail-item-compact\"><span class=\"detail-label\">${escapeHtml(fieldLabels[k]||k)}</span><span class=\"detail-value\">${v}</span></div>`).join('')
      }
      // Прочее
      const other = overlay.querySelector('#carDetailsOther')
      if (other) {
        other.innerHTML = [
          ['description', `<textarea data-edit-key=\"description\" class=\"filter-input\" rows=\"3\">${escapeHtml(car.description||'')}</textarea>`]
        ].map(([k,v])=>`<div class=\"detail-item-compact\"><span class=\"detail-label\">${escapeHtml(fieldLabels[k]||k)}</span><span class=\"detail-value\">${v}</span></div>`).join('')
      }
      // Инициализация поискового выпадающего списка брендов
      const attachBrandCombo = () => {
        const brands = (window.CabrioData?.carBrands || [])
        const input = overlay.querySelector('#brandSearchInput')
        const hidden = overlay.querySelector('[data-edit-key="car_brand_id"]')
        const list = overlay.querySelector('#brandSuggestList')
        if (!input || !hidden || !list) return

        const renderList = () => {
          const q = (input.value||'').toLowerCase().trim()
          const items = brands.filter(b => !q || String(b.name||'').toLowerCase().includes(q)).slice(0, 50)
          if (!items.length) { list.innerHTML = ''; list.style.display = 'none'; return }
          list.innerHTML = items.map(b=>`<div class=\"combo-item\" data-id=\"${Number(b.id)}\">${escapeHtml(b.name||'')}</div>`).join('')
          list.style.display = 'block'
        }

        input.addEventListener('focus', renderList)
        input.addEventListener('input', () => { hidden.value=''; renderList() })
        list.addEventListener('click', (e)=>{
          const item = e.target.closest('.combo-item')
          if (!item) return
          const id = item.getAttribute('data-id')
          const name = item.textContent || ''
          input.value = name
          hidden.value = id
          list.style.display = 'none'
        })
        document.addEventListener('click', (e)=>{
          if (!overlay.contains(e.target)) return
          const combo = overlay.querySelector('.combo')
          if (combo && !combo.contains(e.target)) { list.style.display = 'none' }
        })
      }

      attachBrandCombo()
    }

    const renderReadOnly = () => {
      // Перерисовать модалку заново в read-only проще: закрыть и открыть с обновлёнными данными
      const saved = { ...car }
      overlay.remove()
      openCarModal(saved)
    }

    // Кнопка загрузки фото (добавляем при входе в режим редактирования)
    const ensureUploadControls = () => {
      const actions = overlay.querySelector('#carHeaderActions')
      if (!actions) return
      // Скрытый input (один на модалку)
      let input = actions.querySelector('#carPhotoInput')
      if (!input) {
        input = document.createElement('input')
        input.type = 'file'
        input.id = 'carPhotoInput'
        input.accept = 'image/*'
        input.style.display = 'none'
        actions.appendChild(input)
        input.addEventListener('change', async ()=>{
          const file = input.files && input.files[0]
          if (!file) return
          try {
            const overlayEl = overlay.querySelector('#carUploadOverlay'); if (overlayEl) overlayEl.style.display='flex'
            const base = (window.__API_URL || (window.location.origin + '/app/backend')).replace(/\/$/, '')
            const url = `${base}/routes/api.php?route=${encodeURIComponent('/api/photos')}`
            const fd = new FormData()
            fd.append('entity_type','car')
            fd.append('entity_id', String(car.id))
            fd.append('photo', file)
            const headers = (()=>{ try{ const tg=window.Telegram?.WebApp; const u=tg?.initDataUnsafe?.user||{}; const h={}; if(u.id) h['X-Telegram-User-Id']=String(u.id); if(u.first_name) h['X-Telegram-First-Name']=String(u.first_name); if(u.last_name) h['X-Telegram-Last-Name']=String(u.last_name); if(u.username) h['X-Telegram-Username']=String(u.username); if(tg?.initData) h['X-Telegram-Init-Data']=String(tg.initData); return h }catch{return {}} })()
            const resp = await new Promise((resolve)=>{
              const xhr = new XMLHttpRequest()
              xhr.open('POST', url, true)
              Object.entries(headers||{}).forEach(([k,v])=>{ try{ xhr.setRequestHeader(k,v) }catch{} })
              xhr.onreadystatechange = ()=>{
                if (xhr.readyState===4){
                  try{ resolve(JSON.parse(xhr.responseText)) }catch{ resolve(null) }
                }
              }
              xhr.send(fd)
            })
            if (!resp || resp.success === false) {
              alert((resp && resp.error && resp.error.message) || 'Не удалось загрузить фото')
              return
            }
            const newPhoto = resp.data
            car.photo = newPhoto
            if (!Array.isArray(car.photos)) car.photos = []
            car.photos.unshift(newPhoto)
            const saved = { ...car }
            overlay.remove()
            openCarModal(saved)
          } catch {
            alert('Ошибка загрузки')
          } finally {
            const overlayEl = overlay.querySelector('#carUploadOverlay'); if (overlayEl) overlayEl.style.display='none'
            input.value = ''
          }
        })
      }
      // FAB под фото
      const container = overlay.querySelector('.main-photo-compact')
      if (container && !container.querySelector('#carUploadFab')) {
        const fabBtn = document.createElement('button')
        fabBtn.id = 'carUploadFab'
        fabBtn.className = 'photo-upload-fab'
        fabBtn.innerHTML = '<span>📷</span><span>Загрузить фото</span>'
        const wrapper = document.createElement('div')
        wrapper.style.cssText = 'display:flex;justify-content:flex-end'
        wrapper.appendChild(fabBtn)
        container.appendChild(wrapper)
        fabBtn.addEventListener('click', ()=> actions.querySelector('#carPhotoInput')?.click())
        const placeholder = container.querySelector('.car-placeholder')
        if (placeholder) placeholder.addEventListener('click', ()=> actions.querySelector('#carPhotoInput')?.click())
      }
    }

    editBtn.dataset.bound = '1'
    editBtn.addEventListener('click', async ()=>{
      if (!isEditing) {
        isEditing = true
        editBtn.textContent = 'Сохранить'
        editBtn.classList.remove('btn-warning')
        editBtn.classList.add('btn-success')
        renderEditable()
        ensureUploadControls()
      } else {
        // Сохранить
        const payload = {}
        editableKeys.forEach(k=>{ payload[k] = getValue(k) })
        try {
          const base = (window.__API_URL || (window.location.origin + '/app/backend')).replace(/\/$/, '')
          const url = `${base}/routes/api.php?route=${encodeURIComponent(`/api/cars/${car.id}`)}`
          const headers = (()=>{ try{ const tg=window.Telegram?.WebApp; const u=tg?.initDataUnsafe?.user||{}; const h={ 'Content-Type':'application/json' }; if(u.id) h['X-Telegram-User-Id']=String(u.id); if(u.first_name) h['X-Telegram-First-Name']=String(u.first_name); if(u.last_name) h['X-Telegram-Last-Name']=String(u.last_name); if(u.username) h['X-Telegram-Username']=String(u.username); if(tg?.initData) h['X-Telegram-Init-Data']=String(tg.initData); return h }catch{return {'Content-Type':'application/json'}} })()
          const res = await fetch(url, { method:'PATCH', headers, body: JSON.stringify(payload) }).then(r=>r.json().catch(()=>null))
          if (!res || res.success === false || res.__httpStatus === 403) {
            alert((res && res.error && res.error.message) || 'Не удалось сохранить')
            return
          }
          // Успех: обновляем локальные данные и перерисовываем в read-only
          Object.assign(car, res.data || {})
          isEditing = false
          renderReadOnly()
        } catch (e) {
          alert('Ошибка сохранения')
        }
      }
    })
  }

  // Если права уже есть — подключаем сразу
  if (canEdit) {
    attachEditingControls()
  } else {
    // Вариант Б: открываем модалку сразу и фоном проверяем права модератора/админа через API
    ;(async ()=>{
      try {
        let resp = null
        if (window.CabrioAPI?.apiGet) {
          resp = await window.CabrioAPI.apiGet(`/api/cars/${car.id}`)
        } else {
          const base = (window.__API_URL || (window.location.origin + '/app/backend')).replace(/\/$/, '')
          const url = `${base}/routes/api.php?route=${encodeURIComponent(`/api/cars/${car.id}`)}`
          const headers = (()=>{ try{ const tg=window.Telegram?.WebApp; const u=tg?.initDataUnsafe?.user||{}; const h={}; if(u.id) h['X-Telegram-User-Id']=String(u.id); if(u.first_name) h['X-Telegram-First-Name']=String(u.first_name); if(u.last_name) h['X-Telegram-Last-Name']=String(u.last_name); if(u.username) h['X-Telegram-Username']=String(u.username); if(tg?.initData) h['X-Telegram-Init-Data']=String(tg.initData); return h }catch{return {}} })()
          resp = await fetch(url, { headers }).then(r=>r.json().catch(()=>null))
        }
        if (resp && resp.success !== false && resp.data && resp.data.permissions && resp.data.permissions.canEdit) {
          attachEditingControls()
        }
      } catch {}
    })()
  }
}

// Глобально для удобства
window.CabrioModals = window.CabrioModals || {}
window.CabrioModals.openCarModal = openCarModal


