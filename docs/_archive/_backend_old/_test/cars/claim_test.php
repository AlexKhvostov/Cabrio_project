<?php
/**
 * Тест claim.php — "Забрать" авто без владельца
 * Позволяет вручную протестировать эндпоинт назначения себя владельцем автомобиля
 * Введите car_id, отправьте запрос, посмотрите результат
 */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тест: Забрать авто без владельца (claim.php)</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 520px;
            margin: 40px auto 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07), 0 1.5px 4px rgba(0,0,0,0.03);
            padding: 2.5em 2em 2em 2em;
        }
        h1 {
            font-size: 1.5em;
            margin-bottom: 0.5em;
            color: #1a4b7a;
        }
        label {
            display: block;
            margin-top: 1.2em;
            color: #1a4b7a;
            font-weight: 500;
        }
        input, select {
            width: 100%;
            padding: 0.6em 0.8em;
            margin-top: 0.3em;
            border: 1px solid #c7d0db;
            border-radius: 6px;
            font-size: 1em;
            background: #f8fafc;
            transition: border 0.2s;
        }
        input:focus, select:focus {
            border-color: #4fa3ff;
            outline: none;
        }
        .json-preview {
            background: #f8fafc;
            border: 1px solid #e0e6ed;
            border-radius: 6px;
            padding: 1em;
            margin-top: 1.5em;
            font-size: 0.97em;
            color: #2d3a4a;
        }
        button {
            margin-top: 1.5em;
            width: 100%;
            background: linear-gradient(90deg, #4fa3ff 0%, #1a4b7a 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 0.9em 0;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(79,163,255,0.08);
            transition: background 0.2s, box-shadow 0.2s;
        }
        button:hover {
            background: linear-gradient(90deg, #1a4b7a 0%, #4fa3ff 100%);
            box-shadow: 0 4px 16px rgba(79,163,255,0.13);
        }
        .result {
            margin-top: 2em;
            background: #f8fafc;
            border: 1px solid #e0e6ed;
            border-radius: 6px;
            padding: 1.2em;
            font-size: 1em;
            color: #1a4b7a;
            word-break: break-all;
        }
        hr {
            margin: 2.5em 0 1.5em 0;
            border: none;
            border-top: 1px solid #e0e6ed;
        }
        ul {
            color: #3a4a5d;
            font-size: 0.98em;
            margin-top: 0.5em;
        }
        @media (max-width: 600px) {
            .container {
                padding: 1.2em 0.5em 1.5em 0.5em;
                border-radius: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🚗 Тест: Забрать авто без владельца (claim.php)</h1>
    <form id="claimForm" onsubmit="return false;">
        <label>
            <b>car_id</b> (ID автомобиля):
            <input type="number" id="car_id" name="car_id" required>
        </label>
        <label>
            <b>user_id</b> (ваш user_id):
            <input type="number" id="user_id" name="user_id" value="1" required>
        </label>
        <label>
            <b>role</b> (ваша роль):
            <select id="role" name="role">
                <option value="member">member</option>
                <option value="moderator">moderator</option>
                <option value="admin">admin</option>
            </select>
        </label>
        <div class="json-preview">
            <b>JSON-запрос:</b>
            <pre id="jsonPreview"></pre>
        </div>
        <button type="submit" onclick="sendClaim()">Отправить запрос</button>
    </form>
    <div class="result" id="result"></div>
    <hr>
    <p><b>Пояснения:</b></p>
    <ul>
        <li>Этот тест позволяет вручную "забрать" авто без владельца (owner_user_id = NULL).</li>
        <li>Если авто уже с владельцем — будет ошибка 422.</li>
        <li>Если авто не найдено — будет ошибка 404.</li>
        <li>После успешного запроса вы становитесь владельцем авто, и создаётся связь в link_user_cars.</li>
    </ul>
</div>
<script>
    function updatePreview() {
        const car_id = document.getElementById('car_id').value;
        const user_id = document.getElementById('user_id').value;
        const role = document.getElementById('role').value;
        const json = {
            auth: { user_id: Number(user_id), role },
            data: { car_id: Number(car_id) }
        };
        document.getElementById('jsonPreview').textContent = JSON.stringify(json, null, 2);
    }
    document.getElementById('car_id').addEventListener('input', updatePreview);
    document.getElementById('user_id').addEventListener('input', updatePreview);
    document.getElementById('role').addEventListener('change', updatePreview);
    updatePreview();
    function sendClaim() {
        const car_id = document.getElementById('car_id').value;
        const user_id = document.getElementById('user_id').value;
        const role = document.getElementById('role').value;
        const payload = {
            auth: { user_id: Number(user_id), role },
            data: { car_id: Number(car_id) }
        };
        document.getElementById('result').innerHTML = '<em>Отправка запроса...</em>';
        fetch('../../api/cars/claim.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('result').innerHTML = '<b>Ответ:</b><br><pre>' + JSON.stringify(data, null, 2) + '</pre>';
        })
        .catch(e => {
            document.getElementById('result').innerHTML = '<b>Ошибка:</b> ' + e;
        });
    }
</script>
</body>
</html> 