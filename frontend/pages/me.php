<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('Профиль — CabrioRide'); ?>
    <link rel="stylesheet" href="/app/frontend/assets/css/styles.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/styles.css'); ?>" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
  </head>
  <body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    <?php include __DIR__ . '/../components/nav.php'; ?>
    <main class="page">
      <section id="profile" class="card profile-card" style="display:none;">
        <div class="profile-topbar">
          <div class="topbar-title" aria-hidden="true">Профиль</div>
          <div class="profile-actions">
            <button type="button" id="editBtn" class="btn-warning">Редактировать</button>
            <button type="button" id="saveBtn" class="btn-success" style="display:none;">Сохранить</button>
          </div>
        </div>

        <div class="profile-avatar-row" id="profileAvatarRow">
          <div class="profile-avatar" id="profileAvatar"></div>
          <div class="profile-info-stack">
            <div class="profile-line">
              <div class="profile-name" id="profileName">&nbsp;</div>
            </div>
            <div class="profile-subline">
              <div class="profile-username" id="profileUsername">&nbsp;</div>
              <div id="role" class="role-badge" aria-live="polite"></div>
            </div>
            <div class="profile-upload-line">
              <button type="button" id="userUploadBtn" class="btn-secondary" style="display:none;">Загрузить фото</button>
            </div>
          </div>
        </div>
        <form id="profile-form" class="profile-form" autocomplete="off">
          <div class="form-grid">
            <label class="form-field">
              <span>Имя (приложение)</span>
              <input type="text" id="first_name_app" />
            </label>
            <label class="form-field">
              <span>Фамилия (приложение)</span>
              <input type="text" id="last_name_app" />
            </label>
            <label class="form-field">
              <span>Город</span>
              <input type="text" id="city" />
            </label>
            <label class="form-field">
              <span>Страна</span>
              <input type="text" id="country" />
            </label>
            <label class="form-field">
              <span>Email</span>
              <input type="email" id="email" inputmode="email" />
            </label>
            <label class="form-field">
              <span>Телефон</span>
              <input type="tel" id="phone" inputmode="tel" />
            </label>
            <label class="form-field form-col">
              <span>О себе</span>
              <textarea id="about" rows="3"></textarea>
            </label>
            
          </div>
          
        </form>

        <div class="divider"></div>

        <div class="tg-block card">
          <h3 style="margin:0 0 6px 0">Данные Telegram</h3>
          <div class="form-grid readonly">
            <label class="form-field">
              <span>Telegram ID</span>
              <input type="text" id="tg_id" disabled />
            </label>
            <label class="form-field">
              <span>Username</span>
              <input type="text" id="tg_username" disabled />
            </label>
            <label class="form-field">
              <span>Имя (TG)</span>
              <input type="text" id="first_name_tg" disabled />
            </label>
            <label class="form-field">
              <span>Фамилия (TG)</span>
              <input type="text" id="last_name_tg" disabled />
            </label>
          </div>
        </div>
      </section>

      <section id="my-cars" style="margin-top:12px; display:none;">
        <h3 style="margin:0 0 6px 0">Мои автомобили</h3>
        <div id="cars-list" class="cars-grid"></div>
      </section>

      <details id="debug-wrap" style="margin-top:12px;">
        <summary>Диагностика (временная)</summary>
        <pre id="debug" style="white-space:pre-wrap;background:rgba(255,255,255,0.05);padding:8px;border-radius:8px;border:1px solid var(--border-color);"></pre>
      </details>
    </main>
    <?php include __DIR__ . '/../components/footer.php'; ?>
    <script type="module">
      import '/app/frontend/assets/js/app.js'
      import '/app/frontend/assets/js/components.js'
      import { initProfilePage } from '/app/frontend/assets/js/components/profile_view.js'
      // Локальный клиент на случай, если глобальный не инициализировался
      const API_ROOT = (window.__API_URL || (window.location.origin + '/app/backend')).replace(/\/$/, '')
      const buildTelegramHeaders = () => {
        const headers = {}
        try {
          const tg = window.Telegram?.WebApp
          const u = tg?.initDataUnsafe?.user || {}
          if (u.id) headers['X-Telegram-User-Id'] = String(u.id)
          if (u.first_name) headers['X-Telegram-First-Name'] = String(u.first_name)
          if (u.last_name) headers['X-Telegram-Last-Name'] = String(u.last_name)
          if (u.username) headers['X-Telegram-Username'] = String(u.username)
          if (tg?.initData) headers['X-Telegram-Init-Data'] = String(tg.initData)
        } catch {}
        return headers
      }
      const callApi = async (route) => {
        if (window.CabrioAPI?.apiGet) return window.CabrioAPI.apiGet(route)
        const url = `${API_ROOT}/routes/api.php?route=${encodeURIComponent(route)}`
        const res = await fetch(url, { headers: buildTelegramHeaders() })
        const data = await res.json().catch(()=>null)
        if (res.status === 401 || res.status === 403) return { __httpStatus: res.status, ...(data||{}) }
        return data
      }
      const meEl = document.getElementById('me')
      const profile = document.getElementById('profile')
      const roleEl = document.getElementById('role')
      const carsSection = document.getElementById('my-cars')
      const carsListEl = document.getElementById('cars-list')
      const dbg = document.getElementById('debug')
      const tg = window.Telegram?.WebApp
      const u = tg?.initDataUnsafe?.user
      const clientInfo = {
        telegram_present: !!u,
        telegram_user: u ? { id: u.id, username: u.username, first_name: u.first_name, last_name: u.last_name } : null,
      }
      // Инициализация страницы профиля
      initProfilePage()
    </script>
  </body>
  </html>

