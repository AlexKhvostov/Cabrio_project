// Simple map implementation (Yandex Maps) with GPS tracking and "follow me" toggle

let map;
let selfPlacemark = null;
let isTracking = false;
let followMe = false; // appears when GPS is on; default true on start
let watchId = null;
let wakeLock = null;
let userLocation = null;
let heartbeatTid = null;
let lastSentAt = 0;
let mapBehaviors = null; // to manage dragging/scroll zoom if needed
let activeUsers = [];
let usersRefreshTimer = null;

const UPDATE_SEC = Number(window.MAP_UPD_SEC || 30);
const UPDATE_MOVING_SEC = Number(window.MAP_UPD_MOVING_SEC || 10);
const MOVE_THRESHOLD_M = Number(window.MAP_MOVE_THRESHOLD_M || 25);

let lastLocation = null;
let lastSentCoords = null;
let isMoving = false;

// Роль текущего пользователя (кешируем после первого запроса)
let currentUserRole = null;
const ROLE_ORDER = ['external','guest','new','registered','member','moderator','admin'];

function showAccessDenied(message){
	try{
		if (window.Telegram && window.Telegram.WebApp && typeof window.Telegram.WebApp.showAlert === 'function') {
			window.Telegram.WebApp.showAlert(message);
			return;
		}
	} catch {}
	try { alert(message); return; } catch {}
	// Фолбэк: простая видимая плашка поверх карты
	try{
		const overlay = document.createElement('div');
		overlay.style.cssText = 'position:fixed;inset:0;z-index:2147483647;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;padding:24px;';
		const box = document.createElement('div');
		box.style.cssText = 'max-width:420px;background:#1a1a1a;color:#fff;border-radius:12px;padding:16px 18px;font-size:16px;line-height:1.4;text-align:center;box-shadow:0 8px 30px rgba(0,0,0,0.35)';
		box.textContent = message;
		overlay.appendChild(box);
		document.body.appendChild(overlay);
		overlay.addEventListener('click', ()=>{ try{ overlay.remove(); }catch{} });
	}catch{}
}

async function fetchCurrentUserRole(){
	if (currentUserRole) return currentUserRole;
	try{
		if (!window.CabrioAPI?.apiGet) { currentUserRole = 'guest'; return currentUserRole; }
		const me = await window.CabrioAPI.apiGet('/api/users/profile');
		const d = me?.data || me || {};
		let code = null;
		if (typeof d.role === 'string') code = d.role;
		else if (d.role && typeof d.role.code === 'string') code = d.role.code;
		else if (d.user && typeof d.user.role === 'string') code = d.user.role;
		else if (d.user && d.user.role && typeof d.user.role.code === 'string') code = d.user.role.code;
		else if (typeof d.role_code === 'string') code = d.role_code;
		currentUserRole = code ? String(code).toLowerCase() : 'guest';
		return currentUserRole;
	}catch{ currentUserRole = 'guest'; return currentUserRole; }
}

function isRoleAtLeast(role, minRole){
    try{
        const a = ROLE_ORDER.indexOf((role||'').toLowerCase());
        const b = ROLE_ORDER.indexOf((minRole||'').toLowerCase());
        if (b === -1) return false; // некорректный minRole — перестрахуемся
        if (a === -1) return false; // роль не распознали — считаем ниже порога
        return a >= b;
    }catch{ return false }
}

async function ensureAccessAllowed(showDialog){
    try{
        const role = await fetchCurrentUserRole();
        const allowed = isRoleAtLeast(role, 'member');
        if (!allowed && showDialog) {
            showAccessDenied('Карта доступна только подтверждённым участникам клуба (роль "Участник" и выше).');
        }
        return !!allowed;
    }catch{ return false }
}

function distanceMeters(lat1, lon1, lat2, lon2) {
	try{
		const R = 6371000; // meters
		const toRad = (d)=>d*Math.PI/180;
		const dLat = toRad(lat2-lat1);
		const dLon = toRad(lon2-lon1);
		const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1))*Math.cos(toRad(lat2))*Math.sin(dLon/2)**2;
		return 2*R*Math.asin(Math.sqrt(a));
	}catch{return 0}
}

function initMap() {
  map = new ymaps.Map('map', {
		center: [53.902284, 27.561831], // Minsk, Belarus
		zoom: 12,
    controls: ['zoomControl', 'fullscreenControl']
  });
	mapBehaviors = map.behaviors;
    // На мобильном лучше отключить скролл зум колесом/жестом страницы
    try { map.behaviors.disable('scrollZoom'); } catch {}
}

function acquireWakeLock() {
	if (!('wakeLock' in navigator)) return Promise.resolve();
	return navigator.wakeLock.request('screen')
		.then(lock => {
			wakeLock = lock;
			lock.addEventListener('release', () => { wakeLock = null; });
		})
		.catch(() => {});
}

function releaseWakeLock() {
	try { wakeLock && wakeLock.release && wakeLock.release(); } catch {}
	wakeLock = null;
}

