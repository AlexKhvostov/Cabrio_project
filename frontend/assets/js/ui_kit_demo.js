// Живые примеры карточек и рамок фото для страницы UI Kit.

// Сами стили — в styles.css, здесь только подставляем те же функции, что в приложении.



import { phUser, phCar } from './components/media.js?v=cabrio20'

import { renderUserCard, renderMemberCarStack } from './components/cards/user_card.js?v=cabrio14'

import { renderCarCard } from './components/cards/car_card.js?v=cabrio14'

import { renderEventCard } from './components/cards/event_card.js?v=list2'

import { renderGuideCard } from './components/cards/guide_card.js?v=list2'

import {

  renderPersonLink, renderCarLink, sheetField, viewVal, headerActions, emptyMark

} from './components/sheet.js?v=write1'

import { bindHintPops } from './components/hints.js?v=tip2'
import {
  renderKitModalFrame, renderKitUserModal, renderKitCarModal, renderKitMapModalHint,
  renderKitEventModal, renderKitGuideModal, renderKitProfilePage, renderKitEditModal,
  renderKitEventCreate, renderKitGuideCreate, renderKitCreateModal
} from './ui_kit_modals.js?v=tags1'

const EVENT_IMG = new URL('../img/nav/events.png', import.meta.url).href
const GUIDE_IMG = new URL('../img/nav/guide.png', import.meta.url).href



const demoUserBase = {

  id: 1,

  first_name_app: 'Иван',

  last_name_app: 'Петров',

  username: 'ivan_cabriolet',

  city: 'Минск',

  role: { code: 'member', name: 'Участник' },

}



const demoUser1 = {

  ...demoUserBase,

  cars: [{ id: 11, brand: { name: 'BMW' }, year: 2016 }]

}



const demoUser2 = {

  ...demoUserBase,

  cars: [

    { id: 11, brand: { name: 'BMW' } },

    { id: 12, brand: { name: 'Mazda' } },

  ]

}



const demoUser3 = {

  ...demoUserBase,

  cars: [

    { id: 11, brand: { name: 'BMW' } },

    { id: 12, brand: { name: 'Mazda' } },

    { id: 13, brand: { name: 'Porsche' } },

  ]

}



const demoUser4 = {

  ...demoUserBase,

  cars: [

    ...demoUser3.cars,

    { id: 14, brand: { name: 'Audi' } },

  ]

}



const demoUserEmpty = {

  id: 2,

  first_name_app: 'Анна',

  last_name_app: 'К.',

  username: 'anna',

  city: '',

  role: { code: 'user', name: 'Пользователь' },

  cars: []

}



const demoCar = {

  id: 11,

  model: 'Z4',

  year: 2016,

  color: 'оранжевый',

  brand: { name: 'BMW' },

  status: { code: 'pending', name: 'На модерации' },

  owner: demoUserBase

}



const demoCarEmpty = {

  id: 12,

  model: 'MX-5',

  year: 2018,

  brand: { name: 'Mazda' },

  status: { code: 'active', name: 'Активен' },

  owner: demoUserEmpty

}



function put(id, html){

  const el = document.getElementById(id)

  if (el) el.innerHTML = html

}



put('d-ph-user-empty', phUser(demoUserEmpty, 'АК', 'medium', true))

put('d-ph-user-photo', phUser(demoUserBase, 'ИП', 'medium', true))

put('d-ph-user-list', phUser(demoUserEmpty, 'АК', 'mini', true))

put('d-ph-car-empty', phCar(demoCarEmpty, 'medium', true))

put('d-ph-car-photo', phCar(demoCar, 'medium', true))

put('d-cover', `<div class="main-photo-compact">${phCar(demoCar, 'medium', true)}

  <span class="sheet-photo-badge" style="position:absolute;top:10px;left:10px">На модерации</span>

  <div class="sheet-photo-caption">

    <div class="sheet-photo-title">BMW Z4</div>

    <div class="sheet-photo-meta">2016</div>

  </div>

  <button type="button" class="photo-upload-fab photo-upload-center"><span>📷</span><span>Фото</span></button>

</div>`)



put('d-member-card-3', renderUserCard(demoUser3))

put('d-member-card-2', renderUserCard(demoUser2))

put('d-member-card-1', renderUserCard(demoUser1))

put('d-member-card-empty', renderUserCard(demoUserEmpty))
put('d-car-stack', renderMemberCarStack(demoUser3.cars))

put('d-car-grid', `<div class="cars-grid">${renderCarCard(demoCar)}${renderCarCard(demoCarEmpty)}</div>`)

