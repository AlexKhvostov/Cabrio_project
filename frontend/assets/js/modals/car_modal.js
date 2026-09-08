// Большая карточка автомобиля. Пустые поля на месте. Хозяин — ссылка на его карточку.

import { phCar, photoUrl } from '../components/media.js?v=cabrio15'
import {
  escapeHtml, viewVal, sheetField, carTitle, renderPersonLink, bindRelLinks, headerActions, filled, openPhotoViewer
} from '../components/sheet.js?v=write1'

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

export function openCarModal(car){
  const overlay = document.createElement('div')
  overlay.className = 'modal-overlay'
  const title = carTitle(car)
  const yearText = car.year ? String(car.year) : ''
  const rawPhotos = Array.isArray(car.photos) && car.photos.length ? car.photos : (car.photo ? [car.photo] : [])
  const statusLabel = car.status?.name || car.status?.code || ''
  const canEdit = !!(car.permissions && car.permissions.canEdit)
  const owner = car.owner || null
  let isEditing = false

  const paintHeader = () => {
    const actions = overlay.querySelector('#carHeaderActions')
    if (!actions) return
    actions.innerHTML = headerActions({ canEdit, editing: isEditing, withClose: true })
    actions.querySelector('.modal-close')?.addEventListener('click', close)
    actions.querySelector('[data-sheet-edit]')?.addEventListener('click', () => enterEdit())
    actions.querySelector('[data-sheet-cancel]')?.addEventListener('click', () => exitEdit())
    actions.querySelector('[data-sheet-save]')?.addEventListener('click', () => saveEdit())
  }

  overlay.innerHTML = `
    <div class="modal-content modal-compact sheet-card">
      <div class="modal-header">
        <div class="modal-title">Автомобиль</div>
        <div id="carHeaderActions" class="sheet-actions"></div>
      </div>
      <div class="modal-body">
        <div class="main-photo-compact">
          ${phCar(car, 'medium', true)}
          <span id="carStatusControl">${statusLabel ? `<span class="sheet-photo-badge">${escapeHtml(statusLabel)}</span>` : ''}</span>
          <div class="sheet-photo-caption">
            <div class="sheet-photo-title">${escapeHtml(title)}</div>
            <div class="sheet-photo-meta">${yearText ? escapeHtml(yearText) : 'год не указан'}</div>
          </div>
          <div class="photo-upload-overlay" id="carUploadOverlay" style="display:none"> <div class="spinner"></div> <span>Загрузка…</span> </div>
        </div>
        ${owner ? renderPersonLink(owner) : ''}
        <div class="sheet-section-title">Характеристики</div>
        <div class="sheet-grid" id="carFieldsMain"></div>
        <div class="sheet-section-title">Идентификация</div>
        <div class="sheet-grid" id="carFieldsId"></div>
        <div class="sheet-section-title">Дополнительно</div>
        <div class="sheet-grid" id="carFieldsExtra"></div>
      </div>
    </div>`

  function close(){ overlay.remove() }
  overlay.addEventListener('click', (e)=>{ if(e.target===overlay) close() })
  document.body.appendChild(overlay)
  paintHeader()
  bindRelLinks(overlay)

  const showCarPhotos = async (startIndex = 0) => {
    if (isEditing) return
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

    overlay.querySelector('#carFieldsMain').innerHTML = [
      sheetField(FIELD_LABELS.brand, brandInner),
      sheetField(FIELD_LABELS.model, isEditing ? input('model') : viewVal(car.model)),
      sheetField(FIELD_LABELS.color, isEditing ? input('color') : viewVal(car.color)),
      sheetField(FIELD_LABELS.year, isEditing ? input('year', 'inputmode="numeric"') : viewVal(car.year)),
      sheetField(FIELD_LABELS.roof_type, roofInner),
      sheetField(FIELD_LABELS.status, statusInner),
      sheetField(FIELD_LABELS.engine_power, isEditing ? input('engine_power') : viewVal(car.engine_power)),
      sheetField(FIELD_LABELS.engine_volume, isEditing ? input('engine_volume') : viewVal(car.engine_volume)),
    ].join('')
    overlay.querySelector('#carFieldsId').innerHTML = [
      sheetField(FIELD_LABELS.reg_number, numInner, 'full'),
      sheetField(FIELD_LABELS.vin, isEditing ? input('vin') : viewVal(car.vin), 'full'),
    ].join('')
    overlay.querySelector('#carFieldsExtra').innerHTML = [
      sheetField(FIELD_LABELS.description, descInner, 'full'),
    ].join('')

    overlay.querySelector('.sheet-card')?.classList.toggle('editing', isEditing)
    if (isEditing) attachBrandCombo()
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
      try {
        overlay.querySelector('#carUploadOverlay').style.display='flex'
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
        overlay.remove()
        openCarModal(car)
      } catch { alert('Ошибка загрузки') }
      finally { overlay.querySelector('#carUploadOverlay').style.display='none'; localInput.value='' }
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
  }

  async function enterEdit(){
    isEditing = true
    try {
      if (!(Array.isArray(window.CabrioData?.carBrands) && window.CabrioData.carBrands.length) && window.CabrioAPI?.apiGet) {
        const res = await window.CabrioAPI.apiGet('/api/ref/car-brands')
        if (res && res.success && Array.isArray(res.data)) window.CabrioData.carBrands = res.data
      }
    } catch {}
    paintHeader()
    renderFields()
    ensureUploadControls()
    window.CabrioUI?.kickModalLayout?.(overlay)
  }

  function exitEdit(){
    isEditing = false
    removeUploadControls()
    paintHeader()
    renderFields()
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
    const payload = {}
    ;['car_brand_id','model','color','year','roof_type','engine_power','engine_volume','vin','description','reg_number','show_reg_number','status_id'].forEach(k=>{ payload[k] = getValue(k) })
    try {
      const base = (window.__API_URL || (window.location.origin + '/app/backend')).replace(/\/$/, '')
      const url = `${base}/routes/api.php?route=${encodeURIComponent(`/api/cars/${car.id}`)}`
      const res = await fetch(url, { method:'PATCH', headers: { 'Content-Type':'application/json' }, body: JSON.stringify(Object.assign({}, payload, readTelegramUser())) }).then(r=>r.json().catch(()=>null))
      if (!res || res.success === false || res.__httpStatus === 403) {
        alert((res && res.error && res.error.message) || 'Не удалось сохранить')
        return
      }
      overlay.remove()
      openCarModal(Object.assign(car, res.data || {}))
    } catch { alert('Ошибка сохранения') }
  }

  renderFields()
}

window.CabrioModals = window.CabrioModals || {}
window.CabrioModals.openCarModal = openCarModal
