// map.js — Яндекс.Карты и управление координатами пользователей

let map;
let userLocation;

// Конфигурация
const UPDATE_INTERVAL_SEC = Number(window.MAP_UPD_SEC || 30);
const UPDATE_MOVING_SEC = Number(window.MAP_UPD_MOVING_SEC || 10);
const MOVE_THRESHOLD_M = Number(window.MAP_MOVE_THRESHOLD_M || 25);
const USERS_REFRESH_IDLE_SEC = Number(window.MAP_USERS_REFRESH_IDLE_SEC || 60);
const USERS_REFRESH_MOVING_SEC = Number(window.MAP_USERS_REFRESH_MOVING_SEC || 20);

// Состояние трекинга
let isTracking = false;
let watchId = null;
let lastSentAt = 0;
let lastReceivedAt = 0;

// Состояние
let isMoving = false;
let lastCoord = null;
let usersRefreshTid = null;

// Screen Wake Lock — чтобы экран не гас при активной передаче
let wakeLock = null;
async function acquireWakeLock() {
  if (!('wakeLock' in navigator)) return;
  try {
    wakeLock = await navigator.wakeLock.request('screen');
    wakeLock.addEventListener('release', () => {
      wakeLock = null;
      if (isTracking && document.visibilityState === 'visible') {
        acquireWakeLock().catch(()=>{});
      }
    });
  } catch (_) {}
}
function releaseWakeLock() {
  try { wakeLock && wakeLock.release && wakeLock.release(); } catch(_) {}
  wakeLock = null;
}

// Слои для объектов
let selfLayer = null;
let usersLayer = null;

// Кэш машин пользователей для карточек
let userIdToCars = {};
let activeUsersById = {}; // кэш активных пользователей для аватаров (включая себя)

// Управление автоцентрированием карты (чтобы не сбрасывать масштаб пользователю)
let hasAutoCentered = false;
let userMovedMap = false;

let lastUsersRefreshAt = 0;
let refreshTimerTid = null;
let heartbeatTid = null; // таймер периодической отправки

// Счётчик секунд с момента последней отправки координат
let lastCoordSentAt = 0;
function setCoordTimerLabel(){
  const el = document.getElementById('toggleTime');
  if (!el) return;
  if (!lastCoordSentAt) { el.textContent = '(—s)'; return; }
  const secs = Math.max(0, Math.floor((Date.now() - lastCoordSentAt)/1000));
  el.textContent = `(${secs}s)`;
}
function markCoordSentNow(){
  lastCoordSentAt = Date.now();
  setCoordTimerLabel();
}

function setRefreshTimerLabel() {
  const el = document.getElementById('refreshTimer');
  if (!el) return;
  if (!lastUsersRefreshAt) { el.textContent = '(—s)'; return; }
  const secs = Math.max(0, Math.floor((Date.now() - lastUsersRefreshAt)/1000));
  el.textContent = `(${secs}s)`;
}

function markUsersRefreshNow(){
  lastUsersRefreshAt = Date.now();
  setRefreshTimerLabel();
}

async function refreshUsersManually(){
  // Обнуляем счётчик сразу по нажатию
  markUsersRefreshNow();
  await loadActiveUsers();
}

function getCurrentTelegramUserId() {
  try {
    return window.Telegram?.WebApp?.initDataUnsafe?.user?.id || null;
  } catch { return null; }
}

function initMap() {
  map = new ymaps.Map('map', {
    center: [53.902284, 27.561831], // Минск
    zoom: 10,
    controls: ['zoomControl', 'fullscreenControl']
  });
  selfLayer = new ymaps.GeoObjectCollection();
  usersLayer = new ymaps.GeoObjectCollection();
  map.geoObjects.add(usersLayer);
  map.geoObjects.add(selfLayer);
  map.geoObjects.add(new ymaps.TrafficLayer());

  // Если пользователь начал взаимодействовать с картой — больше не центрируем автоматически
  try {
    map.events.add('actionbegin', () => { userMovedMap = true; });
  } catch {}

  // Стартовая загрузка данных
  loadCarsForCards().catch(()=>{});
  loadActiveUsers().catch(()=>{});
  // Автообновление меток других пользователей с динамическим интервалом
  restartUsersAutoRefresh();
}

