// car_modal.js — модальное окно автомобиля

function escapeHtml(str){
  return String(str||'').replace(/[&<>"]|'/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

export function openCarModal(car){
  const overlay = document.createElement('div')
  overlay.className = 'modal-overlay'
  const title = `${car.brand?.name||''} ${car.model||''}`.trim()
  const ownerAvatar = car.owner?.photo?.url || ''
  const rawPhotos = Array.isArray(car.photos) && car.photos.length ? car.photos : (car.photo?.url ? [car.photo] : [])
  const photos = rawPhotos.map(p=> typeof p === 'string' ? { url: p } : { url: p.url })

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
        <div style="display:flex;gap:6px;align-items:center;">
          ${canEdit ? `<button class=\"btn-warning\" id=\"carEditBtn\" style=\"padding:6px 10px;font-size:13px\">Редактировать</button>` : ''}
          <button class="modal-close" aria-label="close">×</button>
        </div>
      </div>
      <div class="modal-body">
        ${photos.length ? `<div class=\"main-photo-compact\"><img src=\"${escapeHtml(photos[0].url)}\" class=\"main-image\" alt=\"${escapeHtml(title)}\"/></div>` : ''}
        ${photos.length > 1 ? `<div class=\"photo-thumbnails-compact\">${photos.map((p,i)=>`<img src=\\\"${escapeHtml(p.url)}\\\" class=\\\"thumbnail-compact${i===0?' active':''}\\\" data-index=\\\"${i}\\\" alt=\\\"thumb\\\"/>`).join('')}</div>` : ''}

        <div class="car-info-section-compact">
          <div class="car-header-compact">
            <div class="car-title-compact">
              <h3 class="car-name-compact">${escapeHtml(title)}</h3>
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
      basic.innerHTML = `
        <div class="detail-item-compact"><span class="detail-label">${escapeHtml(fieldLabels['brand'])}</span><span class="detail-value"><div class="combo"><input id="brandSearchInput" class="combo-input" type="text" placeholder="Начните ввод..." value="${escapeHtml(brandName)}" autocomplete="off" ${disAttr} /><input type="hidden" data-edit-key="car_brand_id" value="${car.car_brand_id?Number(car.car_brand_id):''}"><div class="combo-list" id="brandSuggestList"></div></div></span></div>
        <div class="detail-item-compact"><span class="detail-label">${escapeHtml(fieldLabels['model'])}</span><span class="detail-value"><input data-edit-key="model" class="filter-input" value="${escapeHtml(car.model||'')}" ${disAttr} /></span></div>
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

  // Режим редактирования авто — только если есть права
  if (canEdit) {
    const editBtn = overlay.querySelector('#carEditBtn')
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
          ['engine_power', `<input data-edit-key="engine_power" class="filter-input" value="${escapeHtml(car.engine_power||'')}" />`],
          ['engine_volume', `<input data-edit-key="engine_volume" class="filter-input" value="${escapeHtml(car.engine_volume||'')}" />`]
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
          ['description', `<textarea data-edit-key="description" class="filter-input" rows=\"3\">${escapeHtml(car.description||'')}</textarea>`]
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

    editBtn?.addEventListener('click', async ()=>{
      if (!isEditing) {
        isEditing = true
        editBtn.textContent = 'Сохранить'
        editBtn.classList.remove('btn-warning')
        editBtn.classList.add('btn-success')
        renderEditable()
      } else {
        // Сохранить
        const payload = {}
        editableKeys.forEach(k=>{ payload[k] = getValue(k) })
        try {
          const base = (window.VITE_BACKEND_API_URL || (window.location.origin + '/app')).replace(/\/$/, '')
          const url = `${base}/backend/routes/api.php?route=${encodeURIComponent(`/api/cars/${car.id}`)}`
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
}

// Глобально для удобства
window.CabrioModals = window.CabrioModals || {}
window.CabrioModals.openCarModal = openCarModal


