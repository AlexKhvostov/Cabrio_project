<!DOCTYPE html>
<html>
<head>
    <title>🧪 Автотест API CabrioRide</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 10px; background: #f5f5f5; }
        h1 { color: #007cba; font-size: 1.2em; margin-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; font-size: 13px; background: #fff; }
        th, td { border: 1px solid #e0e0e0; padding: 4px 7px; text-align: center; }
        th { background: #f0f8ff; font-weight: 600; }
        tr:nth-child(even) { background: #fafbfc; }
        .ok { color: #28a745; font-size: 1.2em; cursor: pointer; }
        .fail { color: #dc3545; font-size: 1.2em; cursor: pointer; }
        .pending { color: #aaa; }
        .test-type { font-size: 11px; color: #888; }
        .endpoint { font-size: 12px; text-align: left; }
        .tooltip { display: none; position: absolute; z-index: 10; background: #fff; border: 1px solid #ccc; padding: 7px 10px; font-size: 12px; color: #222; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); max-width: 400px; word-break: break-all; }
        .copy-hint { font-size: 10px; color: #aaa; margin-top: 2px; }
        .footer { margin-top: 18px; color: #888; font-size: 12px; }
    </style>
</head>
<body>
<h1>🧪 Автотест API CabrioRide</h1>
<div id="table-wrap"></div>
<div class="footer">
    ✅ — тест пройден | ❌ — ошибка | Наведи для подробностей, кликни для копирования инфо о тесте.<br>
    OCR: для каждого фото отдельный тест. Все тесты запускаются автоматически.<br>
    <b>Если найдёшь баг — кликни по ячейке и отправь скопированное мне!</b>
</div>
<script>
// --- Динамическая загрузка эндпоинтов и тестов из JSON ---
Promise.all([
    fetch('endpoints_list.json').then(r => r.json()),
    fetch('endpoints_test_config.json').then(r => r.json())
]).then(([endpointsMeta, endpointsTests]) => {
    // OCR-файлы (асинхронно)
    fetchOCRFiles().then(ocrFiles => {
        // Собираем ENDPOINTS
        const ENDPOINTS = endpointsMeta.map(meta => {
            // Находим тесты для этого эндпоинта
            const testBlock = endpointsTests.find(t => t.url === meta.url);
            let tests = testBlock ? testBlock.tests : [];
            // Для OCR: если recognize/process, добавляем тесты по фото
            if ((meta.url === '/api/ocr/recognize' || meta.url === '/api/ocr/process') && ocrFiles.length) {
                tests = tests.concat(
                    ocrFiles.map(f => ({
                        type: `OCR-файл: ${f}`,
                        request: { ocr_file: f },
                        expect: { success: true }
                    }))
                );
            }
            return {
                name: meta.name,
                url: meta.url,
                method: meta.method,
                description: meta.description,
                tests
            };
        });
        window.ENDPOINTS = ENDPOINTS;
        renderTable();
        runAllTests();
    });
});

function fetchOCRFiles() {
    // Получаем список файлов из папки __test_ jpg через PHP (или через list.json, если появится)
    return fetch('../__test_ jpg/')
        .then(r => r.text())
        .then(html => {
            // Парсим HTML-список файлов (Apache/NGINX directory listing)
            const files = [];
            html.replace(/href="([^"]+\.(jpg|jpeg|png))"/gi, (m, f) => { files.push(f); });
            return files.filter(f => !f.startsWith('.'));
        })
        .catch(() => []);
}

function renderTable() {
    // Собираем все типы тестов по позициям (максимальное количество тестов среди всех эндпоинтов)
    let maxTests = Math.max(...ENDPOINTS.map(e => e.tests.length));
    // Для каждой позиции собираем type (название теста) из первого эндпоинта, где оно есть
    let testTypes = [];
    for (let i = 0; i < maxTests; ++i) {
        let type = '';
        for (let ep of ENDPOINTS) {
            if (ep.tests[i] && ep.tests[i].type) {
                type = ep.tests[i].type;
                break;
            }
        }
        testTypes.push(type);
    }
    let html = '<table><tr><th>Эндпоинт</th>';
    for (let i = 0; i < maxTests; ++i) {
        let t = testTypes[i] || '';
        let short = t.length > 16 ? t.slice(0, 16) + '…' : t;
        html += `<th title="${t.replace(/\"/g, '&quot;')}">${short}</th>`;
    }
    html += '</tr>';
    ENDPOINTS.forEach((ep, epi) => {
        html += `<tr><td class="endpoint">${ep.name}<div class="test-type">${ep.url}</div></td>`;
        for (let ti = 0; ti < maxTests; ++ti) {
            let t = ep.tests[ti];
            html += `<td id="cell-${epi}-${ti}" class="pending">…</td>`;
        }
        html += '</tr>';
    });
    html += '</table>';
    document.getElementById('table-wrap').innerHTML = html;
}

function runAllTests() {
    ENDPOINTS.forEach((ep, epi) => {
        ep.tests.forEach((test, ti) => {
            runTest(ep, test, epi, ti);
        });
    });
}

function runTest(ep, test, epi, ti) {
    const cell = document.getElementById(`cell-${epi}-${ti}`);
    if (!cell) return;
    let reqData = test.request; // Используем test.request для данных запроса
    let isOCR = ep.url.includes('/ocr/');
    if (isOCR && typeof reqData === 'string') {
        // Для OCR: загружаем файл как base64
        fetch(`../__test_jpg/${reqData}`)
            .then(r => r.blob())
            .then(blob => {
                const reader = new FileReader();
                reader.onload = function() {
                    let base64 = reader.result.split(',')[1];
                    let payload = {auth: {user_id: 1, role: 'member'}, data: {image: base64}};
                    sendRequest(ep, test, epi, ti, payload, reqData);
                };
                reader.readAsDataURL(blob);
            });
    } else {
        sendRequest(ep, test, epi, ti, reqData);
    }
}

function sendRequest(ep, test, epi, ti, reqData, fileName) {
    const cell = document.getElementById(`cell-${epi}-${ti}`);
    if (!cell) return;
    fetch(ep.url, {
        method: ep.method,
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(reqData)
    })
    .then(r => r.json().catch(() => ({success: false, error: {message: 'Некорректный JSON'}})))
    .then(resp => {
        let ok = resp.success === true;
        let expectOk = test.expect.success === true; // Сравниваем с expect.success
        let pass = (ok && expectOk) || (!ok && !expectOk);
        cell.className = pass ? 'ok' : 'fail';
        cell.innerHTML = pass ? '✅' : '❌';
        let details =
            `<b>Эндпоинт:</b> ${ep.name} (${ep.url})<br>`+
            `<b>Тип теста:</b> ${test.type}${fileName ? ' ('+fileName+')' : ''}<br>`+
            `<b>Ожидание:</b> ${test.expect.success ? 'Успех' : 'Ошибка'}<br>`+
            `<b>Отправлено:</b><br><pre>${JSON.stringify(reqData, null, 2)}</pre>`+
            `<b>Ответ:</b><br><pre>${JSON.stringify(resp, null, 2)}</pre>`+
            `<span class='copy-hint'>Клик — скопировать инфо</span>`;
        cell.onmouseenter = e => showTooltip(e, details);
        cell.onmouseleave = hideTooltip;
        cell.onclick = () => {
            let txt = `Эндпоинт: ${ep.name} (${ep.url})\nТип теста: ${test.type}${fileName ? ' ('+fileName+')' : ''}\nОжидание: ${test.expect.success ? 'Успех' : 'Ошибка'}\nОтправлено: ${JSON.stringify(reqData)}\nОтвет: ${JSON.stringify(resp)}`;
            copyToClipboard(txt);
        };
    })
    .catch(err => {
        cell.className = 'fail';
        cell.innerHTML = '❌';
        let details = `<b>Ошибка JS:</b> ${err}`;
        cell.onmouseenter = e => showTooltip(e, details);
        cell.onmouseleave = hideTooltip;
        cell.onclick = () => copyToClipboard(`Эндпоинт: ${ep.name} (${ep.url})\nТип теста: ${test.type}\nОшибка JS: ${err}`);
    });
}

// --- Tooltip ---
let tooltip;
function showTooltip(e, html) {
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        document.body.appendChild(tooltip);
    }
    tooltip.innerHTML = html;
    tooltip.style.display = 'block';
    let rect = e.target.getBoundingClientRect();
    tooltip.style.left = (rect.left + window.scrollX + 10) + 'px';
    tooltip.style.top = (rect.top + window.scrollY + 25) + 'px';
}
function hideTooltip() {
    if (tooltip) tooltip.style.display = 'none';
}
function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
}
</script>
</body>
</html> 