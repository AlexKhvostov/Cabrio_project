// Большая карточка события (kit п. 58): когда и где, описание, еду / нет / возможно

import { phEvent, photoUrl } from '../components/media.js?v=cabrio21'
import { renderEventOccupancy } from '../components/cards/event_card.js?v=list3'
import { hintBubble } from '../components/hints.js?v=tip2'
import {
  escapeHtml, viewVal, sheetField, renderPersonLink, bindRelLinks, headerActions, openPhotoViewer
} from '../components/sheet.js?v=tags2'

const INVITE_HINT = 'Галка снята — открытая встреча: любой участник клуба видит её в списке и может ответить «еду».\n\nГалка стоит — встреча по приглашению: так помечаем закрытый формат. Список приглашённых подключим следующим шагом, отметка уже сохраняется в карточке.'

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

function formatFull(dateStr){
  if (!dateStr) return ''
  const d = new Date(String(dateStr).includes('T') ? dateStr : dateStr + 'T12:00:00')
  if (isNaN(d.getTime())) return String(dateStr)
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

function timeShort(t){
  return t ? String(t).slice(0, 5) : ''
}

function eventStarted(event){
  if (!event?.event_date) return false
  const t = String(event.event_time || '00:00:00')
  const d = new Date(`${event.event_date}T${t.length === 5 ? t + ':00' : t}`)
  return !isNaN(d.getTime()) && d.getTime() < Date.now()
}

export function openEventModal(event, options = {}){
  const overlay = document.createElement('div')
  let data = Object.assign({}, event || {})
  let isNew = !data.id
  overlay.className = 'modal-overlay modal-above-nav' + (isNew ? ' modal-create' : '')
  let isEditing = isNew || !!options.startEdit
  const canEdit = isNew || !!(data.permissions && data.permissions.canEdit)
  let pendingFile = null

  const modalTitle = () => {
    if (isNew) return 'Создание события'
    return 'Событие'
  }

  const paintHeader = () => {
    const actions = overlay.querySelector('#eventHeaderActions')
    const titleEl = overlay.querySelector('#eventModalTitle')
    const foot = overlay.querySelector('#eventCreateFoot')
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
    if (canEdit && !isEditing) {
      extra = `<button type="button" class="btn-ghost" data-sheet-delete>Удалить</button>`
    }
    actions.innerHTML = extra + headerActions({ canEdit, editing: isEditing, withClose: true })
    actions.querySelector('.modal-close')?.addEventListener('click', close)
    actions.querySelector('[data-sheet-edit]')?.addEventListener('click', () => enterEdit())
    actions.querySelector('[data-sheet-cancel]')?.addEventListener('click', () => {
      isEditing = false
      paintHeader(); renderAll()
    })
    actions.querySelector('[data-sheet-save]')?.addEventListener('click', () => saveEdit())
    actions.querySelector('[data-sheet-delete]')?.addEventListener('click', () => deleteEvent())
  }

  overlay.innerHTML = `
    <div class="modal-content modal-compact sheet-card${isNew ? ' create-sheet' : ''}${isEditing ? ' editing' : ''}">
      <div class="modal-header${isNew ? ' create-head' : ''}">
        <div class="modal-title" id="eventModalTitle">${modalTitle()}</div>
        <div id="eventHeaderActions" class="sheet-actions"></div>
      </div>
      <div class="modal-body">
        <div class="main-photo-compact">
          <span id="eventPhotoInner"></span>
          <div class="sheet-photo-caption">
            <div class="sheet-photo-title" id="eventCoverTitle"></div>
            <div class="sheet-photo-meta" id="eventCoverMeta"></div>
          </div>
          <div class="photo-upload-overlay" id="eventUploadOverlay" style="display:none"><div class="spinner"></div><span>Загрузка…</span></div>
        </div>
        <div id="eventNameRow"></div>
        <div id="eventOrg"></div>
        <div class="sheet-section-title">Когда и где</div>
        <div class="sheet-grid" id="eventWhen"></div>
        <div class="sheet-section-title">О событии</div>
        <div class="sheet-grid" id="eventAbout"></div>
        <div id="eventRsvpWrap"></div>
      </div>
      <div class="modal-footer create-foot" id="eventCreateFoot" ${isNew ? '' : 'hidden'}>
        <button type="button" class="btn-ghost" data-create-cancel>Отмена</button>
        <button type="button" class="btn-primary" data-create-save>Сохранить</button>
      </div>
    </div>`

  function close(){ overlay.remove() }
  overlay.addEventListener('click', (e)=>{ if (e.target === overlay) close() })
  overlay.querySelector('[data-create-cancel]')?.addEventListener('click', close)
  overlay.querySelector('[data-create-save]')?.addEventListener('click', () => saveEdit())
  document.body.appendChild(overlay)
  paintHeader()
  renderAll()

  function cover(){
    const inner = overlay.querySelector('#eventPhotoInner')
    if (data._preview) {
      inner.innerHTML = `<div class="ph ph-event"><img class="ph-img ph-ok" src="${escapeHtml(data._preview)}" alt=""></div>`
    } else {
      inner.innerHTML = phEvent(data, 'medium', true)
    }
    overlay.querySelector('#eventCoverTitle').textContent = ''
    overlay.querySelector('#eventCoverMeta').textContent = ''
    overlay.querySelector('.sheet-photo-caption')?.classList.add('is-off')
    const nameRow = overlay.querySelector('#eventNameRow')
    if (nameRow) {
      nameRow.innerHTML = isNew ? '' : sheetField(
        'Название',
        isEditing
          ? `<input data-edit-key="title" class="filter-input" value="${escapeHtml(data.title||'')}">`
          : viewVal(data.title),
        'full'
      )
    }
  }

  function renderAll(){
    cover()
    const org = overlay.querySelector('#eventOrg')
    org.innerHTML = data.organizer ? renderPersonLink(data.organizer) : ''
    bindRelLinks(overlay)
    const types = window.CabrioData?.eventTypes || []
    const typeInner = isEditing
      ? `<select data-edit-key="event_type_id" class="filter-select"><option value="">—</option>${types.map(t=>`<option value="${t.id}" ${Number(t.id)===Number(data.event_type_id||data.event_type?.id||0)?'selected':''}>${escapeHtml(t.name)}</option>`).join('')}</select>`
      : viewVal(data.event_type?.name)
    const when = overlay.querySelector('#eventWhen')
    const about = overlay.querySelector('#eventAbout')
    if (isNew) {
      when.innerHTML = [
        sheetField('Дата', `<input data-edit-key="event_date" class="filter-input" type="date" value="${escapeHtml(data.event_date||'')}">`),
        sheetField('Время', `<input data-edit-key="event_time" class="filter-input" type="time" value="${escapeHtml(timeShort(data.event_time)||'12:00')}">`),
        sheetField('Город', `<input data-edit-key="city" class="filter-input" value="${escapeHtml(data.city||'')}">`),
        sheetField('Место', `<input data-edit-key="location" class="filter-input" value="${escapeHtml(data.location||'')}">`, 'full'),
      ].join('')
      about.innerHTML = [
        sheetField('Название', `<input data-edit-key="title" class="filter-input" value="${escapeHtml(data.title||'')}">`, 'full'),
        sheetField('Тип', typeInner),
        sheetField('Лимит', `<input data-edit-key="max_participants" class="filter-input" type="number" min="1" value="${escapeHtml(data.max_participants??'')}">`),
        sheetField('Описание', `<textarea data-edit-key="description" class="filter-input" rows="1">${escapeHtml(data.description||'')}</textarea>`, 'full'),
        sheetField('Приватное', `<div class="sheet-check-row"><label class="sheet-check"><input type="checkbox" data-edit-key="is_private" ${data.registration_type==='invitation'?'checked':''}/> Только по приглашению</label>${hintBubble(INVITE_HINT, { label: 'Что значит эта галка' })}</div>`, 'full'),
      ].join('')
    } else {
      // Просмотр и правка — одна сетка. Меняется только светлый фон у полей.
      when.innerHTML = [
        sheetField('Дата', isEditing ? `<input data-edit-key="event_date" class="filter-input" type="date" value="${escapeHtml(data.event_date||'')}">` : viewVal(formatFull(data.event_date))),
        sheetField('Время', isEditing ? `<input data-edit-key="event_time" class="filter-input" type="time" value="${escapeHtml(timeShort(data.event_time)||'')}">` : viewVal(timeShort(data.event_time))),
        sheetField('Город', isEditing ? `<input data-edit-key="city" class="filter-input" value="${escapeHtml(data.city||'')}">` : viewVal(data.city)),
        sheetField('Место', isEditing ? `<input data-edit-key="location" class="filter-input" value="${escapeHtml(data.location||'')}">` : viewVal(data.location), 'full'),
      ].join('')
      about.innerHTML = [
        sheetField('Тип', typeInner),
        sheetField('Статус', viewVal(data.status?.name || data.status?.code)),
        sheetField('Описание', isEditing ? `<textarea data-edit-key="description" class="filter-input" rows="1">${escapeHtml(data.description||'')}</textarea>` : viewVal(data.description), 'full'),
      ].join('')
    }
    renderRsvp()
    if (isEditing) ensureUpload()
    else overlay.querySelector('#eventUploadFab')?.remove()
  }

  function rsvpPersonLine(p){
    const name = [p.first_name_app, p.last_name_app].filter(Boolean).join(' ').trim()
      || (p.username ? '@' + p.username : 'Участник')
    const extra = p.plus_one ? ' · +1 гость' : ''
    return `<div class="rsvp-person">${escapeHtml(name)}<em>${escapeHtml(extra)}</em></div>`
  }

  function renderRsvp(){
    const wrap = overlay.querySelector('#eventRsvpWrap')
    if (isNew) { wrap.innerHTML = ''; return }
    const mine = data.my_rsvp?.confidence || ''
    const plus = !!data.my_rsvp?.plus_one
    const locked = eventStarted(data)
    const goingList = Array.isArray(data.rsvp_going) ? data.rsvp_going : []
    const maybeList = Array.isArray(data.rsvp_maybe) ? data.rsvp_maybe : []
    const rsvpOff = locked || isEditing
    wrap.innerHTML = `
      <div class="sheet-section-title">Участие</div>
      ${renderEventOccupancy(data)}
      ${goingList.length ? `<div class="sheet-label">Кто едет</div><div class="rsvp-people">${goingList.map(rsvpPersonLine).join('')}</div>` : ''}
      ${maybeList.length ? `<div class="sheet-label">Думают</div><div class="rsvp-people">${maybeList.map(rsvpPersonLine).join('')}</div>` : ''}
      <div class="sheet-section-title">Будете участвовать?</div>
      <div class="rsvp-row">
        <button type="button" class="rsvp-btn ${mine==='yes'?'is-on':''}" data-rsvp="yes" ${rsvpOff?'disabled':''}>Да</button>
        <button type="button" class="rsvp-btn ${mine==='maybe'?'is-on':''}" data-rsvp="maybe" ${rsvpOff?'disabled':''}>Возможно</button>
        <button type="button" class="rsvp-btn ${mine==='no'?'is-on':''}" data-rsvp="no" ${rsvpOff?'disabled':''}>Нет</button>
      </div>
      <label class="sheet-check rsvp-plus" ${mine==='yes'?'':'hidden'}>
        <input type="checkbox" id="eventPlusOne" ${plus?'checked':''} ${rsvpOff?'disabled':''}/> +1 гость
      </label>
      ${locked ? `<p class="kit-note" style="margin:6px 0 0">Событие уже началось, ответ менять нельзя.</p>` : ''}`
    wrap.querySelectorAll('[data-rsvp]').forEach(btn=>{
      btn.addEventListener('click', async ()=>{
        if (isEditing) return
        const confidence = btn.getAttribute('data-rsvp')
        const plusOne = confidence === 'yes' && !!overlay.querySelector('#eventPlusOne')?.checked
        const res = await window.CabrioAPI.apiPost(`/api/events/${data.id}/rsvp`, { confidence, plus_one: plusOne })
        if (!res || res.success === false) {
          alert((res && res.error && res.error.message) || 'Не удалось сохранить ответ')
          return
        }
        data = res.data
        paintHeader(); renderAll()
        options.onChanged?.(data)
      })
    })
    wrap.querySelector('#eventPlusOne')?.addEventListener('change', async (e)=>{
      if ((data.my_rsvp?.confidence || '') !== 'yes') return
      const res = await window.CabrioAPI.apiPost(`/api/events/${data.id}/rsvp`, { confidence: 'yes', plus_one: e.target.checked })
      if (res && res.success !== false) { data = res.data; options.onChanged?.(data) }
    })
  }

  function val(key){
    const el = overlay.querySelector(`[data-edit-key="${key}"]`)
    if (!el) return data[key]
    if (el.type === 'checkbox') return el.checked
    return el.value
  }

  async function enterEdit(){
    isEditing = true
    await loadTypes()
    paintHeader(); renderAll()
  }

  async function loadTypes(){
    try {
      if (!(Array.isArray(window.CabrioData?.eventTypes) && window.CabrioData.eventTypes.length)) {
        const res = await window.CabrioAPI.apiGet('/api/ref/event-types')
        window.CabrioData = window.CabrioData || {}
        if (res && res.success && Array.isArray(res.data)) window.CabrioData.eventTypes = res.data
      }
    } catch {}
  }

  async function saveEdit(){
    const payload = {
      title: val('title'),
      event_date: val('event_date'),
      event_time: val('event_time'),
      city: val('city'),
      location: val('location'),
      description: val('description'),
      event_type_id: val('event_type_id'),
      max_participants: overlay.querySelector('[data-edit-key="max_participants"]') ? val('max_participants') : data.max_participants,
      is_private: overlay.querySelector('[data-edit-key="is_private"]') ? val('is_private') : (data.registration_type === 'invitation'),
    }
    let res
    if (isNew) {
      res = await window.CabrioAPI.apiPost('/api/events', payload)
    } else {
      res = await window.CabrioAPI.apiPatch(`/api/events/${data.id}`, payload)
    }
    if (!res || res.success === false) {
      alert((res && res.error && res.error.message) || 'Не удалось сохранить')
      return
    }
    data = res.data
    isNew = false
    isEditing = false
    if (pendingFile) {
      await sendEventPhoto(pendingFile)
      pendingFile = null
      data._preview = null
    }
    paintHeader(); renderAll()
    options.onChanged?.(data)
  }

  async function deleteEvent(){
    if (!confirm('Событие будет помечено как удалённое. Продолжить?')) return
    const res = await window.CabrioAPI.apiDelete(`/api/events/${data.id}`)
    if (!res || res.success === false) {
      alert((res && res.error && res.error.message) || 'Не удалось удалить')
      return
    }
    options.onChanged?.(null)
    close()
  }

  async function sendEventPhoto(file){
    const base = (window.__API_URL || (window.location.origin + '/app/backend')).replace(/\/$/, '')
    const fd = new FormData()
    fd.append('entity_type', 'event')
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
      const fresh = await window.CabrioAPI.apiGet(`/api/events/${data.id}`)
      if (fresh && fresh.success !== false && fresh.data) data = Object.assign(data, fresh.data)
    } catch {}
  }

  function ensureUpload(){
    const photoContainer = overlay.querySelector('.main-photo-compact')
    if (!photoContainer || photoContainer.querySelector('#eventUploadFab')) return
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
        overlay.querySelector('#eventUploadOverlay').style.display = 'flex'
        await sendEventPhoto(file)
        cover()
      } catch { alert('Ошибка загрузки') }
      finally { overlay.querySelector('#eventUploadOverlay').style.display='none'; localInput.value='' }
    })
    const fabBtn = document.createElement('button')
    fabBtn.id = 'eventUploadFab'
    fabBtn.className = 'photo-upload-fab photo-upload-center'
    fabBtn.type = 'button'
    fabBtn.innerHTML = '<span>📷</span><span>Фото</span>'
    photoContainer.appendChild(fabBtn)
    fabBtn.addEventListener('click', (e)=>{ e.stopPropagation(); localInput.click() })
  }

  overlay.querySelector('.main-photo-compact')?.addEventListener('click', (e)=>{
    if (e.target.closest('#eventUploadFab')) return
    const url = photoUrl(data, 'medium')
    if (url) openPhotoViewer([url], 0)
  })

  loadTypes().then(()=>{ paintHeader(); renderAll() })
}

window.CabrioModals = window.CabrioModals || {}
window.CabrioModals.openEventModal = openEventModal
