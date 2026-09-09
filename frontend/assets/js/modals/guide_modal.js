// Карточка места: фото, название, описание, ярлыки. Отзывы — в просмотре.

import { phPlace, photoUrl } from '../components/media.js?v=cabrio21'
import {
  escapeHtml, viewVal, sheetField, headerActions, openPhotoViewer, renderLabelChips, labelCode,
  renderPersonLink, bindRelLinks
} from '../components/sheet.js?v=tags2'

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

function clampStars(value){
  const n = Number(value)
  if (!isFinite(n)) return 3
  return Math.max(1, Math.min(5, Math.round(n)))
}

// Три оценки 1–5 звёздами, как на плитке списка
function starPicker(key, label, value){
  const v = clampStars(value)
  const stars = [1, 2, 3, 4, 5].map(i =>
    `<button type="button" class="review-star-pick${i <= v ? ' is-on' : ''}" data-star-key="${key}" data-star="${i}" aria-label="${i} из 5">★</button>`
  ).join('')
  return `<label class="review-slider"><span>${escapeHtml(label)} <b data-sl-out="${key}">${v}</b></span>
    <input type="hidden" data-edit-key="${key}" value="${v}">
    <div class="review-star-row">${stars}</div>
  </label>`
}

export function openGuideModal(place, options = {}){
  const overlay = document.createElement('div')
  let data = Object.assign({}, place || {})
  let isNew = !data.id
  overlay.className = 'modal-overlay modal-above-nav' + (isNew ? ' modal-create' : '')
  let isEditing = isNew
  const canEdit = isNew || !!(data.permissions && data.permissions.canEdit)
  const canDelete = !!(data.permissions && data.permissions.canDelete)
  let pendingFile = null
  let draftLabels = (Array.isArray(data.labels) ? data.labels : []).map(labelCode).filter(Boolean)

  const modalTitle = () => {
    if (isNew) return 'Добавить'
    return 'Карточка'
  }

  const paintHeader = () => {
    const actions = overlay.querySelector('#guideHeaderActions')
    const titleEl = overlay.querySelector('#guideModalTitle')
    const foot = overlay.querySelector('#guideCreateFoot')
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
    let extra = ''
    if (canDelete && !isEditing) {
      extra = `<button type="button" class="btn-ghost" data-sheet-delete>Удалить</button>`
    }
    actions.innerHTML = extra + headerActions({ canEdit, editing: isEditing, withClose: true })
    actions.querySelector('.modal-close')?.addEventListener('click', close)
    actions.querySelector('[data-sheet-edit]')?.addEventListener('click', () => {
      isEditing = true
      draftLabels = (Array.isArray(data.labels) ? data.labels : []).map(labelCode).filter(Boolean)
      loadLabelHints().then(()=>{ paintHeader(); renderAll() })
    })
    actions.querySelector('[data-sheet-cancel]')?.addEventListener('click', () => {
      isEditing = false
      draftLabels = (Array.isArray(data.labels) ? data.labels : []).map(labelCode).filter(Boolean)
      paintHeader(); renderAll()
    })
    actions.querySelector('[data-sheet-save]')?.addEventListener('click', () => savePlace())
    actions.querySelector('[data-sheet-delete]')?.addEventListener('click', () => deletePlace())
  }

  overlay.innerHTML = `
    <div class="modal-content modal-compact sheet-card${isNew ? ' create-sheet' : ''}${isEditing ? ' editing' : ''}">
      <div class="modal-header${isNew ? ' create-head' : ''}">
        <div class="modal-title" id="guideModalTitle">${modalTitle()}</div>
        <div id="guideHeaderActions" class="sheet-actions"></div>
      </div>
      <div class="modal-body">
        <div class="main-photo-compact">
          <span id="guidePhotoInner"></span>
          <span id="guideRateBadge"></span>
          <div class="sheet-photo-caption">
            <div class="sheet-photo-title" id="guideCoverTitle"></div>
            <div class="sheet-photo-meta" id="guideCoverMeta"></div>
          </div>
          <div class="photo-upload-overlay" id="guideUploadOverlay" style="display:none"><div class="spinner"></div><span>Загрузка…</span></div>
        </div>
        <div id="guideNameRow"></div>
        <div id="guideAuthor"></div>
        <div class="sheet-grid" id="guideMain"></div>
        <div id="guideReviews"></div>
      </div>
      <div class="modal-footer create-foot" id="guideCreateFoot" ${isNew ? '' : 'hidden'}>
        <button type="button" class="btn-ghost" data-create-cancel>Отмена</button>
        <button type="button" class="btn-primary" data-create-save>Сохранить</button>
      </div>
    </div>`

  function close(){ overlay.remove() }
  overlay.addEventListener('click', (e)=>{ if (e.target === overlay) close() })
  overlay.querySelector('[data-create-cancel]')?.addEventListener('click', close)
  overlay.querySelector('[data-create-save]')?.addEventListener('click', () => savePlace())
  document.body.appendChild(overlay)
  bindRelLinks(overlay)
  paintHeader()
  renderAll()

  function cover(){
    const inner = overlay.querySelector('#guidePhotoInner')
    if (data._preview) {
      inner.innerHTML = `<div class="ph ph-place"><img class="ph-img ph-ok" src="${escapeHtml(data._preview)}" alt=""></div>`
    } else {
      inner.innerHTML = phPlace(data, 'medium', true)
    }
    overlay.querySelector('#guideCoverTitle').textContent = ''
    overlay.querySelector('#guideCoverMeta').textContent = ''
    overlay.querySelector('.sheet-photo-caption')?.classList.add('is-off')
    const badge = overlay.querySelector('#guideRateBadge')
    const avg = data.rating || {}
    if (badge) {
      badge.innerHTML = (!isNew && avg.overall != null)
        ? `<span class="sheet-photo-badge">${escapeHtml(String(avg.overall))} / 5 · ${Number(avg.count || 0)} отз.</span>`
        : ''
    }
  }

  function labelsInner(){
    const names = isEditing ? draftLabels : (data.labels || []).map(labelCode).filter(Boolean)
    if (!isEditing) return renderLabelChips(names)
    return `<div class="label-editor" id="guideLabelChips">
      ${renderLabelChips(names, { editing: true, wrap: false })}
      <div class="combo">
        <input class="filter-input combo-input" id="guideLabelInput" placeholder="+" autocomplete="off">
        <div class="combo-list" id="guideLabelSuggest"></div>
      </div>
    </div>`
  }

  function bindLabels(){
    overlay.querySelector('#guideLabelChips')?.addEventListener('click', (e)=>{
      const chip = e.target.closest('[data-label]')
      if (!chip) return
      draftLabels = draftLabels.filter(n => n !== chip.getAttribute('data-label'))
      renderAll()
    })
    const input = overlay.querySelector('#guideLabelInput')
    const list = overlay.querySelector('#guideLabelSuggest')
    if (!input || !list) return
    const addFromInput = () => {
      const parts = String(input.value || '').split(/[,\s]+/).map(s => s.replace(/^#+/, '').trim()).filter(Boolean)
      parts.forEach(p => {
        const code = p.toLowerCase()
        if (code && !draftLabels.includes(code) && draftLabels.length < 12) draftLabels.push(code)
      })
      input.value = ''
      list.style.display = 'none'
      renderAll()
    }
    const renderSuggest = () => {
      const q = String(input.value || '').replace(/^#+/, '').trim().toLowerCase()
      const all = window.CabrioData?.labels || []
      const items = all.filter(l => {
        const c = String(l.code || l.name || '').toLowerCase()
        if (draftLabels.includes(c)) return false
        return !q || c.includes(q)
      }).slice(0, 12)
      if (!items.length) { list.innerHTML = ''; list.style.display = 'none'; return }
      list.innerHTML = items.map(l => `<div class="combo-item" data-code="${escapeHtml(l.code || l.name)}">#${escapeHtml(l.name || l.code)}</div>`).join('')
      list.style.display = 'block'
    }
    input.addEventListener('focus', renderSuggest)
    input.addEventListener('input', renderSuggest)
    input.addEventListener('keydown', (e)=>{
      if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault()
        addFromInput()
      }
    })
    input.addEventListener('blur', ()=>{
      setTimeout(()=>{ if (String(input.value||'').trim()) addFromInput() }, 120)
    })
    list.addEventListener('mousedown', (e)=>{
      const item = e.target.closest('.combo-item')
      if (!item) return
      e.preventDefault()
      const code = item.getAttribute('data-code')
      if (code && !draftLabels.includes(code) && draftLabels.length < 12) draftLabels.push(code)
      input.value = ''
      list.style.display = 'none'
      renderAll()
    })
  }

  function renderAll(){
    cover()
    const inp = (key, extra='') => `<input data-edit-key="${key}" class="filter-input" value="${escapeHtml(data[key]||'')}" ${extra}>`
    const nameRow = overlay.querySelector('#guideNameRow')
    if (nameRow) {
      nameRow.innerHTML = isNew ? '' : sheetField(
        'Название',
        isEditing ? inp('name') : viewVal(data.name),
        'full'
      )
    }
    const authorBox = overlay.querySelector('#guideAuthor')
    if (authorBox) {
      authorBox.innerHTML = (!isNew && data.author) ? renderPersonLink(data.author) : ''
    }
    const main = overlay.querySelector('#guideMain')
    if (isNew) {
      main.innerHTML = [
        sheetField('Название', inp('name'), 'full'),
        sheetField('Ярлыки', labelsInner()),
        sheetField('Описание', `<textarea data-edit-key="description" class="filter-input" rows="1">${escapeHtml(data.description||'')}</textarea>`, 'full'),
      ].join('')
      bindLabels()
    } else {
      main.innerHTML = [
        sheetField('Ярлыки', labelsInner()),
        sheetField('Описание', isEditing
          ? `<textarea data-edit-key="description" class="filter-input" rows="1">${escapeHtml(data.description||'')}</textarea>`
          : viewVal(data.description), 'full'),
      ].join('')
      if (isEditing) bindLabels()
    }
    renderReviews()
    if (isEditing) ensureUpload()
    else overlay.querySelector('#guideUploadFab')?.remove()
  }

  function reviewWho(r){
    return [r.author?.first_name, r.author?.last_name].filter(Boolean).join(' ').trim() || 'Участник'
  }

  function reviewScore(r){
    const n = [r.quality_rating, r.speed_rating, r.price_rating].map(v => Number(v) || 0)
    return Math.round((n[0] + n[1] + n[2]) / 3 * 10) / 10
  }

  function renderReviews(){
    const box = overlay.querySelector('#guideReviews')
    if (isNew) { box.innerHTML = ''; return }
    const avg = data.rating || {}
    const list = Array.isArray(data.reviews) ? data.reviews : []
    const meId = window.__ME_ID || 0
    const mine = list.find(r => Number(r.author_user_id) === Number(meId) || Number(r.author?.id) === Number(meId))
    const canReview = !isEditing && !!meId && data.permissions?.canReview !== false
    const n = Number(avg.count || 0)
    const word = (n % 10 === 1 && n % 100 !== 11) ? 'отзыв' : ((n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20)) ? 'отзыва' : 'отзывов')
    const avgLine = avg.overall != null
      ? `Средняя ${avg.overall} из 5 · ${n} ${word}`
      : 'Пока нет оценок'
    const avgParts = avg.overall != null
      ? `<div class="review-avg-parts">качество ${avg.quality} · скорость ${avg.speed} · цена ${avg.price}</div>`
      : ''
    const writeLabel = mine ? 'Изменить мой отзыв' : 'Написать отзыв'
    box.innerHTML = `
      <div class="sheet-section-title">Оценки</div>
      <div class="review-avg">${escapeHtml(avgLine)}</div>
      ${avgParts}
      ${canReview ? `<button type="button" class="btn-ghost" id="guideReviewWrite">${escapeHtml(writeLabel)}</button>` : ''}
      <div class="sheet-section-title">Отзывы</div>
      <div class="review-list">${list.map(r => {
        const preview = String(r.feedback || '').trim()
        const short = preview.length > 70 ? preview.slice(0, 70) + '…' : preview
        return `<button type="button" class="review-row" data-review-id="${escapeHtml(r.id)}">
          <span class="review-row-main">
            <span class="review-row-name">${escapeHtml(reviewWho(r))}</span>
            <span class="review-row-text">${escapeHtml(short || 'без текста')}</span>
          </span>
          <span class="review-row-score">${escapeHtml(String(reviewScore(r)))}</span>
          <span class="review-row-go" aria-hidden="true">›</span>
        </button>`
      }).join('') || '<p class="sheet-empty">Пока нет отзывов</p>'}</div>`
    box.querySelector('#guideReviewWrite')?.addEventListener('click', () => openReviewForm(mine || {}))
    box.querySelectorAll('[data-review-id]').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = Number(btn.getAttribute('data-review-id'))
        const item = list.find(r => Number(r.id) === id)
        if (item) openReviewView(item)
      })
    })
  }

  function openStack({ title, body, foot, onSave }){
    const stack = document.createElement('div')
    stack.className = 'modal-overlay modal-above-nav modal-stack'
    stack.innerHTML = `
      <div class="modal-content modal-compact sheet-card create-sheet editing">
        <div class="modal-header create-head">
          <div class="modal-title">${escapeHtml(title)}</div>
          <div class="sheet-actions"><button type="button" class="modal-close" aria-label="close">×</button></div>
        </div>
        <div class="modal-body">${body}</div>
        ${foot ? `<div class="modal-footer create-foot">${foot}</div>` : ''}
      </div>`
    const closeStack = () => stack.remove()
    stack.addEventListener('click', (e)=>{ if (e.target === stack) closeStack() })
    stack.querySelector('.modal-close')?.addEventListener('click', closeStack)
    stack.querySelector('[data-create-cancel]')?.addEventListener('click', closeStack)
    stack.querySelector('[data-create-save]')?.addEventListener('click', () => onSave(stack, closeStack))
    document.body.appendChild(stack)
    stack.querySelectorAll('[data-star]').forEach(btn => {
      btn.addEventListener('click', () => {
        const key = btn.getAttribute('data-star-key')
        const n = btn.getAttribute('data-star')
        const hidden = stack.querySelector(`[data-edit-key="${key}"]`)
        if (hidden) hidden.value = n
        const out = stack.querySelector(`[data-sl-out="${key}"]`)
        if (out) out.textContent = n
        stack.querySelectorAll(`[data-star-key="${key}"]`).forEach(b => {
          b.classList.toggle('is-on', Number(b.getAttribute('data-star')) <= Number(n))
        })
      })
    })
    return stack
  }

  function openReviewForm(mine){
    openStack({
      title: mine.id ? 'Изменить отзыв' : 'Ваш отзыв',
      body: `
        <p class="review-form-lead">Оцените по трём шкалам от 1 до 5 и коротко напишите, что важно другим.</p>
        ${starPicker('quality_rating', 'Качество', mine.quality_rating)}
        ${starPicker('speed_rating', 'Скорость', mine.speed_rating)}
        ${starPicker('price_rating', 'Цена', mine.price_rating)}
        ${sheetField('Текст', `<textarea data-edit-key="feedback" class="filter-input" rows="3" placeholder="Что понравилось или нет">${escapeHtml(mine.feedback || '')}</textarea>`, 'full')}`,
      foot: `<button type="button" class="btn-ghost" data-create-cancel>Отмена</button>
        <button type="button" class="btn-primary" data-create-save>Сохранить</button>`,
      onSave: async (stack, closeStack) => {
        const payload = {
          guide_object_id: data.id,
          quality_rating: stack.querySelector('[data-edit-key="quality_rating"]')?.value,
          speed_rating: stack.querySelector('[data-edit-key="speed_rating"]')?.value,
          price_rating: stack.querySelector('[data-edit-key="price_rating"]')?.value,
          feedback: stack.querySelector('[data-edit-key="feedback"]')?.value,
        }
        if (!String(payload.feedback || '').trim()) {
          alert('Напишите текст отзыва')
          return
        }
        const res = await window.CabrioAPI.apiPost('/api/reviews', payload)
        if (!res || res.success === false) {
          alert((res && res.error && res.error.message) || 'Не удалось сохранить отзыв')
          return
        }
        data = res.data || data
        try {
          const fresh = await window.CabrioAPI.apiGet(`/api/guide-objects/${data.id}`)
          if (fresh && fresh.success !== false && fresh.data) data = Object.assign(data, fresh.data)
        } catch {}
        closeStack()
        paintHeader(); renderAll()
        options.onChanged?.(data)
      }
    })
  }

  function openReviewView(r){
    const rows = [
      sheetField('Кто', viewVal(reviewWho(r))),
      sheetField('Качество', viewVal(r.quality_rating)),
      sheetField('Скорость', viewVal(r.speed_rating)),
      sheetField('Цена', viewVal(r.price_rating)),
      sheetField('Текст', viewVal(r.feedback), 'full'),
    ].join('')
    openStack({
      title: 'Отзыв',
      body: `<div class="sheet-grid">${rows}</div>
        <div class="review-avg">Среднее по этому отзыву: ${escapeHtml(String(reviewScore(r)))} из 5</div>`,
      foot: `<button type="button" class="btn-ghost" data-create-cancel>Закрыть</button>`,
      onSave: (_s, closeStack) => closeStack()
    })
  }

  function val(key){
    const el = overlay.querySelector(`[data-edit-key="${key}"]`)
    return el ? el.value : ''
  }

  async function loadLabelHints(){
    window.CabrioData = window.CabrioData || {}
    try {
      if (!(Array.isArray(window.CabrioData.labels) && window.CabrioData.labels.length)) {
        const res = await window.CabrioAPI.apiGet('/api/ref/labels')
        if (res && res.success && Array.isArray(res.data)) window.CabrioData.labels = res.data
      }
    } catch {}
  }

  async function savePlace(){
    const payload = {
      name: val('name') || data.name,
      description: val('description'),
      labels: draftLabels,
    }
    if (!String(payload.name||'').trim()) {
      alert('Напишите название')
      return
    }
    let res
    if (isNew) {
      res = await window.CabrioAPI.apiPost('/api/guide-objects', payload)
    } else {
      res = await window.CabrioAPI.apiPatch(`/api/guide-objects/${data.id}`, payload)
    }
    if (!res || res.success === false) {
      alert((res && res.error && res.error.message) || 'Не удалось сохранить')
      return
    }
    data = res.data
    draftLabels = (Array.isArray(data.labels) ? data.labels : []).map(labelCode).filter(Boolean)
    isNew = false
    isEditing = false
    if (pendingFile) {
      await sendGuidePhoto(pendingFile)
      pendingFile = null
      data._preview = null
    }
    paintHeader(); renderAll()
    options.onChanged?.(data)
  }

  async function deletePlace(){
    if (!confirm('Место будет помечено как удалённое. Продолжить?')) return
    const res = await window.CabrioAPI.apiDelete(`/api/guide-objects/${data.id}`)
    if (!res || res.success === false) {
      alert((res && res.error && res.error.message) || 'Не удалось удалить')
      return
    }
    options.onChanged?.(null)
    close()
  }

  async function sendGuidePhoto(file){
    const base = (window.__API_URL || (window.location.origin + '/app/backend')).replace(/\/$/, '')
    const fd = new FormData()
    fd.append('entity_type', 'guide_object')
    fd.append('entity_id', String(data.id))
    fd.append('photo', file)
    Object.entries(readTelegramUser()).forEach(([k,v])=>{ if (v!==undefined) fd.append(k, v) })
    const resp = await fetch(`${base}/routes/api.php?route=${encodeURIComponent('/api/photos')}`, { method:'POST', body: fd }).then(r=>r.json().catch(()=>null))
    if (!resp || resp.success === false) {
      alert((resp && resp.error && resp.error.message) || 'Не удалось загрузить фото')
      return
    }
    data.photo = resp.data
    try {
      const fresh = await window.CabrioAPI.apiGet(`/api/guide-objects/${data.id}`)
      if (fresh && fresh.success !== false && fresh.data) data = Object.assign(data, fresh.data)
    } catch {}
  }

  function ensureUpload(){
    const photoContainer = overlay.querySelector('.main-photo-compact')
    if (!photoContainer || photoContainer.querySelector('#guideUploadFab')) return
    const localInput = document.createElement('input')
    localInput.type = 'file'
    localInput.accept = 'image/*'
    localInput.style.display = 'none'
    photoContainer.appendChild(localInput)
    localInput.addEventListener('change', async ()=>{
      const file = localInput.files && localInput.files[0]
      if (!file) return
      if (!data.id) {
        pendingFile = file
        data._preview = URL.createObjectURL(file)
        cover()
        localInput.value = ''
        return
      }
      try {
        overlay.querySelector('#guideUploadOverlay').style.display = 'flex'
        await sendGuidePhoto(file)
        cover()
      } catch { alert('Ошибка загрузки') }
      finally { overlay.querySelector('#guideUploadOverlay').style.display='none'; localInput.value='' }
    })
    const fabBtn = document.createElement('button')
    fabBtn.id = 'guideUploadFab'
    fabBtn.className = 'photo-upload-fab photo-upload-center'
    fabBtn.type = 'button'
    fabBtn.innerHTML = '<span>📷</span><span>Фото</span>'
    photoContainer.appendChild(fabBtn)
    fabBtn.addEventListener('click', (e)=>{ e.stopPropagation(); localInput.click() })
  }

  overlay.querySelector('.main-photo-compact')?.addEventListener('click', (e)=>{
    if (e.target.closest('#guideUploadFab')) return
    const url = photoUrl(data, 'medium')
    if (url) openPhotoViewer([url], 0)
  })

  window.CabrioAPI.getMe().then(me=>{
    window.__ME_ID = me?.data?.id || me?.id || 0
  }).finally(()=> loadLabelHints().then(()=>{ paintHeader(); renderAll() }))
}

window.CabrioModals = window.CabrioModals || {}
window.CabrioModals.openGuideModal = openGuideModal