function kitIsoDays(n){
  const d = new Date()
  d.setDate(d.getDate() + n)
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${d.getFullYear()}-${m}-${day}`
}

const demoEventSoon = {
  id: 101,
  title: 'Вечерний заезд',
  city: 'Минск',
  event_date: kitIsoDays(0),
  event_time: '19:00',
  event_type: { name: 'Поездка' },
  going_count: 8,
  maybe_count: 3,
  spots_left: 4,
  my_rsvp: { confidence: 'yes', plus_one: true },
}
const demoEventLater = {
  id: 102,
  title: 'Завтрак у озера',
  city: 'Заславль',
  event_date: kitIsoDays(5),
  event_time: '10:00',
  event_type: { name: 'Встреча' },
  going_count: 2,
  maybe_count: 6,
  spots_left: null,
  my_rsvp: { confidence: 'maybe' },
}
put('d-event-grid', `<div class="cars-grid">${renderEventCard(demoEventSoon)}${renderEventCard(demoEventLater)}</div>`)

const demoGuideBad = {
  id: 201,
  name: 'Мойка у кольца',
  labels: [{ name: 'мойка' }],
  rating: { overall: 1.2, count: 3 },
}
const demoGuideOk = {
  id: 202,
  name: 'Кафе OpenTop',
  labels: [{ name: 'кафе' }, { name: 'минск' }],
  rating: { overall: 3.5, count: 8 },
}
const demoGuideGood = {
  id: 203,
  name: 'Детейлинг Cabrio',
  labels: [{ name: 'детейлинг' }],
  rating: { overall: 4.7, count: 12 },
}
put('d-guide-grid', `<div class="cars-grid">${renderGuideCard(demoGuideBad)}${renderGuideCard(demoGuideOk)}${renderGuideCard(demoGuideGood)}</div>`)

put('d-rel-person', renderPersonLink(demoUserBase))

put('d-rel-car', renderCarLink(demoCar))

put('d-sheet-fields', [

  sheetField('Имя', viewVal('Иван')),

  sheetField('Город', viewVal('')),

  sheetField('О себе', viewVal('Люблю открытый верх и вечерние маршруты.'), 'full'),

].join(''))

put('d-header-view', headerActions({ canEdit: true, editing: false, withClose: true }))

put('d-header-edit', headerActions({ canEdit: true, editing: true, withClose: true }))

put('d-hero', `${phUser(demoUserBase, 'ИП', 'medium', true)}

  <div class="sheet-hero-text">

    <div class="sheet-hero-name">Иван Петров</div>

    <div class="sheet-hero-meta">@ivan_cabriolet · Минск</div>

    <span class="role-badge">Участник</span>

  </div>`)

put('d-empty-mark', emptyMark())



// Для отладки: 4 машины с +1 на передней

put('d-member-card-4', renderUserCard(demoUser4))

// Модалки разделов (п. 54–61)
const demoEvent = {
  title: 'Вечерний заезд по набережной',
  dateLabel: '15 июня 2026',
  time: '19:00',
  city: 'Минск',
  type: 'Поездка',
  status: 'Активно',
  description: 'Сбор у парка, дальше — маршрут вдоль воды. Открытый верх приветствуется.',
  photo: EVENT_IMG,
}

const demoGuide = {
  title: 'Автомойка SelfWash',
  labels: ['мойка', 'минск', 'кабрио'],
  description: 'Бесконтактная мойка, удобный заезд для кабриолетов.',
  photo: GUIDE_IMG,
}

const demoCarView = {
  id: 11,
  model: 'Z4',
  year: 2016,
  color: 'оранжевый',
  roof_type: 'Мягкая',
  reg_number: '',
  description: '',
  brand: { name: 'BMW' },
  status: { name: 'На модерации' },
}

put('d-modal-frame', renderKitModalFrame())
put('d-modal-user', renderKitUserModal({ ...demoUserBase, country: 'Беларусь', about: 'Люблю открытый верх и вечерние маршруты.' }, demoUser3.cars))
put('d-modal-car', renderKitCarModal(demoCarView, demoUserBase))
put('d-modal-map', renderKitMapModalHint())
put('d-modal-event', renderKitEventModal(demoEvent))
put('d-modal-guide', renderKitGuideModal(demoGuide))
put('d-modal-profile', renderKitProfilePage({ ...demoUserBase, about: 'Люблю открытый верх.' }, demoUser2.cars))
put('d-modal-edit', renderKitEditModal({ ...demoCarView, description: 'Люблю вечерние поездки с открытым верхом.' }))
put('d-modal-create', renderKitCreateModal({ ...demoCarView, description: '' }))
put('d-modal-event-create', renderKitEventCreate())
put('d-modal-guide-create', renderKitGuideCreate())

bindHintPops(document)


