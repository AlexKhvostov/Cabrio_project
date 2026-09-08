// Карта клуба: видеть своих. Кнопка ⏻ — «показывать меня». Без GPS карту всё равно можно смотреть.

let map;
let selfPinPlacemark = null;
let usersLayer = null;
let isTracking = false;
let followMe = false;
let watchId = null;
let wakeLock = null;
let userLocation = null;
let heartbeatTid = null;
let lastSentAt = 0;
let activeUsers = [];
let usersRefreshTimer = null;
let currentUserId = null;
let lastLocation = null;
let lastSentCoords = null;
let isMoving = false;
let refreshUsersNow = null;
let setUsersMoving = null;
let toastTimer = null;

const UPDATE_SEC = Number(window.MAP_UPD_SEC || 30);
const UPDATE_MOVING_SEC = Number(window.MAP_UPD_MOVING_SEC || 10);
const MOVE_THRESHOLD_M = Number(window.MAP_MOVE_THRESHOLD_M || 25);
const MINSK = [53.902284, 27.561831];

function phUserUrl() {
	const scripts = document.getElementsByTagName('script')
	for (let i = 0; i < scripts.length; i++) {
		const src = scripts[i].src || ''
		if (src.includes('/assets/js/map.js')) {
			return src.replace(/\/assets\/js\/map\.js.*$/, '/assets/img/ph-user.png?v=2')
		}
	}
	const front = String(window.__FRONT_URL || '/app/frontend').replace(/\/$/, '')
	return front + '/assets/img/ph-user.png?v=2'
}

function escapeHtml(s) {
	return String(s ?? '')
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;');
}

function distanceMeters(lat1, lon1, lat2, lon2) {
	try {
		const R = 6371000;
		const toRad = (d) => d * Math.PI / 180;
		const dLat = toRad(lat2 - lat1);
		const dLon = toRad(lon2 - lon1);
		const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lon2)) * Math.sin(dLon / 2) ** 2;
		return 2 * R * Math.asin(Math.sqrt(a));
	} catch { return 0; }
}

function ageMinutesFromSql(s) {
	const ts = Date.parse(String(s || '').replace(' ', 'T') + 'Z') || 0;
	return Math.max(0, Math.floor((Date.now() - ts) / 60000));
}

function relativeTimeLabel(mins) {
	if (mins < 1) return 'только что';
	if (mins < 60) return mins + 'м';
	const h = Math.floor(mins / 60);
	const m = mins % 60;
	return h + 'ч' + (m ? ' ' + m + 'м' : '');
}

function showMapError(message) {
	const el = document.getElementById('mapError');
	if (!el) return;
	el.textContent = message;
	el.hidden = false;
}

function hideMapError() {
	const el = document.getElementById('mapError');
	if (!el) return;
	el.hidden = true;
	el.textContent = '';
}

function showToast(message, ms) {
	const el = document.getElementById('mapToast');
	if (!el) return;
	el.textContent = message;
	el.hidden = false;
	if (toastTimer) clearTimeout(toastTimer);
	toastTimer = setTimeout(() => { el.hidden = true; }, Number(ms) || 2500);
}

function setShareHintVisible(visible) {
	const el = document.getElementById('mapShareHint');
	if (!el) return;
	el.hidden = !visible;
}

function setGpsBtnState(active) {
	const btn = document.getElementById('sendLocationBtn');
	if (!btn) return;
	btn.setAttribute('aria-pressed', active ? 'true' : 'false');
}

function showFollowBtn(visible) {
	const btn = document.getElementById('followMeBtn');
	if (!btn) return;
	if (visible) {
		btn.hidden = false;
		followMe = true;
		btn.setAttribute('aria-pressed', 'true');
	} else {
		btn.hidden = true;
		followMe = false;
		btn.setAttribute('aria-pressed', 'false');
	}
}