function ensureSelfPlacemark() {
	if (!map || !window.ymaps) return;
	if (selfPlacemark) return;
	selfPlacemark = new ymaps.Placemark([0, 0], {}, { preset: 'islands#blueCircleDotIcon' });
	map.geoObjects.add(selfPlacemark);
}

function updateSelfMarker(lat, lon) {
	if (!map || !window.ymaps) return;
	ensureSelfPlacemark();
	try { selfPlacemark.geometry.setCoordinates([lat, lon]); } catch { try { selfPlacemark = new ymaps.Placemark([lat, lon]); map.geoObjects.add(selfPlacemark); } catch {} }
}

async function sendLocation(minIntervalSec = UPDATE_SEC) {
	if (!window.CabrioAPI?.apiPost) return;
	if (!userLocation) return;
	const now = Date.now();
	if (now - lastSentAt < Math.max(0, Number(minIntervalSec)||0) * 1000) return;
	lastSentAt = now;
	try {
		await window.CabrioAPI.apiPost('/api/user-locations', {
			latitude: userLocation.latitude,
			longitude: userLocation.longitude,
			accuracy: userLocation.accuracy
		});
		lastSentCoords = { lat: userLocation.latitude, lon: userLocation.longitude };
  } catch {}
}

function startHeartbeat() {
	stopHeartbeat();
	heartbeatTid = setInterval(sendLocation, Math.max(UPDATE_SEC, 5) * 1000);
}

function stopHeartbeat() {
	if (heartbeatTid) { try { clearInterval(heartbeatTid); } catch {} heartbeatTid = null; }
}

async function startTracking() {
  const allowed = await ensureAccessAllowed(true);
  if (!allowed) return;
  if (isTracking) return;
	if (!navigator.geolocation) return;
    isTracking = true;
	setGpsBtnState(true);
	followMe = true;
	showFollowBtn(true);
	acquireWakeLock();
    startHeartbeat();
	if (watchId !== null) { try { navigator.geolocation.clearWatch(watchId); } catch {} watchId = null; }
    watchId = navigator.geolocation.watchPosition(
		(position) => {
			userLocation = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy
          };
			updateSelfMarker(userLocation.latitude, userLocation.longitude);
			if (followMe && map) {
				try { map.setCenter([userLocation.latitude, userLocation.longitude], 17, { duration: 0, checkZoomRange: true }); } catch {}
			}
			// Определяем движение и отправляем при проходе порога, но не чаще заданного интервала для движения
			if (lastLocation) {
				const distFix = distanceMeters(lastLocation.latitude, lastLocation.longitude, userLocation.latitude, userLocation.longitude);
				const prevMoving = isMoving;
				isMoving = distFix >= MOVE_THRESHOLD_M;
				if (prevMoving !== isMoving && typeof window.__setUsersMoving === 'function') {
					try { window.__setUsersMoving(isMoving); } catch {}
				}
			} else {
				// первая фиксация
				isMoving = false;
			}
			// Немедленная первая отправка
            if (lastSentAt === 0) {
                sendLocation(0);
                try { if (typeof window.__refreshUsersNow === 'function') window.__refreshUsersNow(); } catch {}
            }
			// Отправка при преодолении порога расстояния
			if (lastSentCoords) {
				const distSinceSent = distanceMeters(lastSentCoords.lat, lastSentCoords.lon, userLocation.latitude, userLocation.longitude);
                if (distSinceSent >= MOVE_THRESHOLD_M) {
                    sendLocation(UPDATE_MOVING_SEC);
                    try { if (typeof window.__refreshUsersNow === 'function') window.__refreshUsersNow(); } catch {}
                }
			} else {
				// если координаты ещё не отправлялись, привяжем к текущей
				lastSentCoords = { lat: userLocation.latitude, lon: userLocation.longitude };
			}
			lastLocation = { latitude: userLocation.latitude, longitude: userLocation.longitude };
		},
		() => { stopTracking(); },
      { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
    );
}

function stopTracking() {
	if (!isTracking) return;
	isTracking = false;
	setGpsBtnState(false);
	showFollowBtn(false);
  if (watchId !== null) { try { navigator.geolocation.clearWatch(watchId); } catch {} watchId = null; }
  stopHeartbeat();
  releaseWakeLock();
}

function setGpsBtnState(active) {
	const btn = document.getElementById('sendLocationBtn');
	if (!btn) return;
	btn.setAttribute('aria-pressed', active ? 'true' : 'false');
}

