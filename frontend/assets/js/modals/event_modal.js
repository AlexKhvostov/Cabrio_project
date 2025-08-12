// event_modal.js — модальное окно события

function escapeHtml(str){
  return String(str||'').replace(/[&<>"']/g, s=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;' }[s]))
}

function formatFull(dateStr){
  if(!dateStr) return ''
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return String(dateStr)
  return d.toLocaleDateString('ru-RU', { day:'numeric', month:'long', year:'numeric' })
}

export function openEventModal(event){
  const overlay = document.createElement('div')
  overlay.className = 'modal-overlay'
  const title = event.title || 'Событие'
  const photoUrl = (event.photo && event.photo.url) ? event.photo.url : ''
  const status = event.status || ''
  const type = event.type || ''
  overlay.innerHTML = `
    <div class=\"modal-content\">
      <div class=\"modal-header\">
        <div class=\"modal-title\">${escapeHtml(title)}</div>
        <button class=\"modal-close\" aria-label=\"close\">×</button>
      </div>
      <div class=\"modal-body\">
        ${photoUrl?`<div class=\"main-photo-compact\"><img src=\"${escapeHtml(photoUrl)}\" class=\"main-image\" alt=\"${escapeHtml(title)}\"/></div>`:''}
        <div class=\"detail-grid-compact\">
          <div class=\"detail-item-compact\"><span class=\"detail-label\">Дата</span><span class=\"detail-value\">${escapeHtml(formatFull(event.event_date||event.date||''))} ${event.event_time?(' в '+escapeHtml(event.event_time)) : ''}</span></div>
          <div class=\"detail-item-compact\"><span class=\"detail-label\">Место</span><span class=\"detail-value\">${escapeHtml(event.city||'')}</span></div>
          ${type?`<div class=\"detail-item-compact\"><span class=\"detail-label\">Тип</span><span class=\"detail-value\">${escapeHtml(type)}</span></div>`:''}
          ${status?`<div class=\"detail-item-compact\"><span class=\"detail-label\">Статус</span><span class=\"detail-value\">${escapeHtml(status)}</span></div>`:''}
        </div>
        ${event.description?`<div class=\"detail-section-compact\"><h4>Описание</h4><div>${escapeHtml(event.description)}</div></div>`:''}
      </div>
    </div>`
  function close(){ overlay.remove() }
  overlay.addEventListener('click', (e)=>{ if(e.target===overlay) close() })
  overlay.querySelector('.modal-close')?.addEventListener('click', close)
  document.body.appendChild(overlay)
}

window.CabrioModals = window.CabrioModals || {}
window.CabrioModals.openEventModal = openEventModal