function focusPerson(lat, lon, userId) {
	const id = Number(userId);
	if (!id) return;
	if (focusPerson._lock && Date.now() - focusPerson._lock.at < 450 && focusPerson._lock.id === id) return;
	focusPerson._lock = { at: Date.now(), id: id };
	followMe = false;
	const followBtn = document.getElementById('followMeBtn');
	if (followBtn && !followBtn.hidden) followBtn.setAttribute('aria-pressed', 'false');
	if (map && isFinite(lat) && isFinite(lon)) {
		try { map.setCenter([lat, lon], Math.max(map.getZoom(), 14), { duration: 300, checkZoomRange: true }); } catch {}
	}
	if (window.CabrioNav && typeof window.CabrioNav.openUser === 'function') {
		window.CabrioNav.openUser(id);
		return;
	}
	showToast('Карточка сейчас не открылась, попробуйте ещё раз', 1800);
}

function markerOpts(contentLayout, zIndex) {
	// Зона нажатия = видимая метка 36×48. Раньше было 1×1 — тап по фото не попадал в метку.
	return {
		iconLayout: 'default#imageWithContent',
		iconImageHref: 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==',
		iconImageSize: [36, 48],
		iconImageOffset: [-18, -48],
		iconContentLayout: contentLayout,
		iconContentOffset: [0, 0],
		zIndex: zIndex || 5000,
		hasBalloon: false,
		openBalloonOnClick: false,
		hideIconOnBalloonOpen: false
	};
}

function initMap() {
	// Свои кнопки и карточка человека — без штатных плашек Яндекса по центру
	map = new ymaps.Map('map', {
		center: MINSK,
		zoom: 12,
		controls: []
	}, {
		suppressMapOpenBlock: true,
		yandexMapDisablePoiInteractivity: true
	});
	try { map.behaviors.disable('scrollZoom'); } catch {}
	try {
		usersLayer = new ymaps.GeoObjectCollection();
		map.geoObjects.add(usersLayer);
	} catch {}
}

function acquireWakeLock() {
	if (!('wakeLock' in navigator)) return Promise.resolve();
	return navigator.wakeLock.request('screen')
		.then((lock) => {
			wakeLock = lock;
			lock.addEventListener('release', () => { wakeLock = null; });
		})
		.catch(() => {});
}

function releaseWakeLock() {
	try { wakeLock && wakeLock.release && wakeLock.release(); } catch {}
	wakeLock = null;
}

function ensureSelfPinPlacemark() {
	if (!map || !window.ymaps) return;
	if (selfPinPlacemark) return;
	const contentLayout = createAvatarLayout((window.getSelfAvatarUrl ? window.getSelfAvatarUrl() : ''), 1, true, false, '#3b82f6');
	selfPinPlacemark = new ymaps.Placemark([0, 0], { userId: currentUserId || 0, self: true }, markerOpts(contentLayout, 10000));
	selfPinPlacemark.events.add('click', () => {
		const coords = selfPinPlacemark.geometry.getCoordinates() || [];
		const id = currentUserId || selfPinPlacemark.properties.get('userId');
		if (id) focusPerson(coords[0], coords[1], id);
		else showToast('Это вы', 1600);
	});
	map.geoObjects.add(selfPinPlacemark);
}

function hideSelfMarker() {
	if (!map || !selfPinPlacemark) return;
	try { map.geoObjects.remove(selfPinPlacemark); } catch {}
	selfPinPlacemark = null;
}

function updateSelfMarker(lat, lon) {
	if (!map || !window.ymaps) return;
	ensureSelfPinPlacemark();
	try {
		selfPinPlacemark.geometry.setCoordinates([lat, lon]);
		try { if (currentUserId) selfPinPlacemark.properties.set('userId', currentUserId); } catch {}
		const url = (window.getSelfAvatarUrl ? window.getSelfAvatarUrl() : '') || '';
		selfPinPlacemark.options.set('iconContentLayout', createAvatarLayout(url, 1, true, false, '#3b82f6'));
		try { selfPinPlacemark.options.set('avatarUrl', url); } catch {}
	} catch {}
}

