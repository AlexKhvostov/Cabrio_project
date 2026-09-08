// Макеты модалок для UI Kit — эталон до внедрения в разделы приложения.
// Используем те же классы и функции sheet.js, что и в боевых модалках.

import { phUser, phCar } from './components/media.js?v=cabrio18'
import {
  escapeHtml, viewVal, sheetField, renderPersonLink, renderCarLink, headerActions
} from './components/sheet.js?v=write1'

/** Обёртка «затемнённый фон + карточка», как modal-overlay в приложении */
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
  ].join('')
  const eventFields = [
    sheetField('Тип', viewVal(event.type)),
    sheetField('Статус', viewVal(event.status)),
    sheetField('Описание', viewVal(event.description), 'full'),
  ].join('')

  const actions = `<button type="button" class="btn-ghost">Изменить</button><button type="button" class="modal-close">×</button>`

  return kitModalShell(sheetCard(
    'Событие',
    actions,
    cover + sheetSection('Когда и где', scheduleFields) + sheetSection('О событии', eventFields)
  ))
}

/** п. 59 — Гид: карточка места */
export function renderKitGuideModal(place){
  const title = place.title || 'Место'
  const cover = place.photo
    ? `<div class="main-photo-compact">
        <img class="main-image ph-img" src="${escapeHtml(place.photo)}" alt="">
        ${place.type ? `<span class="sheet-photo-badge" style="position:absolute;top:10px;left:10px">${escapeHtml(place.type)}</span>` : ''}
        <div class="sheet-photo-caption">
          <div class="sheet-photo-title">${escapeHtml(title)}</div>
          <div class="sheet-photo-meta">${escapeHtml(place.city || '')}</div>
        </div>
      </div>`
    : ''

  const placeFields = [
    sheetField('Тип', viewVal(place.type)),
    sheetField('Город', viewVal(place.city)),
    sheetField('Адрес', viewVal(place.address)),
  ].join('')
  const contactFields = [
    sheetField('Телефон', viewVal(place.phone)),
    sheetField('Сайт', viewVal(place.website)),
  ].join('')
  const description = sheetField('Описание', viewVal(place.description), 'full')

  const actions = `<button type="button" class="modal-close">×</button>`

  return kitModalShell(sheetCard(
    'Место',
    actions,
    cover
      + sheetSection('Основное', placeFields)
      + sheetSection('Контакты', contactFields)
      + sheetSection('Описание', description)
  ))
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
      <div class="sheet-links">${cars.map(c => renderCarLink(c)).join('')}</div>
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