async function loadCarsForCards() {
  // Загружаем список машин и строим индекс по владельцу
  if (!window.CabrioAPI?.apiGet) return;
  try {
    const res = await window.CabrioAPI.apiGet('/api/cars');
    if (res && res.success && Array.isArray(res.data)) {
      const mapCars = {};
      for (const car of res.data) {
        const ownerId = car?.owner?.id || car?.owner_user_id || null;
        if (!ownerId) continue;
        if (!mapCars[ownerId]) mapCars[ownerId] = [];
        const brand = car?.brand?.name || car?.car_brand?.name || car?.car_brand_name || '';
        const model = car?.model || '';
        mapCars[ownerId].push({ brand, model });
      }
      userIdToCars = mapCars;
    }
  } catch {}
}

function formatRelativeTime(isoOrSql) {
  const t = new Date(isoOrSql.replace(' ', 'T'));
  const diffMs = Date.now() - t.getTime();
  const mins = Math.max(0, Math.floor(diffMs / 60000));
  if (mins < 1) return 'только что';
  if (mins < 60) return `${mins}м`;
  const h = Math.floor(mins / 60);
  const m = mins % 60;
  return m ? `${h}ч ${m}м` : `${h}ч`;
}

// Layout с HTML содержимым (аватар) с фолбэком
function createAvatarLayout(imageUrl) {
  const safeUrl = imageUrl ? String(imageUrl) : '';
  // onerror скрывает img и показывает fallback
  return ymaps.templateLayoutFactory.createClass(
    `<div class="avatar-marker">
       <div class="avatar-wrap">
         ${safeUrl ? `<img src="${safeUrl}" alt="avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"/>` : ''}
         <div class="avatar-fallback" style="display:${safeUrl ? 'none' : 'flex'}">👤</div>
       </div>
     </div>`
  );
}

function buildBalloonHtml(user) {
  const name = user?.first_name || 'Участник';
  const username = user?.username ? `@${user.username}` : '';
  const cars = userIdToCars[user.user_id] || [];
  const carsHtml = cars.length ? (
    `<ul class="member-cars">${cars.slice(0,3).map(c => `<li>${(c.brand||'').trim()} ${(c.model||'').trim()}</li>`).join('')}</ul>`
  ) : '<div class="member-empty">Нет машин</div>';
  return `
    <div class="member-balloon">
      <div class="member-line">
        <div class="member-name">${name}</div>
        ${username ? `<div class="member-username">${username}</div>` : ''}
      </div>
      ${carsHtml}
    </div>
  `;
}

// Предзагрузка изображения с таймаутом
function preloadImage(url, timeoutMs = 2000){
  return new Promise((resolve)=>{
    if (!url) return resolve(false);
    try{
      const img = new Image();
      let done = false;
      const finish = (ok)=>{ if (done) return; done = true; resolve(ok); };
      const t = setTimeout(()=>{ finish(false); }, timeoutMs);
      img.onload = ()=>{ clearTimeout(t); finish(true); };
      img.onerror = ()=>{ clearTimeout(t); finish(false); };
      img.src = url;
    }catch{ resolve(false); }
  });
}

// Преобразование загруженного <img> в dataURL (если разрешено CORS), иначе возвращаем null
function imgToDataUrl(img, size = 36){
  try{
    if (!img || !img.complete || !img.naturalWidth) return null;
    const canvas = document.createElement('canvas');
    canvas.width = size; canvas.height = size;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(img, 0, 0, size, size);
    return canvas.toDataURL('image/png');
  }catch{ return null; }
}