async function sendLocation(minIntervalSec = UPDATE_SEC) {
	if (!window.CabrioAPI?.apiPost) return;
	if (!userLocation) return;
	const now = Date.now();
	if (now - lastSentAt < Math.max(0, Number(minIntervalSec) || 0) * 1000) return;
	lastSentAt = now;
	try {
		const res = await window.CabrioAPI.apiPost('/api/user-locations', {
			latitude: userLocation.latitude,
			longitude: userLocation.longitude,
			accuracy: userLocation.accuracy
		});
		if (res && res.__httpStatus === 403) {
			showMapError('Чтобы тебя видели на карте, нужна регистрация.');
			stopTracking();
			return;
		}
		lastSentCoords = { lat: userLocation.latitude, lon: userLocation.longitude };
	} catch {}
}

async function clearMyLocation() {
	try {
		if (window.CabrioAPI?.apiDelete) {
			await window.CabrioAPI.apiDelete('/api/user-locations', {});
		}
	} catch {}
}

function startHeartbeat() {
	stopHeartbeat();
	heartbeatTid = setInterval(sendLocation, Math.max(UPDATE_SEC, 5) * 1000);
}

function stopHeartbeat() {
	if (heartbeatTid) { try { clearInterval(heartbeatTid); } catch {} heartbeatTid = null; }
}

function geoErrorMessage(err) {
	const code = err && typeof err.code === 'number' ? err.code : null;
	if (code === 1) return 'Нет доступа к геолокации. Карту смотреть можно.';
	if (code === 2) return 'Не удалось определить место. Карту смотреть можно.';
	if (code === 3) return 'Геолокация не ответила. Карту смотреть можно.';
	return 'Не удалось включить геолокацию. Карту смотреть можно.';
}

async function startTracking() {
	if (isTracking) return;
	if (!navigator.geolocation) {
		showMapError('В этом приложении нет геолокации. Карту смотреть можно.');
		return;
	}
	hideMapError();
	isTracking = true;
	setGpsBtnState(true);
	followMe = true;
	showFollowBtn(true);
	setShareHintVisible(false);
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
			if (lastLocation) {
				const distFix = distanceMeters(lastLocation.latitude, lastLocation.longitude, userLocation.latitude, userLocation.longitude);
				const prevMoving = isMoving;
				isMoving = distFix >= MOVE_THRESHOLD_M;
				if (prevMoving !== isMoving && typeof setUsersMoving === 'function') {
					try { setUsersMoving(isMoving); } catch {}
				}
			} else {
				isMoving = false;
			}
			if (lastSentAt === 0) {
				sendLocation(0);
				try { if (typeof refreshUsersNow === 'function') refreshUsersNow(); } catch {}
			}
			if (lastSentCoords) {
				const distSinceSent = distanceMeters(lastSentCoords.lat, lastSentCoords.lon, userLocation.latitude, userLocation.longitude);
				if (distSinceSent >= MOVE_THRESHOLD_M) {
					sendLocation(UPDATE_MOVING_SEC);
					try { if (typeof refreshUsersNow === 'function') refreshUsersNow(); } catch {}
				}
			} else {
				lastSentCoords = { lat: userLocation.latitude, lon: userLocation.longitude };
			}
			lastLocation = { latitude: userLocation.latitude, longitude: userLocation.longitude };
		},
		(err) => {
			stopTracking();
			showMapError(geoErrorMessage(err));
		},
		{ enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
	);
}

