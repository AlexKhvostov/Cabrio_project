<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('Автомобили — CabrioRide'); ?>
    <link rel="stylesheet" href="/app/frontend/assets/css/styles.css" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
  </head>
  <body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    <?php include __DIR__ . '/../components/nav.php'; ?>
    <main class="page">
    <?php $FILTERS_CONFIG = [
      'searchPlaceholder' => 'Поиск по марке, модели, номеру...',
      'filters' => [
        ['id' => 'statusFilter', 'placeholder' => 'Все статусы']
      ]
    ]; include __DIR__ . '/../components/filters.php'; ?>
      <div id="cars" class="cars-grid">Загрузка...</div>
    </main>
    <script type="module">
      import '/app/frontend/assets/js/app.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/app.js'); ?>'
      import '/app/frontend/assets/js/components.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/components.js'); ?>'
      import '/app/frontend/assets/js/modals/car_modal.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/modals/car_modal.js'); ?>'
      const carsEl = document.getElementById('cars')
      const { renderCarCard } = window.CabrioComponents
      const { openCarModal } = window.CabrioModals
      const searchInput = document.getElementById('filters-search')
      const statusSelect = document.getElementById('statusFilter')
      let list = []
      CabrioAPI.apiGet('/api/cars').then(json=>{
        if(!json || json.__httpStatus===401 || json.__httpStatus===403 || json.success===false){
          carsEl.textContent = 'Недостаточно прав'
          return
        }
        list = json.data||[]
        // Статусы
        const statuses = Array.from(new Set(list.map(c=>c.status?.name || c.status?.code).filter(Boolean))).sort()
        if(statusSelect){
          statuses.forEach(s=>{ const opt=document.createElement('option'); opt.value=s; opt.textContent=s; statusSelect.appendChild(opt) })
          // По умолчанию выбираем "Активен"/"active", если есть
          const defaultActive = statuses.find(s=>String(s).toLowerCase()==='активен') || statuses.find(s=>String(s).toLowerCase()==='active')
          if(defaultActive){ statusSelect.value = defaultActive }
          statusSelect.addEventListener('change', render)
        }
        if(searchInput){ searchInput.addEventListener('input', render) }
        render()
        carsEl.addEventListener('click', (e)=>{
          const card = e.target.closest('.car-card-compact')
          if(!card) return
          const id = Number(card.getAttribute('data-id'))
          const car = list.find(x=>Number(x.id)===id)
          if(car) openCarModal(car)
        })
      }).catch(()=>{ carsEl.textContent='Ошибка загрузки' })

      function render(){
        const q = (searchInput?.value||'').toLowerCase().trim()
        const statusValue = statusSelect?.value||''
        const filtered = list.filter(c=>{
          const matchesQ = !q || (
            (c.brand?.name||'').toLowerCase().includes(q) ||
            (c.model||'').toLowerCase().includes(q) ||
            (c.reg_number||'').toLowerCase().includes(q) ||
            (c.owner?.username||'').toLowerCase().includes(q)
          )
          const statusText = (c.status?.name || c.status?.code || '').toString()
          const matchesStatus = !statusValue || statusText === statusValue
          return matchesQ && matchesStatus
        })
        carsEl.innerHTML = filtered.map(c=>renderCarCard(c)).join('') || 'Пусто'
      }
    </script>
  </body>
  </html>