async function loadActiveUsers() {
  markUsersRefreshNow();
  if (!window.CabrioAPI?.apiGet) return;
  const meId = getCurrentTelegramUserId();
  try {
    const res = await window.CabrioAPI.apiGet('/api/user-locations');
    if (!(res && res.success && Array.isArray(res.data))) { return; }

    if (typeof res.live_time_minutes !== 'undefined') {
      const v = Number(res.live_time_minutes);
      if (isFinite(v) && v > 0) LIVE_TIME_MIN = v;
    }

    activeUsersById = {};
    for (const loc of res.data) {
      const mini = (loc.user?.photo?.mini) || (loc.user?.photo?.urls?.mini) || (loc.user?.photo_url) || (loc.user?.photo?.fallback) || '';
      activeUsersById[String(loc.user_id)] = { mini, user: loc.user, dataUrl: null };
    }

    const toggleBtn = document.getElementById('peopleToggle');
    if (toggleBtn) toggleBtn.textContent = `На карте — ${res.data.length}`;

    const list = document.getElementById('peopleList');
    if (list) {
      const items = res.data.map(loc => {
        const name = (loc.user?.first_name) || 'Участник';
        const username = loc.user?.username ? `@${loc.user.username}` : '';
        const timeLabel = formatRelativeTime(loc.updated_at);
        const avatar = (loc.user?.photo?.mini) || (loc.user?.photo?.urls?.mini) || (loc.user?.photo_url) || (loc.user?.photo?.fallback) || '';
        return `
          <div class="people-item" data-user-id="${loc.user_id}">
            <div class="pi-avatar">${avatar ? `<img src="${avatar}" alt="avatar" onerror="this.style.display='none'"/>` : ''}</div>
            <div class="pi-main">
              <div class="pi-name">${name}</div>
              ${username ? `<div class="pi-username">${username}</div>` : ''}
            </div>
            <div class="pi-time">${timeLabel}</div>
          </div>`;
      }).join('');
      list.innerHTML = items;

      // Сразу после вставки — попробуем получить dataURL из реально загруженных картинок
      for (const loc of res.data){
        const el = list.querySelector(`.people-item[data-user-id="${loc.user_id}"] img`);
        const dataUrl = imgToDataUrl(el, 36);
        if (dataUrl){ activeUsersById[String(loc.user_id)].dataUrl = dataUrl; }
      }
    }

    usersLayer.removeAll();

    for (const loc of res.data) {
      const lat = Number(loc.latitude), lon = Number(loc.longitude);
      if (!isFinite(lat) || !isFinite(lon)) continue;

      let opacity = 1;
      try {
        const t = new Date(String(loc.updated_at).replace(' ', 'T'));
        const ageMin = Math.max(0, (Date.now() - t.getTime()) / 60000);
        if (LIVE_TIME_MIN > 0) opacity = Math.max(0.2, Math.min(1, 1 - ageMin / LIVE_TIME_MIN));
      } catch { opacity = 1; }

      const cache = activeUsersById[String(loc.user_id)] || {};
      const useUrl = cache.dataUrl || cache.mini || '';
      const contentLayout = createAvatarLayout(useUrl);
      const placemark = new ymaps.Placemark([lat, lon], {
        balloonContent: buildBalloonHtml({ user_id: loc.user_id, first_name: loc.user?.first_name, username: loc.user?.username })
      }, {
        iconLayout: 'default#imageWithContent',
        iconImageHref: 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==',
        iconImageSize: [1,1],
        iconContentLayout: contentLayout,
        iconContentOffset: [0, 0],
        hideIconOnBalloonOpen: false,
        balloonShadow: false,
        balloonPanelMaxMapArea: 0,
        opacity
      });
      usersLayer.add(placemark);
    }
  } catch (e) { /* игнор */ }
}

// Отправка координат на сервер (через общий API-клиент с Telegram-заголовками)
async function sendLocationToServer(coords) {
  if (!window.CabrioAPI?.apiPost) throw new Error('API клиент не инициализирован');
  const result = await window.CabrioAPI.apiPost('/api/user-locations', {
    latitude: coords.latitude,
    longitude: coords.longitude,
    accuracy: coords.accuracy
  });
  if (!result || result.success !== true) {
    throw new Error(result?.error || 'Сервер вернул ошибку');
  }
  return result;
}

function setTrackingButtonState(active) {
  const btn = document.getElementById('sendLocationBtn');
  if (!btn) return;
  btn.setAttribute('aria-pressed', active ? 'true' : 'false');
}

function setErrorTop(message){
  const el = document.getElementById('mapError');
  if (!el) return;
  if (!message){ el.hidden = true; el.textContent = ''; return; }
  el.textContent = message;
  el.hidden = false;
}

function setStatus(message, kind = 'info') {
  // перенаправляем ошибки в верхний блок
  if (kind === 'error') { setErrorTop(message); return; }
  const statusEl = document.getElementById('locationStatus');
  if (statusEl) {
    statusEl.textContent = message;
    statusEl.className = `location-status ${kind === 'success' ? 'success' : kind === 'error' ? 'error' : ''}`;
  }
}

function addUserMarker(coords) {
  if (!map || !window.ymaps) return;
  try {
    selfLayer.removeAll();
    const meId = getCurrentTelegramUserId();
    const miniUrl = activeUsersById[String(meId)]?.mini || '';
    const contentLayout = createAvatarLayout(miniUrl);
    const placemark = new ymaps.Placemark([coords.latitude, coords.longitude], {}, {
      iconLayout: 'default#imageWithContent',
      iconImageHref: 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==',
      iconImageSize: [1,1],
      iconContentLayout: contentLayout,
      iconContentOffset: [0, 0]
    });
    selfLayer.add(placemark);
    // Автоцентрируем только один раз и только пока пользователь сам не двигал карту
    if (!hasAutoCentered && !userMovedMap) {
      try { map.setCenter([coords.latitude, coords.longitude]); } catch {}
      hasAutoCentered = true;
    }
  } catch {}
}

