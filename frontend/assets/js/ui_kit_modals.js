// Макеты модалок для UI Kit — эталон до внедрения в разделы приложения.
// Используем те же классы и функции sheet.js, что и в боевых модалках.

import { hintBubble } from './components/hints.js?v=tip2'
import { renderEventRegDash } from './components/cards/event_card.js?v=reglive1'
import { phUser, phCar } from './components/media.js?v=cabrio20'
import { roleLabelRu } from './components/roles.js?v=ru1'
import {
  escapeHtml, viewVal, sheetField, renderPersonLink, renderCarLink, renderAddMyCarButton, headerActions, renderLabelChips
} from './components/sheet.js?v=tags1'

function createFoot(){
  return `<div class="modal-footer create-foot">
    <button type="button" class="btn-ghost" data-create-cancel>Отмена</button>
    <button type="button" class="btn-primary" data-create-save>Сохранить</button>
  </div>`
}

function createShell(title, bodyHtml){
  return `<div class="modal-content modal-compact sheet-card create-sheet editing">
    <div class="modal-header create-head">
      <div class="modal-title">${escapeHtml(title)}</div>
      <div class="sheet-actions"><button type="button" class="modal-close">×</button></div>
    </div>
    <div class="modal-body">${bodyHtml}</div>
    ${createFoot()}
  </div>`
}

export function kitModalShell(inner, { asPage = false } = {}){
  const cls = asPage ? 'kit-modal-shell kit-modal-shell--page' : 'kit-modal-shell'
  return `<div class="${cls}">${inner}</div>`
}

function sheetCard(title, actionsHtml, bodyHtml, extraClass = ''){
  return `<div class="modal-content modal-compact sheet-card ${extraClass}">
    <div class="modal-header">
      <div class="modal-title">${escapeHtml(title)}</div>
      <div class="sheet-actions">${actionsHtml}</div>
    </div>
    <div class="modal-body">${bodyHtml}</div>
  </div>`
}

function sheetSection(title, fieldsHtml){
  return `<div class="sheet-section-title">${escapeHtml(title)}</div><div class="sheet-grid">${fieldsHtml}</div>`
}

/** п. 54 — пустой каркас */
export function renderKitModalFrame(){
  const body = `<p class="kit-modal-hint">Тело модалки: фото, ссылки, сетка полей</p>`
  return kitModalShell(sheetCard(
    'Заголовок раздела',
    `<button type="button" class="btn-ghost">Изменить</button><button type="button" class="modal-close">×</button>`,
    body
  ))
}

/** п. 55 — Участники: карточка человека (просмотр) */
export function renderKitUserModal(user, cars = []){
  const fullName = `${user.first_name_app || ''} ${user.last_name_app || ''}`.trim() || 'Без имени'
  const ini = (user.first_name_app?.[0] || '') + (user.last_name_app?.[0] || '')
  const meta = [user.username ? '@' + user.username : '', user.city || ''].filter(Boolean).join(' · ')
  const role = roleLabelRu(user.role)

  const hero = `<div class="sheet-hero-wrap">
    <div class="sheet-hero">
      ${phUser(user, ini, 'medium', true)}
      <div class="sheet-hero-text">
        <div class="sheet-hero-name-row">
          <div class="sheet-hero-name">${escapeHtml(fullName)}</div>
          ${user.username ? `<button type="button" class="btn-ghost btn-write">Написать</button>` : ''}
        </div>
        ${meta ? `<div class="sheet-hero-meta">${escapeHtml(meta)}</div>` : `<div class="sheet-hero-meta sheet-empty">город не указан</div>`}
        ${role ? `<span class="role-badge">${escapeHtml(role)}</span>` : ''}
      </div>
    </div>
  </div>`

  const fields = [
    sheetField('Имя', viewVal(user.first_name_app)),
    sheetField('Фамилия', viewVal(user.last_name_app)),
    sheetField('Город', viewVal(user.city)),
    sheetField('Страна', viewVal(user.country || '')),
    sheetField('О себе', viewVal(user.about || ''), 'full'),
  ].join('')

  const carsBlock = cars.length
    ? `<div class="sheet-section-title">Автомобили</div><div class="sheet-links">${cars.map(c => renderCarLink(c)).join('')}</div>`
    : `<div class="sheet-section-title">Автомобили</div><p class="sheet-empty" style="margin:0">Автомобилей пока нет</p>`

  const actions = headerActions({ canEdit: true, editing: false, withClose: true })

  return kitModalShell(sheetCard(
    'Участник',
    actions,
    hero + sheetSection('Основная информация', fields) + carsBlock
  ))
}

