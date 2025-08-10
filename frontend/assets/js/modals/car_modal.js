// car_modal.js — модальное окно автомобиля

function escapeHtml(str){
  return String(str||'').replace(/[&<>"]|'/g, s=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]))
}

export function openCarModal(car){
  const overlay = document.createElement('div')
  overlay.className = 'modal-overlay'
  const title = `${car.brand?.name||''} ${car.model||''}`.trim()
  const ownerAvatar = car.owner?.photo?.url || ''
  const rawPhotos = Array.isArray(car.photos) && car.photos.length ? car.photos : (car.photo?.url ? [car.photo] : [])
  const photos = rawPhotos.map(p=> typeof p === 'string' ? { url: p } : { url: p.url })

  const fieldLabels = {
    brand: 'Марка',
    model: 'Модель',
    color: 'Цвет',
    year: 'Год выпуска',
    roof_type: 'Тип крыши',
    engine_power: 'Мощность',
    engine_volume: 'Объем двигателя',
    vin: 'VIN',
    description: 'Описание',
    notes: 'Заметки',
    created_at: 'Создано',
    updated_at: 'Обновлено'
  }

  // Хелпер рендера пары
  const renderItem = (key, value) => {
    const label = fieldLabels[key] || key
    const val = (value===null || value===undefined || value==='') ? 'не задано' : String(value)
    return `<div class="detail-item-compact"><span class="detail-label">${escapeHtml(label)}</span><span class="detail-value">${escapeHtml(val)}</span></div>`
  }

  // Группы полей (технические id исключены)
  const brandName = car.brand?.name || ''
  const groupBasic = [ ['brand', brandName], ['model', car.model], ['color', car.color], ['year', car.year], ['roof_type', car.roof_type] ]
  const groupEngine = [ ['engine_power', car.engine_power], ['engine_volume', car.engine_volume] ]
  const groupReg = [ ['vin', car.vin] ]
  const groupOther = [ ['description', car.description], ['notes', car.notes] ]
  const groupMeta = [ ['created_at', car.created_at], ['updated_at', car.updated_at] ]

  const basicHtml = groupBasic.map(([k,v])=>renderItem(k,v)).join('')
  const engineHtml = groupEngine.map(([k,v])=>renderItem(k,v)).join('')
  const regHtml = groupReg.map(([k,v])=>renderItem(k,v)).join('')
  const otherHtml = groupOther.map(([k,v])=>renderItem(k,v)).join('')
  const metaHtml = groupMeta.map(([k,v])=>renderItem(k,v)).join('')

  const statusLabel = car.status?.name || car.status?.code || '—'

  overlay.innerHTML = `
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">${escapeHtml(title)}</div>
        <button class="modal-close" aria-label="close">×</button>
      </div>
      <div class="modal-body">
        ${photos.length ? `<div class=\"main-photo-compact\"><img src=\"${escapeHtml(photos[0].url)}\" class=\"main-image\" alt=\"${escapeHtml(title)}\"/></div>` : ''}
        ${photos.length > 1 ? `<div class=\"photo-thumbnails-compact\">${photos.map((p,i)=>`<img src=\\\"${escapeHtml(p.url)}\\\" class=\\\"thumbnail-compact${i===0?' active':''}\\\" data-index=\\\"${i}\\\" alt=\\\"thumb\\\"/>`).join('')}</div>` : ''}

        <div class="car-info-section-compact">
          <div class="car-header-compact">
            <div class="car-title-compact">
              <h3 class="car-name-compact">${escapeHtml(title)}</h3>
              <span class="status-badge status-primary">${escapeHtml(statusLabel)}</span>
            </div>
            ${(car.reg_number!==undefined && car.reg_number!==null && String(car.reg_number)!=='') ? `
              <div class=\"car-number-compact\"><span class=\"detail-label\">Номер:</span> <span class=\"detail-value\">${escapeHtml(String(car.reg_number))}</span></div>
            ` : ''}
          </div>
        </div>

        ${car.owner ? (()=>{ 
          const o = car.owner || {}
          const first = o.first_name_app || o.first_name || o.first_name_tg || ''
          const last = o.last_name_app || o.last_name || o.last_name_tg || ''
          const normalized = { ...o, first_name: first, last_name: last }
          return `<div class=\\\"member-card-container\\\">${window.CabrioComponents.renderMemberCard(normalized, { showCars: false })}</div>`
        })() : ''}

        <div class="detail-grid-compact">${basicHtml}</div>
        ${engineHtml ? `<div class=\"detail-section-compact\"><h4>Двигатель</h4><div class=\"detail-grid-compact\">${engineHtml}</div></div>` : ''}
        <div class="detail-section-compact">
          <h4>Регистрация</h4>
          <div class="detail-grid-compact">
            ${renderItem('vin', car.vin)}
          </div>
        </div>
        ${otherHtml ? `<div class=\"detail-section-compact\"><h4>Прочее</h4><div class=\"detail-grid-compact\">${otherHtml}</div></div>` : ''}
        ${metaHtml ? `<div class=\"detail-section-compact\"><h4>Метаданные</h4><div class=\"detail-grid-compact\">${metaHtml}</div></div>` : ''}
      </div>
    </div>`
  function close(){ overlay.remove() }
  overlay.addEventListener('click', (e)=>{ if(e.target===overlay) close() })
  overlay.querySelector('.modal-close')?.addEventListener('click', close)
  document.body.appendChild(overlay)

  if (photos.length > 1) {
    const main = overlay.querySelector('.main-photo-compact .main-image')
    overlay.querySelectorAll('.thumbnail-compact').forEach(el=>{
      el.addEventListener('click', ()=>{
        overlay.querySelectorAll('.thumbnail-compact').forEach(t=>t.classList.remove('active'))
        el.classList.add('active')
        const idx = Number(el.getAttribute('data-index'))
        if (main && photos[idx]) main.setAttribute('src', photos[idx].url)
      })
    })
  }

  // Клик по карточке владельца внутри модалки → открыть модалку профиля
  if (car.owner) {
    overlay.querySelector('.member-card-container')?.addEventListener('click', ()=>{
      if (window.CabrioModals?.openUserModal) window.CabrioModals.openUserModal(car.owner)
    })
  }
}

// Глобально для удобства
window.CabrioModals = window.CabrioModals || {}
window.CabrioModals.openCarModal = openCarModal


