// Демо UI Kit 2: те же модалки, что в kit 1, но страница в более светлой тёмной теме.

import { bindHintPops } from './components/hints.js?v=tip2'
import {
  renderKitEventModal, renderKitEventEditModal, renderKitGuideModal
} from './ui_kit_modals.js?v=event72'

const EVENT_IMG = new URL('../img/nav/events.png', import.meta.url).href
const GUIDE_IMG = new URL('../img/nav/guide.png', import.meta.url).href

const demoEvent = {
  title: 'Вечерний заезд по набережной',
  description: 'Сбор у парка, дальше — маршрут вдоль воды. Открытый верх приветствуется.',
  photo: EVENT_IMG,
  dateLabel: '15 июня 2026',
  dateValue: '2026-06-15',
  time: '19:00',
  city: 'Минск',
  location: 'Парк у набережной',
  type: 'Поездка',
  max_participants: 6,
  invite: false,
  status: 'Активно',
  organizer: { name: 'Иван Петров', username: 'ivan_cabriolet' },
}

const demoGuide = {
  title: 'Автомойка SelfWash',
  labels: ['мойка', 'минск', 'кабрио'],
  description: 'Бесконтактная мойка, удобный заезд для кабриолетов.',
  photo: GUIDE_IMG,
  rating: { overall: 4.1, quality: 4.5, speed: 4.0, price: 3.8, count: 4 },
  author: { name: 'Иван Петров', username: 'ivan_cabriolet' },
}

function put(id, html){
  const el = document.getElementById(id)
  if (el) el.innerHTML = html
}

put('d2-event', renderKitEventModal(demoEvent))
put('d2-event-edit', renderKitEventEditModal(demoEvent))
put('d2-guide', renderKitGuideModal(demoGuide))
bindHintPops(document)
