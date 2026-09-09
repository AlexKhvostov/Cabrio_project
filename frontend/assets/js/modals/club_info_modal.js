// Модалки справки. Текст лежит в #clubInfoStore (собирает PHP).

const TITLES = {
  roles: 'Роли и доступы',
  sections: 'Разделы',
}

function closeClubInfoModal(){
  document.getElementById('clubInfoOverlay')?.remove()
}

export function openClubInfoModal(kind){
  const key = kind === 'sections' ? 'sections' : 'roles'
  const src = document.querySelector('#clubInfoStore [data-club-info="' + key + '"]')
  if (!src) return
  closeClubInfoModal()
  const overlay = document.createElement('div')
  overlay.id = 'clubInfoOverlay'
  overlay.className = 'modal-overlay club-info-overlay'
  overlay.innerHTML = `<div class="modal-content modal-compact sheet-card">
    <div class="modal-header">
      <div class="modal-title"></div>
      <div class="sheet-actions"><button type="button" class="modal-close" aria-label="Закрыть">×</button></div>
    </div>
    <div class="modal-body"></div>
  </div>`
  overlay.querySelector('.modal-title').textContent = TITLES[key]
  overlay.querySelector('.modal-body').innerHTML = src.innerHTML
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeClubInfoModal()
  })
  overlay.querySelector('.modal-close')?.addEventListener('click', closeClubInfoModal)
  document.body.appendChild(overlay)
}

export function bindClubInfoTiles(root = document){
  root.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-open-club-info]')
    if (!btn) return
    e.preventDefault()
    openClubInfoModal(btn.getAttribute('data-open-club-info'))
  })
}
