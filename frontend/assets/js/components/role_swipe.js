// Свайп роли в списке участников. Жест виден только модератору и администратору.

import { roleCodeOf, roleLabelRu, isStaffRole, neighborRoles } from './roles.js?v=ru2'

const OPEN_PX = 48
const MAX_PX = 92

function personTitle(member){
  const first = member.first_name_app || member.first_name || member.first_name_tg || ''
  const last = member.last_name_app || member.last_name || member.last_name_tg || ''
  return (`${first} ${last}`).trim() || 'Без имени'
}

function askConfirm(text){
  return new Promise((resolve) => {
    const tg = window.Telegram?.WebApp
    if (tg && typeof tg.showConfirm === 'function') {
      tg.showConfirm(text, (ok) => resolve(!!ok))
      return
    }
    resolve(window.confirm(text))
  })
}

function closeAll(root){
  root.querySelectorAll('.member-swipe.is-up, .member-swipe.is-down').forEach((row) => {
    row.classList.remove('is-up', 'is-down')
    const front = row.querySelector('.member-swipe-front')
    if (front) front.style.transform = ''
  })
}

function actionButton(kind, nextCode){
  const isUp = kind === 'up'
  const verb = isUp ? 'Повысить' : 'Понизить'
  return `<button class="member-swipe-action ${isUp ? 'is-up' : 'is-down'}" type="button" data-role-swipe="${kind}" data-next-role="${nextCode}">
    <span class="member-swipe-ico" aria-hidden="true">${isUp ? '↑' : '↓'}</span>
    <span class="member-swipe-txt"><em>${verb}</em><strong>${roleLabelRu(nextCode)}</strong></span>
  </button>`
}

/** Оборачивает плитку: без кнопок, если свайп этому человеку недоступен. */
export function wrapMemberSwipe(cardHtml, member, actor){
  const actorId = Number(actor?.id || 0)
  const actorRole = roleCodeOf(actor?.role)
  if (!isStaffRole(actorRole) || !actorId || Number(member.id) === actorId) {
    return cardHtml
  }
  const next = neighborRoles(member.role, actorRole)
  if (!next.up && !next.down) return cardHtml
  const downBtn = next.down ? actionButton('down', next.down) : '<span></span>'
  const upBtn = next.up ? actionButton('up', next.up) : '<span></span>'
  return `<div class="member-swipe" data-user-id="${member.id}" data-can-up="${next.up ? '1' : '0'}" data-can-down="${next.down ? '1' : '0'}">
    <div class="member-swipe-back">${downBtn}${upBtn}</div>
    <div class="member-swipe-front">${cardHtml}</div>
  </div>`
}

export function bindMemberRoleSwipe(root, options = {}){
  if (!root || root.dataset.roleSwipeBound === '1') return
  root.dataset.roleSwipeBound = '1'
  const onChanged = options.onChanged
  const listOf = () => (typeof options.members === 'function' ? options.members() : (options.members || []))

  let startX = 0
  let startY = 0
  let lastDx = 0
  let active = null
  let tracking = false
  let moved = false
  let swallowClick = false

  const byId = (id) => listOf().find((u) => String(u.id) === String(id))

  const applyOpen = (row, dir) => {
    closeAll(root)
    row.classList.add(dir === 'up' ? 'is-up' : 'is-down')
    const front = row.querySelector('.member-swipe-front')
    if (front) front.style.transform = ''
  }

  const resetFront = (row) => {
    const front = row.querySelector('.member-swipe-front')
    if (front) front.style.transform = ''
  }

  root.addEventListener('pointerdown', (e) => {
    if (e.pointerType === 'mouse' && e.button !== 0) return
    if (e.target.closest('.member-car-stack-item, .member-car-thumb, .member-swipe-action')) return
    const front = e.target.closest('.member-swipe-front')
    const row = front?.closest('.member-swipe')
    if (!row) return
    active = row
    tracking = true
    moved = false
    startX = e.clientX
    startY = e.clientY
    lastDx = 0
    try { front.setPointerCapture(e.pointerId) } catch {}
  })

  root.addEventListener('pointermove', (e) => {
    if (!tracking || !active) return
    const dx = e.clientX - startX
    const dy = e.clientY - startY
    if (!moved && Math.abs(dx) < 8 && Math.abs(dy) < 8) return
    if (!moved && Math.abs(dy) > Math.abs(dx)) {
      tracking = false
      resetFront(active)
      active = null
      return
    }
    moved = true
    const canUp = active.getAttribute('data-can-up') === '1'
    const canDown = active.getAttribute('data-can-down') === '1'
    let x = dx
    if (x < 0 && !canUp) x = 0
    if (x > 0 && !canDown) x = 0
    x = Math.max(-MAX_PX, Math.min(MAX_PX, x))
    lastDx = x
    const front = active.querySelector('.member-swipe-front')
    if (front) front.style.transform = `translateX(${x}px)`
    e.preventDefault()
  }, { passive: false })

  const endDrag = () => {
    if (!active) { tracking = false; moved = false; lastDx = 0; return }
    const row = active
    const dx = lastDx
    tracking = false
    active = null
    lastDx = 0
    const didMove = moved
    if (moved && dx <= -OPEN_PX && row.getAttribute('data-can-up') === '1') {
      applyOpen(row, 'up')
    } else if (moved && dx >= OPEN_PX && row.getAttribute('data-can-down') === '1') {
      applyOpen(row, 'down')
    } else {
      row.classList.remove('is-up', 'is-down')
      resetFront(row)
    }
    if (didMove) swallowClick = true
    moved = false
  }

  root.addEventListener('click', (e) => {
    if (!swallowClick) return
    e.preventDefault()
    e.stopPropagation()
    swallowClick = false
  }, true)

  root.addEventListener('pointerup', endDrag)
  root.addEventListener('pointercancel', endDrag)

  root.addEventListener('click', async (e) => {
    const action = e.target.closest('[data-role-swipe]')
    if (action) {
      e.preventDefault()
      e.stopPropagation()
      const row = action.closest('.member-swipe')
      const member = byId(row?.getAttribute('data-user-id'))
      const nextRole = action.getAttribute('data-next-role')
      if (!member || !nextRole) return
      const kind = action.getAttribute('data-role-swipe')
      const verb = kind === 'up' ? 'Повысить' : 'Понизить'
      const from = roleLabelRu(member.role)
      const to = roleLabelRu(nextRole)
      const ok = await askConfirm(`${verb} ${personTitle(member)}?\n${from} → ${to}`)
      if (!ok) return
      const res = await window.CabrioAPI.apiPost(`/api/users/${member.id}/role`, { role: nextRole })
      if (!res || res.success === false) {
        alert((res && res.error && res.error.message) || 'Не удалось сменить роль')
        return
      }
      closeAll(root)
      if (typeof onChanged === 'function') onChanged(res.data || { ...member, role: { code: nextRole, name: to } })
      return
    }
    const openRow = e.target.closest('.member-swipe.is-up, .member-swipe.is-down')
    if (openRow && !e.target.closest('.member-swipe-action')) {
      e.preventDefault()
      e.stopPropagation()
      openRow.classList.remove('is-up', 'is-down')
      resetFront(openRow)
    }
  })

  document.querySelector('.page')?.addEventListener('scroll', () => closeAll(root), { passive: true })
}