/** п. 56 — Авто: карточка машины (просмотр) */
export function renderKitCarModal(car, owner){
  const title = `${car.brand?.name || ''} ${car.model || ''}`.trim() || 'Автомобиль'
  const year = car.year ? String(car.year) : ''
  const status = car.status?.name || ''

  const cover = `<div class="main-photo-compact">
    ${phCar(car, 'medium', true)}
    ${status ? `<span class="sheet-photo-badge" style="position:absolute;top:10px;left:10px">${escapeHtml(status)}</span>` : ''}
    <div class="sheet-photo-caption">
      <div class="sheet-photo-title">${escapeHtml(title)}</div>
      <div class="sheet-photo-meta">${year ? escapeHtml(year) : 'год не указан'}</div>
    </div>
  </div>`

  const ownerLink = owner ? renderPersonLink(owner) : ''
  const mainFields = [
    sheetField('Марка', viewVal(car.brand?.name)),
    sheetField('Модель', viewVal(car.model)),
    sheetField('Год', viewVal(car.year)),
    sheetField('Цвет', viewVal(car.color)),
    sheetField('Крыша', viewVal(car.roof_type || '')),
  ].join('')
  const privateFields = sheetField('Гос. номер', viewVal(car.reg_number || ''))
  const description = sheetField('Описание', viewVal(car.description || ''), 'full')

  const actions = headerActions({ canEdit: true, editing: false, withClose: true })

  return kitModalShell(sheetCard(
    'Автомобиль',
    actions,
    cover + ownerLink
      + sheetSection('Характеристики', mainFields)
      + sheetSection('Идентификация', privateFields)
      + sheetSection('Дополнительно', description)
  ))
}

/** п. 57 — подсказка для карты: та же модалка с метки */
export function renderKitMapModalHint(){
  return `<div class="kit-map-modal-bridge">
    <div class="kit-map-modal-from">
      <div class="avatar-marker fresh" style="position:relative;transform:none">
        <div class="avatar-wrap">${phUser(null, '', 'mini', true)}</div>
      </div>
      <p class="kit-note">Тап по метке</p>
    </div>
    <div class="kit-map-modal-arrow" aria-hidden="true">→</div>
    <p class="kit-note kit-map-modal-caption">Открывается модалка п. 55 или 56 — отдельного «карточного» дизайна для карты нет</p>
  </div>`
}

const EVENT_INVITE_HINT = 'Снято — открытая встреча, любой участник видит её и может ответить «еду».\n\nСтоит — встреча по приглашению.'

function kitEventCover(event, { fab = false } = {}){
  const img = event.photo
    ? `<img class="main-image ph-img" src="${escapeHtml(event.photo)}" alt="">`
    : `<div class="ph ph-event" style="position:relative;height:160px"><span class="ph-fallback"></span></div>`
  const when = fab ? '' : `<div class="event-when-badge">15 июня</div>`
  const fabBtn = fab
    ? `<button type="button" class="photo-upload-fab photo-upload-center"><span>📷</span><span>Фото</span></button>`
    : ''
  return `<div class="main-photo-compact">${img}${when}${fabBtn}</div>`
}

function kitEventAuthor(event){
  const org = event.organizer || { name: 'Иван Петров', username: 'ivan_cabriolet' }
  const line = [org.name, org.username ? '@' + String(org.username).replace(/^@/, '') : ''].filter(Boolean).join(' · ')
  return `<p class="guide-place-author">автор ${escapeHtml(line)}</p>`
}

