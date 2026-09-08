// Большая карточка автомобиля. Пустые поля на месте. Хозяин — ссылка на его карточку.
// Создание: kit п. 66 — шапка «Создание авто», Отмена/Сохранить внизу.

import { phCar, photoUrl } from '../components/media.js?v=cabrio20'
import {
  escapeHtml, viewVal, sheetField, carTitle, renderPersonLink, bindRelLinks, headerActions, filled, openPhotoViewer
} from '../components/sheet.js?v=write2'

function isRegNumberPublic(v){
  return v === true || v === 1 || v === '1'
}

function roofLabel(code){
  return ({ soft:'Мягкая', hard:'Жёсткая', targa:'Тарга', none:'Нет' }[code]) || code || ''
}

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

const FIELD_LABELS = {
    brand: 'Марка',
    model: 'Модель',
    color: 'Цвет',
  year: 'Год',
  roof_type: 'Крыша',
    engine_power: 'Мощность',
  engine_volume: 'Объём',
    vin: 'VIN',
    reg_number: 'Гос. номер',
    description: 'Описание',
  status: 'Статус'
  }

const CAR_STATUSES = [
    { id: 1, code: 'noticed', name: 'Замечен' },
    { id: 2, code: 'business_card', name: 'Визитка' },
    { id: 3, code: 'deleted', name: 'Удалён' },
    { id: 4, code: 'archived', name: 'В архиве' },
    { id: 5, code: 'blocked', name: 'Заблокирован' },
    { id: 6, code: 'pending', name: 'На модерации' },
    { id: 7, code: 'active', name: 'Активен' }
  ]

