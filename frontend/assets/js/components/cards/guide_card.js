// Плитка места в гиде — та же сетка, что у авто

import { phPlace } from '../media.js?v=cabrio21'

function escapeHtml(str){
  return String(str||'').replace(/[&<>"']/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

export function renderGuideCard(place){
  const title = place.name || 'Место'
  const labels = Array.isArray(place.labels) ? place.labels : []
  const tags = labels.slice(0, 3).map(l => '#' + (l.name || l.code || '')).filter(t => t.length > 1).join(' ')
  const overall = place.rating?.overall
  const ratingText = overall != null ? `${overall} / 10` : ''

  return `
  <div class="car-card-compact event-card-compact" data-id="${place.id}">
    <div class="car-image-container">
      ${phPlace(place, 'medium')}
      <div class="car-overlay-info">
        <div class="car-title-overlay">${escapeHtml(title)}</div>
        <div class="car-specs-overlay">${escapeHtml([tags, ratingText].filter(Boolean).join(' · '))}</div>
      </div>
    </div>
  </div>`
}