function kitEventFacts(event, { editing = false, creating = false } = {}){
  const limit = event.max_participants == null || event.max_participants === ''
    ? 'без лимита'
    : String(event.max_participants)
  const format = event.invite ? 'по приглашению' : 'открытая'
  const typeSel = `<select class="filter-select"><option selected>Поездка</option><option>Встреча</option></select>`
  const inviteRow = `<label class="sheet-check"><input type="checkbox"${event.invite ? ' checked' : ''}/> да</label>${hintBubble(EVENT_INVITE_HINT, { label: 'Что значит эта галка' })}`
  if (editing || creating) {
    const rows = [
      sheetField('Дата', `<input class="filter-input" type="date" value="${escapeHtml(event.dateValue || '2026-06-15')}">`),
      sheetField('Время', `<input class="filter-input" type="time" value="${escapeHtml(event.time || '19:00')}">`),
      sheetField('Город', `<input class="filter-input" value="${escapeHtml(event.city || '')}">`),
      sheetField('Место', `<input class="filter-input" value="${escapeHtml(event.location || '')}">`),
      sheetField('Тип', typeSel),
      sheetField('Лимит', `<input class="filter-input" type="number" min="1" placeholder="нет" value="${escapeHtml(event.max_participants == null ? '' : String(event.max_participants))}">`),
      sheetField('По приглашению', `<div class="sheet-check-row">${inviteRow}</div>`),
    ]
    if (editing && !creating) {
      rows.push(sheetField('Статус', viewVal(event.status || 'Активно')))
    }
    return rows.join('')
  }
  return [
    sheetField('Дата', viewVal(event.dateLabel)),
    sheetField('Время', viewVal(event.time)),
    sheetField('Город', viewVal(event.city)),
    sheetField('Место', viewVal(event.location || 'Парк у набережной')),
    sheetField('Тип', viewVal(event.type)),
    sheetField('Лимит', viewVal(limit)),
    sheetField('Формат', viewVal(format)),
    sheetField('Статус', viewVal(event.status || 'Активно')),
  ].join('')
}

function kitEventReg(event){
  return `<section class="guide-block guide-block-reviews">
    <p class="guide-block-kicker">регистрация</p>
    ${renderEventRegDash(event)}
    <div class="event-reg-vote">
      <p class="guide-block-kicker">ваш ответ</p>
      <div class="rsvp-row">
        <button type="button" class="rsvp-btn is-on">Да</button>
        <button type="button" class="rsvp-btn">Возможно</button>
        <button type="button" class="rsvp-btn">Нет</button>
      </div>
      <label class="sheet-check rsvp-plus"><input type="checkbox" checked/> +1 гость</label>
    </div>
  </section>
  <section class="guide-block guide-block-who">
    <p class="event-reg-who-title">Кто ответил</p>
    <div class="event-reg-people">
      <div class="event-reg-people-block">
        <p class="event-reg-people-h"><span>Едут</span><b>3</b></p>
        <ul class="event-who-list">
          <li>Иван Петров <em>+1</em></li>
          <li>Анна К.</li>
          <li>Дмитрий Л.</li>
        </ul>
      </div>
      <div class="event-reg-people-block is-soft">
        <p class="event-reg-people-h"><span>Думают</span><b>1</b></p>
        <ul class="event-who-list">
          <li>Сергей М.</li>
        </ul>
      </div>
      <div class="event-reg-people-block is-no">
        <p class="event-reg-people-h"><span>Не едут</span><b>1</b></p>
        <ul class="event-who-list">
          <li>Олег Н.</li>
        </ul>
      </div>
    </div>
  </section>`
}

/** п. 58 — просмотр: все поля встречи + регистрация */
export function renderKitEventModal(event){
  const title = event.title || 'Событие'
  const actions = `<button type="button" class="btn-ghost">Изменить</button><button type="button" class="modal-close">×</button>`
  const body = `
    <div class="guide-modal-stack">
      <section class="guide-block">
        ${kitEventCover(event)}
        <div class="event-info-head">
          <h3 class="guide-place-title">${escapeHtml(title)}</h3>
          <p class="guide-place-desc">${escapeHtml(event.description || '')}</p>
        </div>
        <div class="sheet-grid">${kitEventFacts(event)}</div>
        ${kitEventAuthor(event)}
      </section>
      ${kitEventReg(event)}
    </div>`
  return kitModalShell(sheetCard('Событие', actions, body))
}