function stopTracking() {
	if (!isTracking) return;
	isTracking = false;
	setGpsBtnState(false);
	showFollowBtn(false);
	setShareHintVisible(true);
	if (watchId !== null) { try { navigator.geolocation.clearWatch(watchId); } catch {} watchId = null; }
	stopHeartbeat();
	releaseWakeLock();
	hideSelfMarker();
	userLocation = null;
	lastSentAt = 0;
	lastSentCoords = null;
	lastLocation = null;
	isMoving = false;
	if (typeof setUsersMoving === 'function') {
		try { setUsersMoving(false); } catch {}
	}
	// Сразу убираем себя у остальных, не ждём час
	clearMyLocation().then(() => {
		try { if (typeof refreshUsersNow === 'function') refreshUsersNow(); } catch {}
	});
}

async function loadMe() {
	try {
		if (!window.CabrioAPI?.apiGet) return;
		const me = await window.CabrioAPI.apiGet('/api/users/profile');
		const d = me?.data || me || {};
		const id = (typeof d.id === 'number') ? d.id : (typeof d.user?.id === 'number' ? d.user.id : null);
		currentUserId = id;
	} catch {}
}

function setupOnlinePanel() {
	const toggle = document.getElementById('peopleToggle');
	const list = document.getElementById('peopleList');
	if (!toggle || !list) return;

	const idleSec = Number(window.MAP_USERS_REFRESH_IDLE_SEC || 60);
	const movingSec = Number(window.MAP_USERS_REFRESH_MOVING_SEC || idleSec);

	toggle.textContent = 'Сейчас на карте — 0';
	toggle.addEventListener('click', () => {
		const open = list.hasAttribute('hidden');
		if (open) {
			renderPeopleList(list);
			list.removeAttribute('hidden');
			toggle.setAttribute('aria-expanded', 'true');
		} else {
			list.setAttribute('hidden', '');
			toggle.setAttribute('aria-expanded', 'false');
		}
	});

	list.addEventListener('click', (e) => {
		const item = e.target.closest('.people-item');
		if (!item || !item.dataset.lat) return;
		focusPerson(Number(item.dataset.lat), Number(item.dataset.lon), item.dataset.userId);
	});

	const doRefresh = async () => {
		try {
			const res = await (window.CabrioAPI?.apiGet ? window.CabrioAPI.apiGet('/api/user-locations') : Promise.resolve(null));
			if (res && res.__httpStatus === 403) {
				toggle.textContent = 'Сейчас на карте — 0';
				if (!list.hasAttribute('hidden')) {
					list.innerHTML = '<div class="people-item"><div class="pi-main"><div class="pi-name">Список людей на карте доступен участникам клуба</div></div></div>';
				}
				return;
			}
			if (res && res.success && Array.isArray(res.data)) {
				const LIVE_TIME_MIN = Number(window.MAP_LIVE_TIME_MIN || res.live_time_minutes || 60);
				activeUsers = res.data.slice().sort((a, b) => {
					const ta = Date.parse(String(a.updated_at).replace(' ', 'T') + 'Z') || 0;
					const tb = Date.parse(String(b.updated_at).replace(' ', 'T') + 'Z') || 0;
					return tb - ta;
				});
				toggle.textContent = `Сейчас на карте — ${activeUsers.length}`;
				if (!list.hasAttribute('hidden')) renderPeopleList(list);
				renderUsersOnMap(activeUsers, LIVE_TIME_MIN);
			}
		} catch {}
	};

	function restartUsersInterval(movingFlag) {
		if (usersRefreshTimer) { try { clearInterval(usersRefreshTimer); } catch {} usersRefreshTimer = null; }
		const sec = Math.max(10, movingFlag ? movingSec : idleSec);
		usersRefreshTimer = setInterval(() => { if (document.visibilityState === 'visible') doRefresh(); }, sec * 1000);
	}

	refreshUsersNow = doRefresh;
	setUsersMoving = function (flag) { restartUsersInterval(!!flag); };

	doRefresh();
	restartUsersInterval(false);
}

