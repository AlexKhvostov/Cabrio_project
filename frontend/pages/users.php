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
        ['id' => 'roleFilter', 'placeholder' => 'Все роли']
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
      import '/app/frontend/assets/js/app.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/app.js'); ?>'
      import '/app/frontend/assets/js/components.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/components.js'); ?>'
      import '/app/frontend/assets/js/modals/user_modal.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/modals/user_modal.js'); ?>'
      const usersEl = document.getElementById('users')
      const usersAccessBanner = document.getElementById('usersAccessBanner')
      const searchInput = document.getElementById('filters-search')
      const roleSelect = document.getElementById('roleFilter')
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
        // Заполним роли
        const ROLE_ORDER = ['external','guest','user','member','moderator','admin']
        const roleOptions = [
          { value: 'user_plus', label: 'Пользователь и выше' },
          { value: 'all', label: 'Все роли' },
          { value: 'admin', label: 'Администраторы' },
          { value: 'moderator', label: 'Модераторы' },
          { value: 'member', label: 'Участники' },
          { value: 'user', label: 'Пользователи' },
          { value: 'guest', label: 'Гости' },
          { value: 'external', label: 'Внешние' }
        ]
        if (roleSelect){
          // Очистим и добавим варианты
          roleSelect.innerHTML = ''
          roleOptions.forEach(r=>{ const opt=document.createElement('option'); opt.value=r.value; opt.textContent=r.label; roleSelect.appendChild(opt) })
          // Значение по умолчанию — пользователь и выше
          roleSelect.value = 'user_plus'
          roleSelect.addEventListener('change', render)
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
        const qRaw = (searchInput?.value||'').toLowerCase().trim()
        const qUser = qRaw.replace(/^@+/, '')
        const roleFilter = roleSelect?.value||''
        const getRoleCode = (u)=>{
          if (u.role && typeof u.role.code === 'string') return String(u.role.code).toLowerCase()
          if (typeof u.role === 'string') return String(u.role).toLowerCase()
          if (u.role_id) {
            const map = {1:'external',2:'guest',3:'user',4:'member',5:'moderator',6:'admin'}
            return map[Number(u.role_id)]||'guest'
          }
          return 'guest'
        }
        const ROLE_ORDER_SORT = ['admin','moderator','member','user','guest','external']
        const filtered = list.filter(u=>{
          const first = (u.first_name_app || u.first_name_tg || u.first_name || '').toLowerCase()
          const last  = (u.last_name_app  || u.last_name_tg  || u.last_name  || '').toLowerCase()
          const full  = (first + ' ' + last).trim()
          const usern = (u.username || '').toLowerCase()
          const matchesQ = !qRaw || full.includes(qRaw) || first.includes(qRaw) || last.includes(qRaw) || usern.includes(qUser)
          const code = getRoleCode(u)
          let matchesRole = true
          if (roleFilter === 'user_plus') {
            matchesRole = ['user','member','moderator','admin'].includes(code)
          } else if (roleFilter && roleFilter !== 'all') {
            matchesRole = (code === roleFilter)
          }
          return matchesQ && matchesRole
        })
        // Сортировка по ролям: admin → moderator → member → user → guest → external
        filtered.sort((a,b)=>{
          const ra = getRoleCode(a), rb = getRoleCode(b)
          return ROLE_ORDER_SORT.indexOf(ra) - ROLE_ORDER_SORT.indexOf(rb)
        })
        usersEl.innerHTML = filtered.map(u=>renderMemberCard(u)).join('') || 'Пусто'
      }
    </script>
  </body>
  </html>