/** п. 72 — та же карточка в правке: светлые поля, регистрация и автор как есть */
export function renderKitEventEditModal(event){
  const title = event.title || 'Событие'
  const actions = headerActions({ canEdit: true, editing: true, withClose: true })
  const body = `
    <div class="guide-modal-stack">
      <section class="guide-block">
        ${kitEventCover(event, { fab: true })}
        <div class="event-info-head">
          <input class="filter-input event-title-input" value="${escapeHtml(title)}">
          <textarea class="filter-input event-desc-input" rows="3">${escapeHtml(event.description || '')}</textarea>
        </div>
        <div class="sheet-grid">${kitEventFacts(event, { editing: true })}</div>
        ${kitEventAuthor(event)}
      </section>
      ${kitEventReg(event)}
    </div>`
  return kitModalShell(sheetCard('Событие', actions, body, 'editing'))
}

/** п. 64 — создание: те же поля встречи, без регистрации */
export function renderKitEventCreate(){
  const event = {
    title: 'Вечерний заезд по набережной',
    description: 'Сбор у парка, дальше — маршрут вдоль воды. Открытый верх приветствуется.',
    dateValue: '2026-06-15',
    time: '19:00',
    city: 'Минск',
    location: 'Парк у набережной',
    max_participants: 6,
    invite: false,
  }
  const body = `
    <div class="guide-modal-stack">
      <section class="guide-block">
        ${kitEventCover(event, { fab: true })}
        <div class="event-info-head">
          <input class="filter-input event-title-input" value="${escapeHtml(event.title)}">
          <textarea class="filter-input event-desc-input" rows="3">${escapeHtml(event.description)}</textarea>
        </div>
        <div class="sheet-grid">${kitEventFacts(event, { creating: true })}</div>
      </section>
    </div>`
  return kitModalShell(createShell('Создание события', body))
}

// Звёзды для средней по категории (как на плитке, шаг 0.5)
function kitAvgStars(value){
  const n = Number(value)
  const filled = !isFinite(n) ? 0 : Math.max(0, Math.min(5, Math.round(n * 2) / 2))
  const cells = [1, 2, 3, 4, 5].map((i) => {
    let cls = 'is-empty'
    if (filled >= i) cls = 'is-full'
    else if (filled >= i - 0.5) cls = 'is-half'
    return `<span class="guide-star ${cls}" aria-hidden="true">★</span>`
  }).join('')
  return `<div class="guide-stars" aria-hidden="true">${cells}</div>`
}

function kitAvgCell(label, value){
  const num = Number(value)
  const text = isFinite(num) ? num.toFixed(1) : '—'
  return `<div class="guide-avg-cell">
    <div class="guide-avg-label">${escapeHtml(label)}</div>
    <div class="guide-avg-num">${escapeHtml(text)}</div>
    ${kitAvgStars(value)}
  </div>`
}

function kitGuideAuthorLine(place){
  const author = place.author || { name: 'Иван Петров', username: 'ivan_cabriolet' }
  const line = [author.name, author.username ? '@' + String(author.username).replace(/^@/, '') : '']
    .filter(Boolean).join(' · ')
  return `<p class="guide-place-author">автор ${escapeHtml(line)}</p>`
}

function kitGuideCover(place, { editing = false } = {}){
  const rating = place.rating || { overall: 4.1 }
  const fab = editing
    ? `<button type="button" class="photo-upload-fab photo-upload-center"><span>📷</span><span>Фото</span></button>`
    : ''
  if (!place.photo && !editing) return ''
  const img = place.photo
    ? `<img class="main-image ph-img" src="${escapeHtml(place.photo)}" alt="">`
    : `<div class="ph ph-place" style="position:relative;height:160px"><span class="ph-fallback"></span></div>`
  return `<div class="main-photo-compact">
    ${img}
    <span class="sheet-photo-badge">${escapeHtml(String(rating.overall))} / 5</span>
    ${fab}
  </div>`
}

function kitGuideAvgsBlock(place){
  const rating = place.rating || { overall: 4.1, quality: 4.5, speed: 4.0, price: 3.8, count: 4 }
  return `<section class="guide-block">
    <p class="guide-block-kicker">средние оценки · ${escapeHtml(String(rating.count || 0))} отзыва</p>
    <div class="guide-avg-grid">
      ${kitAvgCell('Качество', rating.quality)}
      ${kitAvgCell('Скорость', rating.speed)}
      ${kitAvgCell('Цена', rating.price)}
    </div>
  </section>`
}

