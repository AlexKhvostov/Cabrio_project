<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('Участники — CabrioRide'); ?>
    <link rel="stylesheet" href="/app/frontend/assets/css/styles.css" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
  </head>
  <body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    <?php include __DIR__ . '/../components/nav.php'; ?>
    <main class="page">
    <?php $FILTERS_CONFIG = [
      'searchPlaceholder' => 'Поиск по имени или нику...',
      'filters' => [
        ['id' => 'cityFilter', 'placeholder' => 'Все города']
      ]
    ]; include __DIR__ . '/../components/filters.php'; ?>
<div id="usersAccessBanner" class="card" style="margin-bottom:12px">
  <h3 style="margin:6px 0 8px 0">Доступ к списку участников</h3>
  <p style="margin:0 0 6px 0; color:#ccc">Список доступен полноправным участникам клуба.</p>
  <ul style="margin:0 0 6px 18px; color:#ccc">
    <li>Добавьте свой автомобиль в приложении</li>
    <li>Познакомьтесь лично на встрече</li>
    <li>Получите роль <b>member</b> или выше</li>
  </ul>
</div>
<div id="users">Загрузка...</div>

    </main>
    <script type="module">
      import '/app/frontend/assets/js/app.js'
      import '/app/frontend/assets/js/components.js'
      import '/app/frontend/assets/js/modals/user_modal.js?v=2'
      const usersEl = document.getElementById('users')
      const usersAccessBanner = document.getElementById('usersAccessBanner')
      const searchInput = document.getElementById('filters-search')
      const citySelect = document.getElementById('cityFilter')
      const { renderMemberCard } = window.CabrioComponents
      const { openUserModal } = window.CabrioModals
      let list = []
      CabrioAPI.apiGet('/api/users').then(json=>{
        if(!json || json.__httpStatus===401 || json.__httpStatus===403 || json.success===false){
          // Недостаточно прав — показываем пояснение и не рендерим список
          usersEl.innerHTML = ''
          if (usersAccessBanner) usersAccessBanner.style.display = ''
          return
        }
        if (usersAccessBanner) usersAccessBanner.style.display = 'none'
        list = json.data||[]
        // Заполним города
        const cities = Array.from(new Set(list.map(u=>u.city).filter(Boolean))).sort()
        if(citySelect){
          cities.forEach(c=>{ const opt=document.createElement('option'); opt.value=c; opt.textContent=c; citySelect.appendChild(opt) })
          citySelect.addEventListener('change', render)
        }
        if(searchInput){ searchInput.addEventListener('input', render) }
        render()
        usersEl.addEventListener('click', (e)=>{
          const card = e.target.closest('.member-card')
          if(!card) return
          const id = Number(card.getAttribute('data-id'))
          const m = list.find(x=>Number(x.id)===id)
          if(m) openUserModal(m)
        })
      }).catch(()=>{ usersEl.textContent='Ошибка загрузки' })

      function render(){
        const q = (searchInput?.value||'').toLowerCase().trim()
        const city = citySelect?.value||''
        const filtered = list.filter(u=>{
          const matchesQ = !q || (
            (u.first_name||'').toLowerCase().includes(q) ||
            (u.last_name||'').toLowerCase().includes(q) ||
            (u.username||'').toLowerCase().includes(q)
          )
          const matchesCity = !city || (u.city===city)
          return matchesQ && matchesCity
        })
        usersEl.innerHTML = filtered.map(u=>renderMemberCard(u)).join('') || 'Пусто'
      }
    </script>
  </body>
  </html>

