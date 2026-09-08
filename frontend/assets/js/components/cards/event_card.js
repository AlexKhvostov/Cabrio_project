// Плитка события в сетке — как авто: фото, название на снимке, свой ответ внизу

import { phEvent } from '../media.js?v=cabrio21'

function escapeHtml(str){
  return String(str||'').replace(/[&<>"']/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

function formatDate(dateStr){
  if (!dateStr) return ''
  const d = new Date(String(dateStr).includes('T') ? dateStr : dateStr + 'T12:00:00')
  if (isNaN(d.getTime())) return String(dateStr)
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}

const RSVP_LABEL = { yes: 'Вы едете', maybe: 'Возможно', no: 'Не едете' }

export function renderEventCard(event){
  const title = event.title || 'Событие'
  const city = event.city || event.location || ''
  const dateText = [formatDate(event.event_date), event.event_time ? String(event.event_time).slice(0, 5) : ''].filter(Boolean).join(' · ')
  const typeName = event.event_type?.name || ''
  const mine = event.my_rsvp?.confidence || ''
  const rsvpText = RSVP_LABEL[mine] || ''
  const going = Number(event.going_count || event.participants_count || 0)
  const maybe = Number(event.maybe_count || 0)
  const spots = event.spots_left

  return `
  <div class="car-card-compact event-card-compact" data-id="${event.id}">
    <div class="car-image-container">
      ${typeName ? `<div class="car-status-badge">${escapeHtml(typeName)}</div>` : ''}
      ${phEvent(event, 'medium')}
      <div class="car-overlay-info">
        <div class="car-title-overlay">${escapeHtml(title)}</div>
        <div class="car-specs-overlay">${escapeHtml([dateText, city].filter(Boolean).join(' · '))}</div>
      </div>
    </div>
    <div class="car-owner-compact event-card-foot">
      <div class="event-fill">
        <span class="event-fill-nums"><b>${going}</b><i>·</i><b>${escapeHtml(spots == null ? '∞' : String(spots))}</b><i>·</i><b>${maybe}</b></span>
      </div>
      ${rsvpText ? `<span class="owner-name-compact">${escapeHtml(rsvpText)}</span>` : ''}
    </div>
  </div>`
}