function kitGuideReviewsBlock(){
  return `<section class="guide-block guide-block-reviews">
    <p class="guide-block-kicker">отзывы</p>
    <div class="review-list">
      <button type="button" class="review-row"><span class="review-row-main"><span class="review-row-name">Иван Петров</span><span class="review-row-text">Удобный заезд, быстро сушат верх.</span></span><span class="review-row-score">4.3</span><span class="review-row-go">›</span></button>
      <button type="button" class="review-row"><span class="review-row-main"><span class="review-row-name">Анна К.</span><span class="review-row-text">Цена нормальная, сушка чуть подольше.</span></span><span class="review-row-score">3.7</span><span class="review-row-go">›</span></button>
    </div>
  </section>`
}

/** п. 59 — Отзывы: карточка места. Под фото: название, описание, ярлыки; автор тонкой строкой. */
export function renderKitGuideModal(place){
  const title = place.title || 'Место'
  const labels = place.labels || ['мойка']
  const actions = `<button type="button" class="btn-ghost">Изменить</button><button type="button" class="modal-close">×</button>`
  const body = `
    <div class="guide-modal-stack">
      <section class="guide-block">
        ${kitGuideCover(place)}
        <h3 class="guide-place-title">${escapeHtml(title)}</h3>
        <p class="guide-place-desc">${escapeHtml(place.description || '')}</p>
        <div class="guide-place-tags">${renderLabelChips(labels)}</div>
        ${kitGuideAuthorLine(place)}
      </section>
      ${kitGuideAvgsBlock(place)}
      <button type="button" class="btn-primary btn-review-cta">Поставить отзыв</button>
      ${kitGuideReviewsBlock()}
    </div>`
  return kitModalShell(sheetCard('Карточка', actions, body))
}

/** п. 71 — та же карточка места в режиме правки: те же блоки, поля чуть светлее */
export function renderKitGuideEditModal(place){
  const title = place.title || 'Место'
  const labels = place.labels || ['мойка']
  const actions = headerActions({ canEdit: true, editing: true, withClose: true })
  const body = `
    <div class="guide-modal-stack">
      <section class="guide-block">
        ${kitGuideCover(place, { editing: true })}
        <div class="sheet-grid">
          ${sheetField('Название', `<input class="filter-input" value="${escapeHtml(title)}">`, 'full')}
          ${sheetField('Описание', `<textarea class="filter-input" rows="2">${escapeHtml(place.description || '')}</textarea>`, 'full')}
          ${sheetField('Ярлыки', `<div class="label-editor">${renderLabelChips(labels, { editing: true, wrap: false })}<input class="filter-input" placeholder="+"></div>`, 'full')}
        </div>
        ${kitGuideAuthorLine(place)}
      </section>
      ${kitGuideAvgsBlock(place)}
      ${kitGuideReviewsBlock()}
    </div>`
  return kitModalShell(sheetCard('Карточка', actions, body, 'editing'))
}

/** п. 65 — создание места: название, описание, ярлыки */
export function renderKitGuideCreate(){
  const cover = `<div class="main-photo-compact">
    <div class="ph ph-place" style="position:relative;height:160px"><span class="ph-fallback"></span></div>
    <button type="button" class="photo-upload-fab photo-upload-center"><span>📷</span><span>Фото</span></button>
  </div>`
  const main = [
    sheetField('Название', `<input class="filter-input" value="Автомойка SelfWash">`, 'full'),
    sheetField('Описание', `<textarea class="filter-input" rows="2">Бесконтактная мойка, удобный заезд для кабриолетов.</textarea>`, 'full'),
    sheetField('Ярлыки', `<div class="label-editor">${renderLabelChips(['мойка', 'минск', 'кабрио'], { editing: true, wrap: false })}<input class="filter-input" placeholder="+"></div>`, 'full'),
  ].join('')
  return kitModalShell(createShell('Добавить', cover + `<div class="sheet-grid">${main}</div>`))
}

