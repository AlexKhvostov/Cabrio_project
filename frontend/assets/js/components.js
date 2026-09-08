// Карточки списков. Тяжёлые модалки лежат в modals/ — здесь только делегирование.

import { renderUserCard as renderMemberCard } from './components/cards/user_card.js?v=cabrio18'
import { renderCarCard } from './components/cards/car_card.js?v=cabrio18'

export function openMemberModal(member){
  if (window.CabrioModals && typeof window.CabrioModals.openUserModal === 'function') {
    return window.CabrioModals.openUserModal(member)
  }
}

export function openCarModal(car){
  if (window.CabrioModals && typeof window.CabrioModals.openCarModal === 'function') {
    return window.CabrioModals.openCarModal(car)
  }
}

window.CabrioComponents = { renderMemberCard, openMemberModal, renderCarCard, openCarModal }