function showFollowBtn(visible) {
	let bar = document.querySelector('.map-fab-bar');
	if (!bar) return;
	let btn = document.getElementById('followMeBtn');
	if (visible) {
		if (!btn) {
			btn = document.createElement('button');
			btn.id = 'followMeBtn';
			btn.type = 'button';
			btn.className = 'fab';
			btn.title = 'Следить за мной';
			btn.setAttribute('aria-pressed', 'true');
			btn.innerHTML = '<span class="fab-icon" aria-hidden="true">➤</span>';
			btn.addEventListener('click', () => {
				followMe = !followMe;
				btn.setAttribute('aria-pressed', followMe ? 'true' : 'false');
				// При включении сразу центрируем и ставим зум 17 одним вызовом
				if (followMe && userLocation && map) {
					try { map.setCenter([userLocation.latitude, userLocation.longitude], 17, { duration: 0, checkZoomRange: true }); } catch {}
				}
			});
			bar.appendChild(btn);
		} else {
			btn.style.display = '';
			btn.setAttribute('aria-pressed', 'true');
			followMe = true;
		}
	} else if (btn) {
		btn.style.display = 'none';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  if (window.ymaps && typeof ymaps.ready === 'function') {
    ymaps.ready(() => { try { initMap(); } catch {} });
  }
	const gpsBtn = document.getElementById('sendLocationBtn');
	if (gpsBtn) {
		gpsBtn.addEventListener('click', async () => {
			const ok = await ensureAccessAllowed(true);
			if (!ok) return;
			if (isTracking) stopTracking(); else startTracking();
		});
		setGpsBtnState(false);
	}
	// WakeLock safety on visibility changes
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
			if (isTracking) acquireWakeLock();
    } else {
      releaseWakeLock();
    }
  });

  // People panel (Online - X)
  setupOnlinePanel();
});

// expose minimal API for debugging
window.MapFunctions = { startTracking, stopTracking };

// ===================== Online panel =====================
function setupOnlinePanel(){
  const toggle = document.getElementById('peopleToggle');
  const list = document.getElementById('peopleList');
  if (!toggle || !list) return;

  const idleSec = Number(window.MAP_USERS_REFRESH_IDLE_SEC || 60);
  const movingSec = Number(window.MAP_USERS_REFRESH_MOVING_SEC || idleSec);

  toggle.textContent = 'Online - 0';
  toggle.addEventListener('click', async ()=>{
    const ok = await ensureAccessAllowed(true);
    if (!ok) return;
    if (list.hasAttribute('hidden')) {
      renderPeopleList(list);
      list.removeAttribute('hidden');
    } else {
      list.setAttribute('hidden', '');
    }
  });

  const doRefresh = async () => {
    try{
      const ok = await ensureAccessAllowed(false);
      if (!ok) return;
      const res = await (window.CabrioAPI?.apiGet ? window.CabrioAPI.apiGet('/api/user-locations') : Promise.resolve(null));
      if (res && res.success && Array.isArray(res.data)) {
        activeUsers = res.data.slice().sort((a,b)=>{
          const ta = Date.parse(String(a.updated_at).replace(' ', 'T')+'Z') || 0;
          const tb = Date.parse(String(b.updated_at).replace(' ', 'T')+'Z') || 0;
          return tb - ta;
        });
        toggle.textContent = `Online - ${activeUsers.length}`;
        // если список открыт — перерисуем
        if (!list.hasAttribute('hidden')) renderPeopleList(list);
      }
    }catch{}
  };

  function restartUsersInterval(movingFlag){
    if (usersRefreshTimer) { try { clearInterval(usersRefreshTimer); } catch {} usersRefreshTimer = null; }
    const sec = Math.max(10, movingFlag ? movingSec : idleSec);
    usersRefreshTimer = setInterval(()=>{ if (document.visibilityState==='visible') doRefresh(); }, sec*1000);
  }

  // Экспорт внутрь окна, чтобы таймер можно было переключать при изменении движения и принудительно обновлять список
  try { window.__setUsersMoving = function(flag){ restartUsersInterval(!!flag); }; } catch {}
  try { window.__refreshUsersNow = function(){ return doRefresh(); }; } catch {}

  // начальная загрузка и периодическое обновление
  doRefresh();
  restartUsersInterval(false);
}

function renderPeopleList(container){
  const items = activeUsers.map(loc => {
    const name = (loc.user?.first_name) || 'Участник';
    const username = loc.user?.username ? `@${loc.user.username}` : '';
    const avatar = (loc.user?.photo?.mini) || (loc.user?.photo?.urls?.mini) || (loc.user?.photo_url) || '';
    const ts = Date.parse(String(loc.updated_at).replace(' ', 'T')+'Z') || 0;
    const mins = Math.max(0, Math.floor((Date.now() - ts)/60000));
    const rel = mins < 1 ? 'только что' : (mins < 60 ? (mins + 'м') : (Math.floor(mins/60) + 'ч' + (mins % 60 ? ' ' + (mins % 60) + 'м' : '')));
    return `
      <div class="people-item" data-user-id="${loc.user_id}">
        <div class="pi-avatar">${avatar ? `<img src="${avatar}" alt="avatar" onerror="this.style.display='none'"/>` : ''}</div>
        <div class="pi-main">
          <div class="pi-name">${name}</div>
          ${username ? `<div class=\"pi-username\">${username}</div>` : ''}
        </div>
        <div class="pi-time">${rel}</div>
      </div>`;
  }).join('');
  container.innerHTML = items || '<div class="people-item"><div class="pi-main"><div class="pi-name">Никого онлайн</div></div></div>';
}