function haversineMeters(a, b){
  const R = 6371000;
  const toRad = (deg)=>deg*Math.PI/180;
  const dLat = toRad(b.lat - a.lat);
  const dLon = toRad(b.lon - a.lon);
  const lat1 = toRad(a.lat);
  const lat2 = toRad(b.lat);
  const h = Math.sin(dLat/2)**2 + Math.cos(lat1)*Math.cos(lat2)*Math.sin(dLon/2)**2;
  return 2*R*Math.asin(Math.sqrt(h));
}

function updateMovingState(newCoords){
  if (!lastCoord){ lastCoord = { lat: newCoords.latitude, lon: newCoords.longitude }; isMoving = false; return; }
  const dist = haversineMeters(lastCoord, { lat: newCoords.latitude, lon: newCoords.longitude });
  if (dist >= MOVE_THRESHOLD_M) {
    isMoving = true;
    lastCoord = { lat: newCoords.latitude, lon: newCoords.longitude };
  } else {
    isMoving = false;
  }
  restartHeartbeat();
  restartUsersAutoRefresh();
}

function restartUsersAutoRefresh(){
  if (usersRefreshTid) { try { clearInterval(usersRefreshTid); } catch{} usersRefreshTid=null; }
  const sec = isMoving ? USERS_REFRESH_MOVING_SEC : USERS_REFRESH_IDLE_SEC;
  usersRefreshTid = setInterval(() => { if (document.visibilityState==='visible') loadActiveUsers().catch(()=>{}); }, sec*1000);
}

function startHeartbeat(){ stopHeartbeat(); heartbeatTid = setInterval(heartbeatTick, (isMoving? UPDATE_MOVING_SEC : UPDATE_INTERVAL_SEC) * 1000); }
function stopHeartbeat(){ if (heartbeatTid) { try { clearInterval(heartbeatTid); } catch{} heartbeatTid = null; } }
function restartHeartbeat(){ if (!isTracking) return; startHeartbeat(); }
async function heartbeatTick(){
  try{
    if (!isTracking) return;
    if (userLocation && (Date.now() - lastSentAt >= (isMoving? UPDATE_MOVING_SEC : UPDATE_INTERVAL_SEC) * 1000)){
      await sendLocationToServer(userLocation);
      lastSentAt = Date.now();
      markCoordSentNow();
      loadActiveUsers().catch(()=>{});
    }
  }catch{}
}

function startTracking() {
  if (isTracking) return;
  if (!navigator.geolocation) { setStatus('Геолокация не поддерживается', 'error'); return; }
  try {
    isTracking = true;
    setTrackingButtonState(true);
    setErrorTop('');
    lastSentAt = 0;
    lastReceivedAt = 0;
    lastCoordSentAt = 0;
    setCoordTimerLabel();
    // Разрешим одно автоцентрирование при новом запуске, если пользователь не трогал карту
    hasAutoCentered = false;

    acquireWakeLock().catch(()=>{});

    // Запускаем heartbeat, чтобы слать даже без движения
    startHeartbeat();

    watchId = navigator.geolocation.watchPosition(
      async (position) => {
        try {
          const coords = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy
          };
          userLocation = coords;
          lastReceivedAt = Date.now();
          updateMovingState(coords);

          addUserMarker(coords);

          const now = Date.now();
          if (now - lastSentAt >= UPDATE_INTERVAL_SEC * 1000) {
            await sendLocationToServer(coords);
            lastSentAt = now;
            markCoordSentNow();
            loadActiveUsers().catch(()=>{});
          }
        } catch (err) {
          setStatus(err?.message || 'Ошибка отправки координат', 'error');
          stopTracking();
        }
      },
      (error) => {
        let msg = 'Ошибка геолокации';
        if (error.code === 1) msg = 'Доступ к геолокации запрещен';
        else if (error.code === 2) msg = 'Не удалось определить местоположение';
        else if (error.code === 3) msg = 'Превышено время ожидания';
        setStatus(msg, 'error');
        stopTracking();
      },
      { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
    );
  } catch (e) {
    setStatus(e?.message || 'Не удалось запустить авто‑отправку', 'error');
    stopTracking();
  }
}

