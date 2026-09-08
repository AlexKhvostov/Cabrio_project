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

function field(label, value, full = false){
  const text = value === null || value === undefined ? '' : String(value).trim()
  return `<div class=\"sheet-field${full ? ' full' : ''}\">
    <span class=\"sheet-label\">${escapeHtml(label)}</span>
    ${text ? `<span class=\"sheet-value\">${escapeHtml(text)}</span>` : `<span class=\"sheet-empty\">не указано</span>`}
  </div>`
}

export function openEventModal(event){
  const overlay = document.createElement('div')
  overlay.className = 'modal-overlay'
  const title = event.title || 'Событие'
  const photoUrl = (event.photo && event.photo.url) ? event.photo.url : ''
  const status = event.status?.name || event.status?.code || event.status || ''
  const type = event.type?.name || event.type?.code || event.type || ''
  overlay.innerHTML = `
    <div class=\"modal-content modal-compact sheet-card\">
      <div class=\"modal-header\">
        <div class=\"modal-title\">Событие</div>
        <button class=\"modal-close\" aria-label=\"close\">×</button>
      </div>
      <div class=\"modal-body\">
        ${photoUrl?`<div class=\"main-photo-compact\">
          <img src=\"${escapeHtml(photoUrl)}\" class=\"main-image\" alt=\"${escapeHtml(title)}\"/>
          <div class=\"sheet-photo-caption\"><div class=\"sheet-photo-title\">${escapeHtml(title)}</div><div class=\"sheet-photo-meta\">${escapeHtml(event.city||'')}</div></div>
        </div>`:`<div class=\"sheet-hero-name\">${escapeHtml(title)}</div>`}
        <div class=\"sheet-section-title\">Когда и где</div>
        <div class=\"sheet-grid\">
          ${field('Дата', formatFull(event.event_date||event.date||''))}
          ${field('Время', event.event_time || '')}
          ${field('Город', event.city || '')}
        </div>
        <div class=\"sheet-section-title\">О событии</div>
        <div class=\"sheet-grid\">
          ${field('Тип', type)}
          ${field('Статус', status)}
          ${field('Описание', event.description || '', true)}
        </div>
      </div>
    </div>`
  function close(){ overlay.remove() }
  overlay.addEventListener('click', (e)=>{ if(e.target===overlay) close() })
  overlay.querySelector('.modal-close')?.addEventListener('click', close)
  document.body.appendChild(overlay)
}

window.CabrioModals = window.CabrioModals || {}
window.CabrioModals.openEventModal = openEventModal