function renderPeopleList(container) {
	const items = activeUsers.map((loc) => {
		const name = (loc.user?.first_name) || 'Участник';
		const username = loc.user?.username ? '@' + loc.user.username : '';
		const avatar = (loc.user?.photo?.urls?.medium) || (loc.user?.photo?.urls?.mini) || (loc.user?.photo?.mini) || (loc.user?.photo_url) || phUserUrl()
		const mins = ageMinutesFromSql(loc.updated_at);
		const rel = relativeTimeLabel(mins);
		const lat = Number(loc.latitude);
		const lon = Number(loc.longitude);
		return `
      <div class="people-item" role="button" data-user-id="${escapeHtml(loc.user_id)}" data-lat="${lat}" data-lon="${lon}">
        <div class="pi-avatar"><img src="${escapeHtml(avatar)}" alt="" onerror="this.onerror=null;this.src='${escapeHtml(phUserUrl())}'"/></div>
        <div class="pi-main">
          <div class="pi-name">${escapeHtml(name)}</div>
          ${username ? `<div class="pi-username">${escapeHtml(username)}</div>` : ''}
        </div>
        <div class="pi-time">${escapeHtml(rel)}</div>
      </div>`;
	}).join('');
	container.innerHTML = items || '<div class="people-item"><div class="pi-main"><div class="pi-name">Никого на карте</div></div></div>';
}