function stopTracking() {
  if (watchId !== null) { try { navigator.geolocation.clearWatch(watchId); } catch {} watchId = null; }
  stopHeartbeat();
  isTracking = false;
  setTrackingButtonState(false);
  lastCoordSentAt = 0;
  setCoordTimerLabel();
  releaseWakeLock();
}

// Обработчик раскрытия панели
function setupPeoplePanel() {
  const toggleBtn = document.getElementById('peopleToggle');
  const list = document.getElementById('peopleList');
  if (!toggleBtn || !list) return;
  toggleBtn.addEventListener('click', () => {
    const expanded = toggleBtn.getAttribute('aria-expanded') === 'true';
    const next = !expanded;
    toggleBtn.setAttribute('aria-expanded', next ? 'true' : 'false');
    if (next) {
      list.hidden = false;
    } else {
      list.hidden = true;
    }
  });
}

function showToast(msg){
  const el = document.getElementById('mapToast');
  if (!el) return;
  el.textContent = msg;
  el.hidden = false;
}
function hideToast(){
  const el = document.getElementById('mapToast');
  if (!el) return;
  el.hidden = true;
}

// Прогресс‑границы для FAB: 0..1 в минутном окне
function setFabProgress(btnId, progress){
  const btn = document.getElementById(btnId);
  if (!btn) return;
  const deg = Math.max(0, Math.min(1, progress || 0)) * 360;
  const ring = btn.querySelector('.fab-progress::after');
  // querySelector на псевдоэлемент не работает — используем CSS var и вращение всей кнопки-псевдоэлемента через style
  btn.style.setProperty('--fab-progress-deg', `${deg}deg`);
}

function updateFabProgressRings(){
  // GPS
  const gpsBtn = document.getElementById('sendLocationBtn');
  if (gpsBtn){
    const secs = lastCoordSentAt ? Math.min(60, Math.max(0, Math.floor((Date.now() - lastCoordSentAt)/1000))) : 0;
    const deg = (secs / 60) * 360; // от -90deg задаётся в CSS
    gpsBtn.style.setProperty('--fab-progress-deg', `${deg}deg`);
  }
  // Refresh
  const refBtn = document.getElementById('refreshUsersBtn');
  if (refBtn){
    const secs = lastUsersRefreshAt ? Math.min(60, Math.max(0, Math.floor((Date.now() - lastUsersRefreshAt)/1000))) : 0;
    const deg = (secs / 60) * 360;
    refBtn.style.setProperty('--fab-progress-deg', `${deg}deg`);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  try {
    const tg = window.Telegram?.WebApp;
    tg?.ready?.();
    tg?.expand?.();
    tg?.disableVerticalSwipes?.();
  } catch {}

  const btn = document.getElementById('sendLocationBtn');
  if (btn) {
    btn.addEventListener('click', () => {
      if (isTracking) stopTracking(); else startTracking();
    });
    setTrackingButtonState(false);
  }

  if (window.ymaps && typeof ymaps.ready === 'function') {
    ymaps.ready(() => { try { initMap(); } catch {} });
  }

  setupPeoplePanel();

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
      if (isTracking) acquireWakeLock().catch(()=>{});
    } else {
      releaseWakeLock();
    }
  });

  window.addEventListener('pagehide', () => { releaseWakeLock(); });

  const refreshBtn = document.getElementById('refreshUsersBtn');
  if (refreshBtn) refreshBtn.addEventListener('click', refreshUsersManually);
  // запускаем общий секундный таймер — обновляем оба счётчика
  refreshTimerTid = setInterval(() => {
    setRefreshTimerLabel();
    setCoordTimerLabel();
    updateFabProgressRings();
  }, 1000);

  const recBtn = document.getElementById('audioRecBtn');
  if (recBtn){
    const onDown = ()=> showToast('Функция записи аудио в разработке');
    const onUp = ()=> hideToast();
    recBtn.addEventListener('mousedown', onDown);
    recBtn.addEventListener('touchstart', onDown, { passive: true });
    recBtn.addEventListener('mouseup', onUp);
    recBtn.addEventListener('mouseleave', onUp);
    recBtn.addEventListener('touchend', onUp);
    recBtn.addEventListener('touchcancel', onUp);
  }
});

window.MapFunctions = { initMap, sendLocationToServer, addUserMarker, startTracking, stopTracking };