export function openCarModal(car, options = {}){
  const overlay = document.createElement('div')
  car = Object.assign({}, car || {})
  let isNew = !car.id
  overlay.className = 'modal-overlay modal-above-nav' + (isNew ? ' modal-create' : '')
  let isEditing = isNew || !!options.startEdit
  const canEdit = isNew || !!(car.permissions && car.permissions.canEdit)
  let pendingFile = null
  let rawPhotos = Array.isArray(car.photos) && car.photos.length ? car.photos : (car.photo ? [car.photo] : [])

  const modalTitle = () => {
    if (isNew) return 'Создание авто'
    if (isEditing) return 'Редактирование авто'
    return 'Автомобиль'
  }

  const paintHeader = () => {
    const actions = overlay.querySelector('#carHeaderActions')
    const titleEl = overlay.querySelector('#carModalTitle')
    const foot = overlay.querySelector('#carCreateFoot')
    const card = overlay.querySelector('.modal-content')
    const head = overlay.querySelector('.modal-header')
    if (titleEl) titleEl.textContent = modalTitle()
    if (foot) foot.hidden = !isNew
    overlay.classList.add('modal-above-nav')
    overlay.classList.toggle('modal-create', isNew)
    card?.classList.toggle('create-sheet', isNew)
    card?.classList.toggle('editing', isEditing)
    head?.classList.toggle('create-head', isNew)
    if (!actions) return
    if (isNew) {
      actions.innerHTML = `<button class="modal-close" type="button" aria-label="close">×</button>`
      actions.querySelector('.modal-close')?.addEventListener('click', close)
      return
    }
    actions.innerHTML = headerActions({ canEdit, editing: isEditing, withClose: true })
    actions.querySelector('.modal-close')?.addEventListener('click', close)
    actions.querySelector('[data-sheet-edit]')?.addEventListener('click', () => enterEdit())
    actions.querySelector('[data-sheet-cancel]')?.addEventListener('click', () => exitEdit())
    actions.querySelector('[data-sheet-save]')?.addEventListener('click', () => saveEdit())
  }

  overlay.innerHTML = `
    <div class="modal-content modal-compact sheet-card${isNew ? ' create-sheet' : ''}${isEditing ? ' editing' : ''}">
      <div class="modal-header${isNew ? ' create-head' : ''}">
        <div class="modal-title" id="carModalTitle">${modalTitle()}</div>
        <div id="carHeaderActions" class="sheet-actions"></div>
      </div>
      <div class="modal-body">
        <div class="main-photo-compact">
          <span id="carPhotoInner"></span>
          <span id="carStatusControl"></span>
          <div class="sheet-photo-caption">
            <div class="sheet-photo-title" id="carCoverTitle"></div>
            <div class="sheet-photo-meta" id="carCoverMeta"></div>
          </div>
          <div class="photo-upload-overlay" id="carUploadOverlay" style="display:none"> <div class="spinner"></div> <span>Загрузка…</span> </div>
        </div>
        <div id="carOwner"></div>
        <div class="sheet-section-title">Характеристики</div>
        <div class="sheet-grid" id="carFieldsMain"></div>
        <div class="sheet-section-title">Идентификация</div>
        <div class="sheet-grid" id="carFieldsId"></div>
        <div class="sheet-section-title">Дополнительно</div>
        <div class="sheet-grid" id="carFieldsExtra"></div>
      </div>
      <div class="modal-footer create-foot" id="carCreateFoot" ${isNew ? '' : 'hidden'}>
        <button type="button" class="btn-ghost" data-create-cancel>Отмена</button>
        <button type="button" class="btn-primary" data-create-save>Сохранить</button>
      </div>
    </div>`

  function close(){ overlay.remove() }
  overlay.addEventListener('click', (e)=>{ if(e.target===overlay) close() })
  overlay.querySelector('[data-create-cancel]')?.addEventListener('click', close)
  overlay.querySelector('[data-create-save]')?.addEventListener('click', () => saveEdit())
  document.body.appendChild(overlay)
  paintHeader()
  bindRelLinks(overlay)

  function paintCover(){
    const inner = overlay.querySelector('#carPhotoInner')
    if (car._preview) {
      inner.innerHTML = `<div class="ph ph-car"><img class="ph-img ph-ok" src="${escapeHtml(car._preview)}" alt=""></div>`
    } else {
      inner.innerHTML = phCar(car, 'medium', true)
    }
    const statusLabel = car.status?.name || car.status?.code || ''
    overlay.querySelector('#carStatusControl').innerHTML = (!isNew && statusLabel)
      ? `<span class="sheet-photo-badge">${escapeHtml(statusLabel)}</span>` : ''
    overlay.querySelector('#carCoverTitle').textContent = isNew ? (carTitle(car) === 'Автомобиль' ? 'Новое авто' : carTitle(car)) : carTitle(car)
    overlay.querySelector('#carCoverMeta').textContent = car.year ? String(car.year) : (isNew ? 'укажите марку и модель' : 'год не указан')
    const ownerBox = overlay.querySelector('#carOwner')
    ownerBox.innerHTML = (!isNew && car.owner) ? renderPersonLink(car.owner) : ''
  }

  const showCarPhotos = async (startIndex = 0) => {
    if (isEditing || isNew) return
    let fullList = Array.isArray(car._allPhotos) && car._allPhotos.length ? car._allPhotos : null
    if (!fullList) {
      try {
        const res = await window.CabrioAPI?.apiGet(`/api/photos?entity_type=car&entity_id=${encodeURIComponent(String(car.id))}`)
          if (res && res.success !== false && Array.isArray(res.data)) fullList = res.data
      } catch {}
      if (!fullList || !fullList.length) fullList = rawPhotos
      car._allPhotos = fullList
    }
    const urls = fullList.map(p => {
      if (typeof p === 'string') return p
      return photoUrl(p, 'medium') || p.url || ''
    }).filter(Boolean)
    openPhotoViewer(urls, startIndex)
  }

  overlay.querySelector('.main-photo-compact')?.addEventListener('click', (e)=>{
    if (e.target.closest('#carUploadFab, #carPhotoInputLocal')) return
    showCarPhotos(0)
  })

  const input = (key, extra='') => `<input data-edit-key="${key}" class="filter-input" value="${escapeHtml(car[key]??'')}" ${extra} />`

  const renderFields = () => {
    const brandName = car.brand?.name || car.brand_name || ''
    const brandId = car.car_brand_id || car.brand?.id || ''
    const publicNum = isRegNumberPublic(car.show_reg_number)
    const shownNumber = (!publicNum && !canEdit) ? (filled(car.reg_number) ? 'скрыт' : '') : (car.reg_number || '')
    const statusLabel = car.status?.name || car.status?.code || ''
    const brandInner = isEditing
      ? `<div class="combo"><input id="brandSearchInput" class="combo-input" type="text" placeholder="Начните ввод..." value="${escapeHtml(brandName)}" autocomplete="off" /><input type="hidden" data-edit-key="car_brand_id" value="${brandId?Number(brandId):''}"><div class="combo-list" id="brandSuggestList"></div></div>`
      : viewVal(brandName)
    const roofInner = isEditing
      ? `<select data-edit-key="roof_type" class="filter-select"><option value="">—</option><option value="soft" ${car.roof_type==='soft'?'selected':''}>Мягкая</option><option value="hard" ${car.roof_type==='hard'?'selected':''}>Жёсткая</option><option value="targa" ${car.roof_type==='targa'?'selected':''}>Тарга</option><option value="none" ${car.roof_type==='none'?'selected':''}>Нет</option></select>`
      : viewVal(roofLabel(car.roof_type))
    const statusInner = isEditing
      ? `<select data-edit-key="status_id" class="filter-select">${CAR_STATUSES.map(s=>`<option value="${s.id}" ${Number(s.id)===Number(car.status?.id||0)?'selected':''}>${escapeHtml(s.name)}</option>`).join('')}</select>`
      : viewVal(statusLabel)
    const numInner = isEditing
      ? `<div class="sheet-edit-stack">${input('reg_number')}<label class="sheet-check"><input type="checkbox" data-edit-key="hide_reg_number" ${publicNum? '':'checked'}/> Скрыть номер</label></div>`
      : viewVal(shownNumber)
    const descInner = isEditing
      ? `<textarea data-edit-key="description" class="filter-input" rows="2">${escapeHtml(car.description||'')}</textarea>`
      : viewVal(car.description)

    const main = [
      sheetField(FIELD_LABELS.brand, brandInner),
      sheetField(FIELD_LABELS.model, isEditing ? input('model') : viewVal(car.model)),
      sheetField(FIELD_LABELS.color, isEditing ? input('color') : viewVal(car.color)),
      sheetField(FIELD_LABELS.year, isEditing ? input('year', 'inputmode="numeric"') : viewVal(car.year)),
      sheetField(FIELD_LABELS.roof_type, roofInner),
    ]
    // При создании статус ставит сервер («на модерации») — поле не показываем
    if (!isNew) main.push(sheetField(FIELD_LABELS.status, statusInner))
    main.push(
      sheetField(FIELD_LABELS.engine_power, isEditing ? input('engine_power') : viewVal(car.engine_power)),
      sheetField(FIELD_LABELS.engine_volume, isEditing ? input('engine_volume') : viewVal(car.engine_volume)),
    )
    overlay.querySelector('#carFieldsMain').innerHTML = main.join('')
    overlay.querySelector('#carFieldsId').innerHTML = [
      sheetField(FIELD_LABELS.reg_number, numInner, 'full'),
      sheetField(FIELD_LABELS.vin, isEditing ? input('vin') : viewVal(car.vin), 'full'),
    ].join('')
    overlay.querySelector('#carFieldsExtra').innerHTML = [
      sheetField(FIELD_LABELS.description, descInner, 'full'),
    ].join('')

    overlay.querySelector('.sheet-card')?.classList.toggle('editing', isEditing)
    paintCover()
    if (isEditing) attachBrandCombo()
    if (isEditing) ensureUploadControls()
    else removeUploadControls()
  }

      const attachBrandCombo = () => {
        const brands = (window.CabrioData?.carBrands || [])
    const brandInput = overlay.querySelector('#brandSearchInput')
        const hidden = overlay.querySelector('[data-edit-key="car_brand_id"]')
        const list = overlay.querySelector('#brandSuggestList')
    if (!brandInput || !hidden || !list) return
        const renderList = () => {
      const q = (brandInput.value||'').toLowerCase().trim()
          const items = brands.filter(b => !q || String(b.name||'').toLowerCase().includes(q)).slice(0, 50)
          if (!items.length) { list.innerHTML = ''; list.style.display = 'none'; return }
      list.innerHTML = items.map(b=>`<div class="combo-item" data-id="${Number(b.id)}">${escapeHtml(b.name||'')}</div>`).join('')
          list.style.display = 'block'
        }
    brandInput.addEventListener('focus', renderList)
    brandInput.addEventListener('input', () => { hidden.value=''; renderList() })
        list.addEventListener('click', (e)=>{
          const item = e.target.closest('.combo-item')
          if (!item) return
      hidden.value = item.getAttribute('data-id')
      brandInput.value = item.textContent || ''
          list.style.display = 'none'
        })
  }

    const ensureUploadControls = () => {
      const photoContainer = overlay.querySelector('.main-photo-compact')
    if (!photoContainer || photoContainer.querySelector('#carUploadFab')) return
    const localInput = document.createElement('input')
        localInput.type = 'file'
        localInput.id = 'carPhotoInputLocal'
        localInput.accept = 'image/*'
        localInput.style.display = 'none'
        photoContainer.appendChild(localInput)
        localInput.addEventListener('change', async ()=>{
          const file = localInput.files && localInput.files[0]
          if (!file) return
      if (!car.id) {
        pendingFile = file
        car._preview = URL.createObjectURL(file)
        paintCover()
        localInput.value = ''
        return
      }
      try {
        overlay.querySelector('#carUploadOverlay').style.display='flex'
        await sendCarPhoto(file)
        overlay.remove()
        openCarModal(car, options)
      } catch { alert('Ошибка загрузки') }
      finally {
        const ov = overlay.querySelector('#carUploadOverlay')
        if (ov) ov.style.display='none'
        localInput.value=''
      }
    })
    const fabBtn = document.createElement('button')
    fabBtn.id = 'carUploadFab'
    fabBtn.className = 'photo-upload-fab photo-upload-center'
    fabBtn.type = 'button'
    fabBtn.innerHTML = '<span>📷</span><span>Фото</span>'
    photoContainer.appendChild(fabBtn)
    fabBtn.addEventListener('click', (e)=>{ e.stopPropagation(); localInput.click() })
  }

  const removeUploadControls = () => {
    overlay.querySelector('#carUploadFab')?.remove()
    overlay.querySelector('#carPhotoInputLocal')?.remove()
  }

  async function loadBrands(){
    try {
      if (!(Array.isArray(window.CabrioData?.carBrands) && window.CabrioData.carBrands.length) && window.CabrioAPI?.apiGet) {
        const res = await window.CabrioAPI.apiGet('/api/ref/car-brands')
        if (res && res.success && Array.isArray(res.data)) window.CabrioData.carBrands = res.data
      }
    } catch {}
  }

  async function enterEdit(){
    isEditing = true
    await loadBrands()
    paintHeader()
    renderFields()
    window.CabrioUI?.kickModalLayout?.(overlay)
  }

  function exitEdit(){
    if (isNew) { close(); return }
    isEditing = false
    paintHeader()
    renderFields()
  }

  async function sendCarPhoto(file){
            const base = (window.__API_URL || (window.location.origin + '/app/backend')).replace(/\/$/, '')
            const fd = new FormData()
            fd.append('entity_type','car')
            fd.append('entity_id', String(car.id))
            fd.append('photo', file)
    Object.entries(readTelegramUser()).forEach(([k,v])=>{ if (v!==undefined) fd.append(k, v) })
    const resp = await fetch(`${base}/routes/api.php?route=${encodeURIComponent('/api/photos')}`, { method:'POST', body: fd }).then(r=>r.json().catch(()=>null))
            if (!resp || resp.success === false) {
              alert((resp && resp.error && resp.error.message) || 'Не удалось загрузить фото')
              return
            }
    car.photo = resp.data
    rawPhotos = [resp.data]
    try {
      const fresh = await window.CabrioAPI.apiGet(`/api/cars/${car.id}`)
      if (fresh && fresh.success !== false && fresh.data) Object.assign(car, fresh.data)
    } catch {}
  }

  async function saveEdit(){
    const getValue = (key) => {
      if (key === 'show_reg_number') {
        const hideEl = overlay.querySelector('[data-edit-key="hide_reg_number"]')
        if (hideEl) return hideEl.checked ? 0 : 1
        return isRegNumberPublic(car.show_reg_number) ? 1 : 0
      }
      const el = overlay.querySelector(`[data-edit-key="${key}"]`)
      if (!el) return car[key]
      return el.value
    }
    const keys = ['car_brand_id','model','color','year','roof_type','engine_power','engine_volume','vin','description','reg_number','show_reg_number']
    if (!isNew) keys.push('status_id')
    const payload = {}
    keys.forEach(k=>{ payload[k] = getValue(k) })
    try {
      let res
      if (isNew) {
        res = await window.CabrioAPI.apiPost('/api/cars', payload)
      } else {
        res = await window.CabrioAPI.apiPatch(`/api/cars/${car.id}`, payload)
      }
          if (!res || res.success === false || res.__httpStatus === 403) {
            alert((res && res.error && res.error.message) || 'Не удалось сохранить')
            return
          }
          Object.assign(car, res.data || {})
      const wasNew = isNew
      isNew = false
          isEditing = false
      if (pendingFile) {
        await sendCarPhoto(pendingFile)
        pendingFile = null
        car._preview = null
      }
      if (wasNew) {
        paintHeader()
        renderFields()
        options.onChanged?.(car)
        return
      }
      overlay.remove()
      openCarModal(car, options)
      options.onChanged?.(car)
    } catch { alert('Ошибка сохранения') }
  }

  loadBrands().then(()=>{
    paintHeader()
    renderFields()
  })
}

window.CabrioModals = window.CabrioModals || {}
window.CabrioModals.openCarModal = openCarModal
