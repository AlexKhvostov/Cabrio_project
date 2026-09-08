// Всплывающая подсказка у кнопки i: поверх экрана, не вылезает за край.

export function hintBubble(text, { label = 'Подсказка', btnClass = 'hint-i' } = {}) {
  const esc = (s) => String(s || '').replace(/[&<>"']/g, ch => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[ch]))
  const safeText = esc(text)
  const safeLabel = esc(label)
  return `<span class="hint-wrap">
    <button type="button" class="${btnClass}" aria-label="${safeLabel}" aria-expanded="false">i</button>
    <span class="hint-pop" hidden>${safeText}</span>
  </span>`
}

function placeHintPop(btn, pop) {
  // Вешаем на body и считаем координаты — иначе обрезает overflow шапки/модалки
  document.body.appendChild(pop)
  pop.hidden = false
  const pad = 12
  const vw = Math.max(window.innerWidth || 0, 320)
  const vh = Math.max(window.innerHeight || 0, 240)
  const maxW = Math.min(300, vw - pad * 2)
  pop.style.position = 'fixed'
  pop.style.right = 'auto'
  pop.style.width = maxW + 'px'
  pop.style.maxWidth = maxW + 'px'
  pop.style.left = pad + 'px'
  pop.style.top = '0px'
  pop.style.zIndex = '1400'
  const br = btn.getBoundingClientRect()
  const ph = pop.offsetHeight || 120
  const pw = pop.offsetWidth || maxW
  let left = br.left + br.width / 2 - pw / 2
  left = Math.max(pad, Math.min(left, vw - pw - pad))
  let top = br.bottom + 8
  if (top + ph > vh - pad) {
    top = Math.max(pad, br.top - ph - 8)
  }
  pop.style.left = Math.round(left) + 'px'
  pop.style.top = Math.round(top) + 'px'
}

function parkHintPop(pop) {
  const wrap = pop._hintWrap
  pop.hidden = true
  pop.style.left = ''
  pop.style.top = ''
  pop.style.right = ''
  pop.style.width = ''
  pop.style.maxWidth = ''
  pop.style.position = ''
  pop.style.zIndex = ''
  if (wrap && pop.parentElement !== wrap) wrap.appendChild(pop)
}

export function bindHintPops(root = document) {
  if (!root || root.__hintPopsBound) return
  root.__hintPopsBound = true
  const closeAll = () => {
    document.querySelectorAll('.hint-pop').forEach((p) => parkHintPop(p))
    root.querySelectorAll('.hint-i[aria-expanded], .app-hint-btn[aria-expanded]').forEach((b) => {
      b.setAttribute('aria-expanded', 'false')
    })
  }
  root.addEventListener('click', (e) => {
    const btn = e.target.closest('.hint-i, .app-hint-btn')
    if (btn && root.contains(btn)) {
      e.preventDefault()
      e.stopPropagation()
      const wrap = btn.closest('.hint-wrap')
      const pop = wrap && wrap.querySelector('.hint-pop')
      const willOpen = !!(pop && pop.hidden)
      closeAll()
      if (pop && willOpen) {
        pop._hintWrap = wrap
        placeHintPop(btn, pop)
        btn.setAttribute('aria-expanded', 'true')
      }
      return
    }
    if (!e.target.closest('.hint-pop')) closeAll()
  })
  window.addEventListener('resize', closeAll)
}
