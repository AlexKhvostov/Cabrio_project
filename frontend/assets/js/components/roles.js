// Русские названия ролей для экрана. В API по-прежнему коды: guest, user, member…
export const ROLE_LABELS = {
  external: 'Внешний',
  guest: 'Гость',
  user: 'Пользователь',
  member: 'Участник',
  moderator: 'Модератор',
  admin: 'Администратор',
}

export function roleCodeOf(role){
  if (role && typeof role === 'object') {
    return String(role.code || '').toLowerCase()
  }
  return String(role || '').toLowerCase()
}

export function roleLabelRu(role){
  const code = roleCodeOf(role)
  if (ROLE_LABELS[code]) return ROLE_LABELS[code]
  const name = (role && typeof role === 'object') ? String(role.name || '').trim() : ''
  return name || ''
}

/** Лестница ролей снизу вверх. Свайп двигает только на одну ступень. */
export const ROLE_LADDER = ['external', 'guest', 'user', 'member', 'moderator', 'admin']

export function isStaffRole(role){
  const code = roleCodeOf(role)
  return code === 'moderator' || code === 'admin'
}

function roleIndex(role){
  return ROLE_LADDER.indexOf(roleCodeOf(role))
}

/** Роли, которые этот человек имеет право назначить: строго ниже своей. */
export function assignableRoles(actorRole){
  const actorI = roleIndex(actorRole)
  if (actorI < 0) return []
  return ROLE_LADDER.filter((_, i) => i < actorI)
}

/**
 * Соседние роли для свайпа.
 * Нельзя трогать себя, равных и выше. Повысить можно только ниже собственной роли.
 */
export function neighborRoles(targetRole, actorRole){
  const empty = { up: null, down: null }
  const targetI = roleIndex(targetRole)
  const actorI = roleIndex(actorRole)
  if (targetI < 0 || actorI < 0) return empty
  if (actorI < ROLE_LADDER.indexOf('moderator')) return empty
  if (targetI >= actorI) return empty
  const upCode = ROLE_LADDER[targetI + 1] || null
  const downCode = ROLE_LADDER[targetI - 1] || null
  return {
    up: upCode && ROLE_LADDER.indexOf(upCode) < actorI ? upCode : null,
    down: downCode || null,
  }
}
