/**
 * Загрузчик списка событий и мест.
 */
(function (w) {
  function esc(str) {
    return String(str || '').replace(/[&<>"']/g, function (s) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[s]
    })
  }

  function apiRoot() {
    return String(w.__API_URL || (w.location.origin + '/app/backend')).replace(/\/$/, '')
  }

  function tgUser() {
    try {
      var u = (w.Telegram && w.Telegram.WebApp && w.Telegram.WebApp.initDataUnsafe && w.Telegram.WebApp.initDataUnsafe.user) || {}
      var out = {}
      if (u.id) out.telegram_id = String(u.id)
      if (u.first_name) out.first_name = String(u.first_name)
      if (u.last_name) out.last_name = String(u.last_name)
      if (u.username) out.username = String(u.username)
      return out
    } catch (e) {
      return {}
    }
  }

  function apiUrl(route) {
    var qp = []
    var u = tgUser()
    for (var k in u) {
      if (u[k]) qp.push(encodeURIComponent(k) + '=' + encodeURIComponent(u[k]))
    }
    return apiRoot() + '/routes/api.php?route=' + encodeURIComponent(route) + (qp.length ? '&' + qp.join('&') : '')
  }

  function start(opts) {
    var listEl = document.getElementById(opts.listId)
    var banner = document.getElementById(opts.bannerId)
    var fab = document.getElementById(opts.fabId)
    var kind = opts.kind
    var list = []

    function log() {}

    function roleCode(me) {
      try {
        return String((me && me.data && me.data.role && me.data.role.code) || (me && me.role && me.role.code) || '').toLowerCase()
      } catch (e) { return '' }
    }

    function canCreate(me) {
      var code = roleCode(me)
      return code === 'member' || code === 'moderator' || code === 'admin' || code === 'root'
    }

    var renderCard = null

    function loadCards() {
      if (!opts.cardUrl) return Promise.resolve(false)
      var load = Function('u', 'return import(u)')
      return load(opts.cardUrl).then(function (m) {
        renderCard = kind === 'guide' ? m.renderGuideCard : m.renderEventCard
        return typeof renderCard === 'function'
      }).catch(function (err) {
        log('плитка не загрузилась: ' + (err && err.message ? err.message : String(err)))
        return false
      })
    }

    function fillLabelFilter() {
      var sel = document.getElementById('typeFilter')
      if (!sel || kind !== 'guide') return
      var seen = {}
      var opts = ['<option value="">Все ярлыки</option>']
      list.forEach(function (place) {
        (place.labels || []).forEach(function (l) {
          var code = l.code || l.name
          if (!code || seen[code]) return
          seen[code] = true
          opts.push('<option value="' + esc(code) + '">#' + esc(l.name || code) + '</option>')
        })
      })
      sel.innerHTML = opts.join('')
    }

    function visibleList() {
      if (kind !== 'guide') return list
      var qRaw = String((document.getElementById('filters-search') || {}).value || '').toLowerCase().trim()
      var q = qRaw.replace(/^#/, '')
      var lab = String((document.getElementById('typeFilter') || {}).value || '').replace(/^#/, '')
      return list.filter(function (place) {
        var codes = (place.labels || []).map(function (l) {
          var raw = typeof l === 'string' ? l : (l.code || l.name || '')
          return String(raw).replace(/^#/, '').toLowerCase()
        })
        var tags = codes.join(' ')
        var hay = ((place.name || '') + ' ' + (place.description || '') + ' ' + tags + ' #' + codes.join(' #')).toLowerCase()
        if (q && hay.indexOf(q) < 0) return false
        if (lab && codes.indexOf(lab.toLowerCase()) < 0) return false
        return true
      })
    }

    function paint() {
      if (!listEl || typeof renderCard !== 'function') return
      var shown = visibleList()
      var html = shown.map(renderCard).join('')
      listEl.innerHTML = html || '<div class="empty-section" style="grid-column:1/-1">Пока пусто</div>'
    }

    function loadList() {
      var url = apiUrl(opts.route)
      log('запрос списка: ' + url)
      var t0 = Date.now()
      fetch(url).then(function (res) {
        log('ответ HTTP ' + res.status + ' за ' + (Date.now() - t0) + ' мс')
        return res.text().then(function (text) {
          log('размер ответа: ' + text.length + ' символов')
          var json = null
          try { json = JSON.parse(text) } catch (e) {
            log('это не JSON. Начало: ' + text.slice(0, 400))
            if (listEl) listEl.innerHTML = '<div class="empty-section" style="grid-column:1/-1">Сервер вернул не JSON</div>'
            return
          }
          if (json && json.error && json.error.message) log('ошибка API: ' + json.error.message)
          if (res.status === 401 || res.status === 403) {
            log('нет доступа (роль ниже нужной)')
            if (listEl) listEl.innerHTML = ''
            if (banner) banner.style.display = ''
            return
          }
          if (!json || json.success === false) {
            log('success=false')
            if (listEl) listEl.innerHTML = '<div class="empty-section" style="grid-column:1/-1">Не удалось загрузить список</div>'
            return
          }
          list = json.data || []
          log('записей в ответе: ' + list.length)
          if (list[0]) log('первая запись: ' + JSON.stringify({ id: list[0].id, title: list[0].title || list[0].name, labels: list[0].labels }))
          fillLabelFilter()
          paint()
        })
      }).catch(function (err) {
        log('сеть/fetch: ' + (err && err.message ? err.message : String(err)))
        if (listEl) listEl.innerHTML = '<div class="empty-section" style="grid-column:1/-1">Ошибка сети</div>'
      })
    }

    function loadMe() {
      var url = apiUrl('/api/users/profile')
      log('запрос профиля: ' + url)
      fetch(url).then(function (res) {
        return res.text().then(function (text) {
          log('профиль HTTP ' + res.status)
          var json = null
          try { json = JSON.parse(text) } catch (e) { log('профиль не JSON'); return }
          var code = roleCode(json)
          log('роль: ' + (code || '(пусто)'))
          if (canCreate(json) && fab) {
            fab.hidden = false
            log('кнопка + показана')
          } else {
            log('кнопка + скрыта (нужна роль member и выше)')
          }
        })
      }).catch(function (err) {
        log('профиль не загрузился: ' + (err && err.message ? err.message : String(err)))
      })
    }

    function openerName() {
      return kind === 'guide' ? 'openGuideModal' : 'openEventModal'
    }

    function formReady() {
      return w.CabrioModals && typeof w.CabrioModals[openerName()] === 'function'
    }

    function loadModal() {
      if (formReady()) {
        log('форма уже есть')
        return Promise.resolve(true)
      }
      if (!opts.modalUrl) {
        log('нет адреса файла формы')
        return Promise.resolve(false)
      }
      log('подключаю форму: ' + opts.modalUrl)
      var load = Function('u', 'return import(u)')
      return load(opts.modalUrl).then(function () {
        var ok = formReady()
        log(ok ? 'форма подключена' : 'файл загружен, но форма не зарегистрировалась')
        return ok
      }).catch(function (err) {
        log('форма не загрузилась: ' + (err && err.message ? err.message : String(err)))
        return false
      })
    }

    function openForm(data) {
      if (w.CabrioBusy) w.CabrioBusy.show()
      loadModal().then(function (ok) {
        if (w.CabrioBusy) w.CabrioBusy.hide()
        if (!ok) {
          log('не могу открыть форму')
          return
        }
        w.CabrioModals[openerName()](data || {}, { onChanged: loadList })
        log('форма открыта')
      }).catch(function () {
        if (w.CabrioBusy) w.CabrioBusy.hide()
      })
    }

    if (listEl) {
      listEl.addEventListener('click', function (e) {
        if (e.target.closest('.hint-i, .hint-pop')) return
        var card = e.target.closest('.event-card-compact')
        if (!card) return
        var id = card.getAttribute('data-id')
        log('тап по карточке id=' + id)
        if (w.CabrioNav && kind === 'event' && w.CabrioNav.openEvent) w.CabrioNav.openEvent(id, loadList)
        else if (w.CabrioNav && kind === 'guide' && w.CabrioNav.openGuide) w.CabrioNav.openGuide(id, loadList)
        else log('CabrioNav ещё нет')
      })
    }
    if (fab) {
      fab.addEventListener('click', function () {
        log('нажата +')
        openForm({})
      })
    }

    var searchEl = document.getElementById('filters-search')
    var typeEl = document.getElementById('typeFilter')
    if (searchEl) searchEl.addEventListener('input', paint)
    if (typeEl) typeEl.addEventListener('change', paint)

    document.addEventListener('click', function (e) {
      var chip = e.target.closest('.label-chip[data-label]')
      if (!chip || chip.classList.contains('is-edit')) return
      if (kind !== 'guide') return
      var code = chip.getAttribute('data-label')
      if (!code) return
      e.preventDefault()
      e.stopPropagation()
      if (searchEl) {
        searchEl.value = '#' + code
        paint()
      }
    })

    loadCards().then(function () {
      loadList()
    })
    loadMe()
    loadModal()
  }

  w.CabrioListDebug = { start: start }
})(window)