/** п. 60 — Профиль: та же карточка на странице, без затемнения и без × */
export function renderKitProfilePage(user, cars = []){
  const fullName = `${user.first_name_app || ''} ${user.last_name_app || ''}`.trim() || 'Без имени'
  const ini = (user.first_name_app?.[0] || '') + (user.last_name_app?.[0] || '')
  const meta = [user.username ? '@' + user.username : '', user.city || ''].filter(Boolean).join(' · ')
  const role = roleLabelRu(user.role)

  const inner = `<div class="sheet-card page-card">
    <div class="sheet-head">
      <div class="sheet-head-title">Мой профиль</div>
      <div class="sheet-actions">${headerActions({ canEdit: true, editing: false, withClose: false })}</div>
    </div>
    <div class="sheet-body">
      <div class="sheet-hero-wrap">
        <div class="sheet-hero">
          ${phUser(user, ini, 'medium', true)}
          <div class="sheet-hero-text">
            <div class="sheet-hero-name">${escapeHtml(fullName)}</div>
            ${meta ? `<div class="sheet-hero-meta">${escapeHtml(meta)}</div>` : ''}
            ${role ? `<span class="role-badge">${escapeHtml(role)}</span>` : ''}
          </div>
        </div>
      </div>
      <div class="sheet-grid">
        ${sheetField('Имя', viewVal(user.first_name_app))}
        ${sheetField('Город', viewVal(user.city))}
        ${sheetField('О себе', viewVal(user.about || ''), 'full')}
      </div>
      <div class="sheet-section-title">Автомобили</div>
      <div class="sheet-links">${cars.map(c => renderCarLink(c)).join('')}${renderAddMyCarButton('kit-add-my-car')}</div>
    </div>
  </div>`

  return kitModalShell(inner, { asPage: true })
}

/** п. 61 — Режим редактирования (на примере авто) */
export function renderKitEditModal(car){
  const title = `${car.brand?.name || ''} ${car.model || ''}`.trim() || 'Автомобиль'
  const actions = headerActions({ canEdit: true, editing: true, withClose: true })

  const cover = `<div class="main-photo-compact">
    ${phCar(car, 'medium', true)}
    <button type="button" class="photo-upload-fab photo-upload-center"><span>📷</span><span>Фото</span></button>
  </div>`

  const fields = `<div class="sheet-grid editing">
    <div class="sheet-field"><span class="sheet-label">Модель</span><input class="filter-input" value="${escapeHtml(car.model || '')}"></div>
    <div class="sheet-field"><span class="sheet-label">Год</span><input class="filter-input" value="${escapeHtml(String(car.year || ''))}"></div>
    <div class="sheet-field"><span class="sheet-label">Цвет</span><input class="filter-input" value="${escapeHtml(car.color || '')}"></div>
    <div class="sheet-field"><span class="sheet-label">Крыша</span>
      <select class="filter-select"><option>Мягкая</option><option>Жёсткая</option></select>
    </div>
    <div class="sheet-field full"><span class="sheet-label">Описание</span><textarea class="filter-input" rows="2">${escapeHtml(car.description || '')}</textarea></div>
  </div>`

  return kitModalShell(sheetCard('Редактирование авто', actions, cover + fields, 'editing'))
}

/** п. 66 — Режим создания (шапка «Создание…», кнопки внизу, светлые поля) */
export function renderKitCreateModal(car){
  const cover = `<div class="main-photo-compact">
    ${phCar(car, 'medium', true)}
    <button type="button" class="photo-upload-fab photo-upload-center"><span>📷</span><span>Фото</span></button>
  </div>`
  const fields = `<div class="sheet-grid">
    <div class="sheet-field"><span class="sheet-label">Модель</span><input class="filter-input" value="${escapeHtml(car.model || '')}"></div>
    <div class="sheet-field"><span class="sheet-label">Год</span><input class="filter-input" value="${escapeHtml(String(car.year || ''))}"></div>
    <div class="sheet-field"><span class="sheet-label">Цвет</span><input class="filter-input" value="${escapeHtml(car.color || '')}"></div>
    <div class="sheet-field"><span class="sheet-label">Крыша</span>
      <select class="filter-select"><option>Мягкая</option><option>Жёсткая</option></select>
    </div>
    <div class="sheet-field full"><span class="sheet-label">Описание</span><textarea class="filter-input" rows="2">${escapeHtml(car.description || '')}</textarea></div>
  </div>`
  return kitModalShell(createShell('Создание авто', cover + fields))
}
