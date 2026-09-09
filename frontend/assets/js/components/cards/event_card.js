// Плитка события в сетке: фото, «через сколько дней», едут / мест / думают

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

// Склонение: 1 день, 2 дня, 5 дней
function ruDaysWord(n){
  const abs = Math.abs(n)
  const n10 = abs % 10
  const n100 = abs % 100
  if (n100 >= 11 && n100 <= 14) return 'дней'
  if (n10 === 1) return 'день'
  if (n10 >= 2 && n10 <= 4) return 'дня'
  return 'дней'
}

// Сколько календарных дней до даты события (местная полночь)
function daysUntil(dateStr){
  if (!dateStr) return null
  const raw = String(dateStr)
  const d = new Date(raw.includes('T') ? raw : raw + 'T12:00:00')
  if (isNaN(d.getTime())) return null
  const start = new Date(d.getFullYear(), d.getMonth(), d.getDate())
  const today = new Date()
  const todayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate())
  return Math.round((start - todayStart) / 86400000)
}

export function whenBadge(dateStr){
  const n = daysUntil(dateStr)
  if (n == null) return { text: '', cls: '' }
  if (n === 0) return { text: 'сегодня', cls: 'is-soon' }
  if (n === 1) return { text: 'завтра', cls: 'is-soon' }
  if (n > 1) return { text: 'через ' + n + ' ' + ruDaysWord(n), cls: '' }
  if (n === -1) return { text: 'вчера', cls: 'is-past' }
  return { text: 'было ' + Math.abs(n) + ' ' + ruDaysWord(n) + ' назад', cls: 'is-past' }
}

function statCell(numHtml, label, isMine, plusOne){
  const mark = isMine
    ? `<span class="event-stat-mark">${plusOne ? '✓+1' : '✓'}</span>`
    : ''
  return `<div class="event-stat-cell${isMine ? ' is-mine' : ''}">${mark}<b>${numHtml}</b><em>${escapeHtml(label)}</em></div>`
}

// Блок едут / думают + места справа — и в плитке списка, и в карточке события
export function renderEventOccupancy(event){
  const mine = event.my_rsvp?.confidence || ''
  const plusOne = mine === 'yes' && !!event.my_rsvp?.plus_one
  const going = Number(event.going_count || event.participants_count || 0)
  const maybe = Number(event.maybe_count || 0)
  const spots = event.spots_left
  const spotsText = (spots == null) ? '∞' : String(spots)
  return `<div class="event-stat-mini">
    <div class="event-stat-people">
      ${statCell(String(going), 'едут', mine === 'yes', plusOne)}
      ${statCell(String(maybe), 'думают', mine === 'maybe', false)}
    </div>
    <div class="event-stat-spots${spots === 0 ? ' is-full' : ''}">
      <b>${escapeHtml(spotsText)}</b>
      <em>мест</em>
    </div>
  </div>`
}

/** Табло регистрации в карточке события (кит п. 58): едут / думают / нет, места в полоске */
export function renderEventRegDash(event){
  const going = Number(event.going_count || event.participants_count || 0)
  const maybe = Number(event.maybe_count || 0)
  const no = Number(event.no_count || 0)
  const spots = event.spots_left
  const hasLimit = spots != null && spots !== ''
  const spotsNum = hasLimit ? Number(spots) : null
  const limit = hasLimit ? going + spotsNum : null
  const pct = limit > 0 ? Math.min(100, Math.round((going / limit) * 100)) : 0
  const mine = event.my_rsvp?.confidence || ''
  const plus = mine === 'yes' && !!event.my_rsvp?.plus_one
  const you = mine === 'yes'
    ? (plus ? 'вы едете +1' : 'вы едете')
    : (mine === 'maybe' ? 'вы думаете' : (mine === 'no' ? 'вы не едете' : ''))
  const cap = hasLimit
    ? (spotsNum === 0 ? `мест нет · ${going} из ${limit}` : `занято ${going} из ${limit} · свободно ${spotsNum}`)
    : 'лимита нет'
  const foot = [cap, you].filter(Boolean).join(' · ')
  const bar = hasLimit
    ? `<div class="event-reg-dash-bar" aria-hidden="true"><i style="width:${pct}%"></i></div>`
    : ''
  return `<div class="event-reg-dash">
    <div class="event-reg-dash-nums">
      <div class="event-reg-dash-item is-go">
        <b>${going}</b>
        <span>едут</span>
      </div>
      <div class="event-reg-dash-item is-maybe">
        <b>${maybe}</b>
        <span>думают</span>
      </div>
      <div class="event-reg-dash-item is-no">
        <b>${no}</b>
        <span>нет</span>
      </div>
    </div>
    ${bar}
    ${foot ? `<p class="event-reg-dash-cap">${escapeHtml(foot)}</p>` : ''}
  </div>`
}

export function renderEventCard(event){
  const title = event.title || 'Событие'
  const city = event.city || event.location || ''
  const dateText = [formatDate(event.event_date), event.event_time ? String(event.event_time).slice(0, 5) : ''].filter(Boolean).join(' · ')
  const typeName = event.event_type?.name || event.type || ''
  const when = whenBadge(event.event_date)

  return `
  <div class="car-card-compact event-card-compact" data-id="${event.id}">
    <div class="car-image-container">
      ${typeName ? `<div class="car-status-badge">${escapeHtml(typeName)}</div>` : ''}
      ${when.text ? `<div class="event-when-badge ${when.cls}">${escapeHtml(when.text)}</div>` : ''}
      ${phEvent(event, 'medium')}
      <div class="car-overlay-info">
        <div class="car-title-overlay">${escapeHtml(title)}</div>
        <div class="car-specs-overlay">${escapeHtml([dateText, city].filter(Boolean).join(' · '))}</div>
      </div>
    </div>
    <div class="event-card-foot">
      ${renderEventOccupancy(event)}
    </div>
  </div>`
}
