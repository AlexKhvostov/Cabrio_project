// Плитка отзыва в сетке: 5 звёзд и цвет рамки от средней оценки

import { phPlace } from '../media.js?v=cabrio21'

function escapeHtml(str){
  return String(str||'').replace(/[&<>"']/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

// Среднее в БД уже из 5. На плитке — ближайшая половина звезды.
export function overallToStars(overall){
  if (overall == null || overall === '') return null
  const n = Number(overall)
  if (!isFinite(n)) return null
  return Math.max(0, Math.min(5, Math.round(n * 2) / 2))
}

// До 2.5 ★ — тепло-красная рамка, до 4 — жёлтая, 4–5 — зелёная
export function starsTone(stars){
  if (stars == null) return ''
  if (stars < 2.5) return 'bad'
  if (stars < 4) return 'mid'
  return 'good'
}

function ruReviews(n){
  const abs = Math.abs(n)
  const n10 = abs % 10
  const n100 = abs % 100
  // после «из» — родительный падеж: из 1 отзыва, из 12 отзывов
  if (n10 === 1 && n100 !== 11) return abs + ' отзыва'
  return abs + ' отзывов'
}

function starRow(stars){
  const filled = stars == null ? 0 : stars
  const cells = [1, 2, 3, 4, 5].map(i => {
    let cls = 'is-empty'
    if (filled >= i) cls = 'is-full'
    else if (filled >= i - 0.5) cls = 'is-half'
    return `<span class="guide-star ${cls}" aria-hidden="true">★</span>`
  }).join('')
  return `<div class="guide-stars" aria-hidden="true">${cells}</div>`
}

export function renderGuideCard(place){
  const title = place.name || 'Место'
  const labels = Array.isArray(place.labels) ? place.labels : []
  const tags = labels.slice(0, 3).map(l => '#' + (l.name || l.code || '')).filter(t => t.length > 1).join(' ')
  const overall = place.rating?.overall
  const stars = overallToStars(overall)
  const tone = starsTone(stars)
  const count = Number(place.rating?.count || 0)
  const hasScore = stars != null
  const scoreText = hasScore ? Number(overall).toFixed(1) : ''
  const countChip = hasScore && count > 0
    ? `<span class="guide-count" title="${escapeHtml('из ' + ruReviews(count))}"><svg class="guide-count-ico" viewBox="0 0 16 16" aria-hidden="true"><path fill="currentColor" d="M3.2 2.5h9.6c.7 0 1.2.5 1.2 1.2v6c0 .7-.5 1.2-1.2 1.2H7.1L4 13.7V10.9H3.2c-.7 0-1.2-.5-1.2-1.2v-6c0-.7.5-1.2 1.2-1.2z"/></svg>${escapeHtml(String(count))}</span>`
    : ''
  const toneClass = tone ? (' rate-' + tone) : ''
  const aria = hasScore
    ? (scoreText + ' из 5' + (count > 0 ? ', из ' + ruReviews(count) : ''))
    : 'пока нет отзывов'

  const foot = hasScore
    ? `<div class="guide-card-foot">
        <div class="guide-rate">
          <b class="guide-score">${escapeHtml(scoreText)}</b>
          ${starRow(stars)}
        </div>
        ${countChip}
      </div>`
    : `<div class="guide-card-foot is-empty"><span class="guide-score-n">нет отзывов</span></div>`

  return `
  <div class="car-card-compact event-card-compact guide-card-compact${toneClass}" data-id="${place.id}" aria-label="${escapeHtml(aria)}">
    <div class="car-image-container">
      ${phPlace(place, 'medium')}
      <div class="car-overlay-info">
        <div class="car-title-overlay">${escapeHtml(title)}</div>
        <div class="car-specs-overlay">${escapeHtml(tags)}</div>
      </div>
    </div>
    ${foot}
  </div>`
}
