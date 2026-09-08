// Макеты модалок для UI Kit — эталон до внедрения в разделы приложения.
// Используем те же классы и функции sheet.js, что и в боевых модалках.

import { phUser, phCar } from './components/media.js?v=cabrio20'
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

function occupancyLine(going, spots, maybe){
  const mid = spots == null ? '∞' : String(spots)
  return `<div class="event-fill">
    <span class="event-fill-nums"><b>${going}</b><i>·</i><b>${escapeHtml(mid)}</b><i>·</i><b>${maybe}</b></span>
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
  const role = user.role?.name || user.role?.code || ''

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

/** п. 58 — События: карточка встречи (целевой вид sheet-card) */
export function renderKitEventModal(event){
  const title = event.title || 'Событие'
  const cover = event.photo
    ? `<div class="main-photo-compact">
        <img class="main-image ph-img" src="${escapeHtml(event.photo)}" alt="">
        <div class="sheet-photo-caption">
          <div class="sheet-photo-title">${escapeHtml(title)}</div>
          <div class="sheet-photo-meta">${escapeHtml(event.city || '')}</div>
        </div>
      </div>`
    : ''

  const scheduleFields = [
    sheetField('Дата', viewVal(event.dateLabel)),
    sheetField('Время', viewVal(event.time)),
    sheetField('Город', viewVal(event.city)),
    sheetField('Место', viewVal(event.location || 'Парк у набережной'), 'full'),
  ].join('')
  const eventFields = [
    sheetField('Тип', viewVal(event.type)),
    sheetField('Статус', viewVal(event.status)),
    sheetField('Описание', viewVal(event.description), 'full'),
  ].join('')
  const rsvp = `<div class="sheet-section-title">Участие</div>
    ${occupancyLine(4, 2, 1)}
    <div class="sheet-label">Кто едет</div>
    <div class="rsvp-people">
      <div class="rsvp-person">Иван Петров<em> · +1 гость</em></div>
      <div class="rsvp-person">Анна К.</div>
    </div>
    <div class="sheet-section-title">Будете участвовать?</div>
    <div class="rsvp-row">
      <button type="button" class="rsvp-btn is-on">Да</button>
      <button type="button" class="rsvp-btn">Возможно</button>
      <button type="button" class="rsvp-btn">Нет</button>
    </div>`

  const actions = `<button type="button" class="btn-ghost">Изменить</button><button type="button" class="modal-close">×</button>`

  return kitModalShell(sheetCard(
    'Событие',
    actions,
    cover + sheetSection('Когда и где', scheduleFields) + sheetSection('О событии', eventFields) + rsvp
  ))
}

/** п. 64 — создание события, как п. 66 */
export function renderKitEventCreate(){
  const cover = `<div class="main-photo-compact">
    <div class="ph ph-event" style="position:relative;height:160px"><span class="ph-fallback"></span></div>
    <button type="button" class="photo-upload-fab photo-upload-center"><span>📷</span><span>Фото</span></button>
  </div>`
  const when = [
    sheetField('Дата', `<input class="filter-input" type="date" value="2026-06-15">`),
    sheetField('Время', `<input class="filter-input" type="time" value="19:00">`),
    sheetField('Город', `<input class="filter-input" value="Минск">`),
    sheetField('Место', `<input class="filter-input" value="Парк у набережной">`, 'full'),
  ].join('')
  const about = [
    sheetField('Название', `<input class="filter-input" value="Вечерний заезд">`),
    sheetField('Тип', `<select class="filter-select"><option>Поездка</option></select>`),
    sheetField('Лимит', `<input class="filter-input" type="number" value="6">`),
    sheetField('Описание', `<textarea class="filter-input" rows="2">Сбор у парка.</textarea>`, 'full'),
  ].join('')
  return kitModalShell(createShell('Создание события', cover + sheetSection('Когда и где', when) + sheetSection('О событии', about)))
}

/** п. 59 — Отзывы: карточка */
export function renderKitGuideModal(place){
  const title = place.title || 'Место'
  const labels = place.labels || ['мойка']
  const cover = place.photo
    ? `<div class="main-photo-compact">
        <img class="main-image ph-img" src="${escapeHtml(place.photo)}" alt="">
        <div class="sheet-photo-caption">
          <div class="sheet-photo-title">${escapeHtml(title)}</div>
          <div class="sheet-photo-meta">${labels.map(n => '#' + n).join(' ')}</div>
        </div>
      </div>`
    : ''

  const body = [
    sheetField('Ярлыки', renderLabelChips(labels)),
    sheetField('Описание', viewVal(place.description), 'full'),
  ].join('')

  const actions = `<button type="button" class="btn-ghost">Изменить</button><button type="button" class="modal-close">×</button>`
  const reviews = `<div class="sheet-section-title">Оценки</div>
    <div class="review-avg">Средняя 8.2 из 10 · 4 отзыва</div>
    <div class="review-avg-parts">качество 8.5 · скорость 8.0 · цена 8.0</div>
    <button type="button" class="btn-ghost" style="width:100%">Написать отзыв</button>
    <div class="sheet-section-title">Отзывы</div>
    <div class="review-list">
      <button type="button" class="review-row"><span class="review-row-main"><span class="review-row-name">Иван Петров</span><span class="review-row-text">Удобный заезд, быстро сушат верх.</span></span><span class="review-row-score">8.3</span><span class="review-row-go">›</span></button>
    </div>`

  return kitModalShell(sheetCard('Карточка', actions, cover + `<div class="sheet-grid">${body}</div>` + reviews))
}

/** п. 65 — создание места, как п. 66 */
export function renderKitGuideCreate(){
  const cover = `<div class="main-photo-compact">
    <div class="ph ph-place" style="position:relative;height:160px"><span class="ph-fallback"></span></div>
    <button type="button" class="photo-upload-fab photo-upload-center"><span>📷</span><span>Фото</span></button>
  </div>`
  const main = [
    sheetField('Название', `<input class="filter-input" value="Автомойка SelfWash">`, 'full'),
    sheetField('Ярлыки', `<div class="label-editor">${renderLabelChips(['мойка', 'минск'], { editing: true, wrap: false })}<input class="filter-input" placeholder="+"></div>`),
    sheetField('Описание', `<textarea class="filter-input" rows="2">Бесконтактная мойка.</textarea>`, 'full'),
  ].join('')
  return kitModalShell(createShell('Добавить', cover + `<div class="sheet-grid">${main}</div>`))
}

/** п. 60 — Профиль: та же карточка на странице, без затемнения и без × */
export function renderKitProfilePage(user, cars = []){
  const fullName = `${user.first_name_app || ''} ${user.last_name_app || ''}`.trim() || 'Без имени'
  const ini = (user.first_name_app?.[0] || '') + (user.last_name_app?.[0] || '')
  const meta = [user.username ? '@' + user.username : '', user.city || ''].filter(Boolean).join(' · ')
  const role = user.role?.name || user.role?.code || ''

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
