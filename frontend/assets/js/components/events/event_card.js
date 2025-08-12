// event_card.js — компактная карточка события

function escapeHtml(str){
  return String(str||'').replace(/[&<>"']/g, s=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;' }[s]))
}

function formatDate(dateStr){
  if(!dateStr) return ''
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return String(dateStr)
  return d.toLocaleDateString('ru-RU', { day:'2-digit', month:'short' })
}

export function renderEventCard(event){
  const title = event.title || 'Событие'
  const city = event.city || ''
  const type = event.type || ''
  const status = event.status || ''
  const dateText = formatDate(event.event_date || event.date || event.start_date)
  const photoUrl = (event.photo && event.photo.url) ? event.photo.url : ''
  return `
  <div class="event-card-compact card" data-id="${event.id}">
    <div class="event-card-head">
      <div class="event-date">${escapeHtml(dateText)}</div>
      ${status ? `<div class="event-status">${escapeHtml(status)}</div>`:''}
    </div>
    <div class="event-card-body">
      <div class="event-image-box">${photoUrl?`<img src="${escapeHtml(photoUrl)}" alt="${escapeHtml(title)}"/>`:`<div class="event-image-ph">📅</div>`}</div>
      <div class="event-info">
        <div class="event-title">${escapeHtml(title)}</div>
        <div class="event-meta">${type?`${escapeHtml(type)} • `:''}${escapeHtml(city)}</div>
      </div>
    </div>
  </div>`
}