// Метка Яндекса с HTML внутри: прозрачная картинка 1×1 — штатный способ API, не «своя карта»
function createAvatarLayout(_urlIgnored, opacity, isFresh, isPulse, strokeColor) {
	const cls = 'avatar-marker' + (isFresh ? ' fresh' : '') + (isPulse ? ' pulse' : '');
	const style = 'opacity:' + String(Math.max(0, Math.min(1, opacity))).substring(0, 5) + ';position:relative;cursor:pointer;';
	const svg = "<svg width=\"36\" height=\"48\" viewBox=\"0 0 498.923 498.923\" xmlns=\"http://www.w3.org/2000/svg\" aria-hidden=\"true\">" +
		"<defs>" +
		"<linearGradient id=\"g1\" x1=\"0\" y1=\"0\" x2=\"0\" y2=\"1\">" +
		"<stop offset=\"0%\" stop-color=\"#46e0d6\" stop-opacity=\"" + (isFresh ? '0.95' : '0.3') + "\"/>" +
		"<stop offset=\"100%\" stop-color=\"#46e0d6\" stop-opacity=\"" + (isFresh ? '0.95' : '0.3') + "\"/>" +
		"</linearGradient>" +
		"</defs>" +
		"<path d=\"M249.462,498.923C354.788,356.17,427.963,272.725,427.963,178.511C427.963,80.106,347.886,0,249.462,0C151.018,0,70.951,80.106,70.951,178.511C70.951,272.725,144.135,356.17,249.462,498.923Z\" fill=\"#222\" />" +
		"<path d=\"M249.462,498.923C354.788,356.17,427.963,272.725,427.963,178.511C427.963,80.106,347.886,0,249.462,0C151.018,0,70.951,80.106,70.951,178.511C70.951,272.725,144.135,356.17,249.462,498.923Z\" fill=\"url(#g1)\" fill-opacity=\"0\" stroke=\"" + (strokeColor || '#46e0d6') + "\" stroke-opacity=\"" + (isFresh ? '0.9' : '0.3') + "\" stroke-width=\"20\" />" +
		"</svg>";
	const avatarSrc = _urlIgnored || phUserUrl()
	const avatar = '<span class="marker-avatar"><img src="' + String(avatarSrc).replace(/"/g, '&quot;') + '" alt="" onerror="this.onerror=null;this.src=\'' + phUserUrl().replace(/'/g, '') + '\'"/></span>'
	const html = '<div class="' + cls + '" style="' + style + '">' + avatar + svg + '</div>';
	try {
		return ymaps.templateLayoutFactory.createClass(html, {
			build: function () {
				this.constructor.superclass.build.call(this);
				this._root = this.getParentElement();
				this._onDomClick = function (ev) {
					try { ev.preventDefault(); ev.stopPropagation(); } catch (e) {}
					const go = this.getData().geoObject;
					if (!go) return;
					const coords = go.geometry.getCoordinates() || [];
					const uid = go.properties.get('userId') || currentUserId;
					if (uid) focusPerson(coords[0], coords[1], uid);
				}.bind(this);
				if (this._root) this._root.addEventListener('click', this._onDomClick);
			},
			clear: function () {
				if (this._root && this._onDomClick) this._root.removeEventListener('click', this._onDomClick);
				this.constructor.superclass.clear.call(this);
			}
		});
	} catch (e) { return null; }
}

function renderUsersOnMap(list, liveTimeMin) {
	if (!map || !window.ymaps || !usersLayer) return;
	try { usersLayer.removeAll(); } catch {}
	const meId = currentUserId;
	for (const loc of list) {
		const lat = Number(loc.latitude), lon = Number(loc.longitude);
		if (!isFinite(lat) || !isFinite(lon)) continue;
		if (typeof liveTimeMin === 'number' && liveTimeMin > 0) {
			const age = ageMinutesFromSql(loc.updated_at);
			if (age > liveTimeMin) continue;
		}
		if (meId && Number(loc.user_id) === Number(meId)) continue;
		const ageMin = ageMinutesFromSql(loc.updated_at);
		const isFresh = true;
		const isPulse = ageMin < 3;
		const url = (loc.user?.photo?.urls?.medium) || (loc.user?.photo?.urls?.mini) || (loc.user?.photo?.mini) || (loc.user?.photo_url) || '';
		const contentLayout = createAvatarLayout(url, 1, isFresh, isPulse);
		try {
			const pm = new ymaps.Placemark([lat, lon], { userId: loc.user_id }, markerOpts(contentLayout, 5000));
			try { pm.options.set('avatarUrl', url); } catch {}
			pm.events.add('click', () => focusPerson(lat, lon, loc.user_id));
			usersLayer.add(pm);
		} catch {}
	}
}

document.addEventListener('DOMContentLoaded', () => {
	(async () => {
		window.CabrioBusy?.show()
		try {
		await loadMe();
		setupOnlinePanel();

		if (window.ymaps && typeof ymaps.ready === 'function') {
			const busyTimer = setTimeout(() => window.CabrioBusy?.hide(), 8000)
			ymaps.ready(() => {
				clearTimeout(busyTimer)
				try { initMap(); } catch {}
				try { if (typeof refreshUsersNow === 'function') refreshUsersNow(); } catch {}
				window.CabrioBusy?.hide()
			});
		} else {
			window.CabrioBusy?.hide()
		}

		setShareHintVisible(true);

		const gpsBtn = document.getElementById('sendLocationBtn');
		if (gpsBtn) {
			gpsBtn.addEventListener('click', () => {
				if (isTracking) {
					stopTracking();
					showToast('Тебя больше не видно на карте', 2200);
				} else {
					startTracking();
				}
			});
			setGpsBtnState(false);
		}

		const followBtn = document.getElementById('followMeBtn');
		if (followBtn) {
			followBtn.addEventListener('click', () => {
				followMe = !followMe;
				followBtn.setAttribute('aria-pressed', followMe ? 'true' : 'false');
				if (followMe && userLocation && map) {
					try { map.setCenter([userLocation.latitude, userLocation.longitude], 17, { duration: 0, checkZoomRange: true }); } catch {}
				}
			});
		}

		document.addEventListener('visibilitychange', () => {
			if (document.visibilityState === 'visible') {
				if (isTracking) acquireWakeLock();
			} else {
				releaseWakeLock();
			}
		});
		} catch {
			window.CabrioBusy?.hide()
		}
	})();
});

window.MapFunctions = { startTracking, stopTracking };
