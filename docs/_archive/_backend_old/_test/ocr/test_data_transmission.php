<!DOCTYPE html>
<html>
<head>
    <title>🔍 Тест передачи данных</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { background: #f5f5f5; padding: 20px; margin: 10px 0; border-radius: 8px; }
        .info { background: #d1ecf1; padding: 15px; border-radius: 4px; }
        .error { background: #f8d7da; padding: 15px; border-radius: 4px; color: #721c24; }
        .success { background: #d4edda; padding: 15px; border-radius: 4px; color: #155724; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; max-height: 300px; }
        input[type="file"] { margin: 10px 0; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🔍 Тест передачи данных изображения</h1>
    
    <div class="section">
        <h2>📷 Выберите изображение</h2>
        <input type="file" id="imageFile" accept="image/*" onchange="analyzeAndTest(event)">
    </div>
    
    <div class="section">
        <h2>📊 Анализ данных</h2>
        <div id="analysis"></div>
    </div>
    
    <div class="section">
        <h2>🧪 Тест передачи</h2>
        <div id="testResult"></div>
    </div>
    
    <script>
        function analyzeAndTest(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const base64Data = e.target.result;
                displayAnalysis(file, base64Data);
                testTransmission(base64Data);
            };
            reader.readAsDataURL(file);
        }
        
        function displayAnalysis(file, base64Data) {
            const analysis = document.getElementById('analysis');
            
            const format = base64Data.match(/^data:([^;]+);base64,/)?.[1] || 'неизвестный';
            const size = Math.round(base64Data.length * 0.75 / 1024); // KB
            
            analysis.innerHTML = `
                <div class="success">
                    <h3>✅ Изображение загружено</h3>
                    <p><strong>Файл:</strong> ${file.name}</p>
                    <p><strong>Размер файла:</strong> ${(file.size / 1024).toFixed(2)} KB</p>
                    <p><strong>Тип файла:</strong> ${file.type}</p>
                    <p><strong>Base64 формат:</strong> ${format}</p>
                    <p><strong>Base64 размер:</strong> ${size} KB</p>
                    <p><strong>Длина строки:</strong> ${base64Data.length} символов</p>
                    <p><strong>Начинается с:</strong> ${base64Data.substring(0, 50)}...</p>
                    <p><strong>Заканчивается:</strong> ...${base64Data.substring(base64Data.length - 20)}</p>
                </div>
                
                <h4>🔍 Проверка валидации:</h4>
                <div class="${isValidFormat(base64Data) ? 'success' : 'error'}">
                    <p><strong>Валидация:</strong> ${isValidFormat(base64Data) ? '✅ Проходит' : '❌ Не проходит'}</p>
                    <p><strong>Regex:</strong> /^data:image\/(jpeg|jpg|png|gif|webp);base64,/</p>
                </div>
            `;
        }
        
        function isValidFormat(base64Data) {
            return /^data:image\/(jpeg|jpg|png|gif|webp);base64,/.test(base64Data);
        }
        
        async function testTransmission(base64Data) {
            const testData = {
                auth: {
                    user_id: 1,
                    role: 'member'
                },
                data: {
                    image: base64Data
                }
            };
            
            const testResult = document.getElementById('testResult');
            testResult.innerHTML = '<div class="info">⏳ Тестируем передачу данных...</div>';
            
            // Показываем данные в консоли
            console.log('=== ТЕСТ ПЕРЕДАЧИ ДАННЫХ ===');
            console.log('Размер данных:', JSON.stringify(testData).length, 'символов');
            console.log('Формат изображения:', base64Data.match(/^data:([^;]+);base64,/)?.[1]);
            console.log('Начало данных:', base64Data.substring(0, 100));
            console.log('Конец данных:', base64Data.substring(base64Data.length - 50));
            
            try {
                const response = await fetch('../../api/ocr/recognize.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(testData)
                });
                
                const result = await response.json();
                
                testResult.innerHTML = `
                    <div class="${result.success ? 'success' : 'error'}">
                        <h3>${result.success ? '✅ Успех' : '❌ Ошибка'}</h3>
                        <p><strong>HTTP статус:</strong> ${response.status}</p>
                        <p><strong>Размер запроса:</strong> ${JSON.stringify(testData).length} символов</p>
                        <pre>${JSON.stringify(result, null, 2)}</pre>
                    </div>
                `;
                
            } catch (error) {
                testResult.innerHTML = `
                    <div class="error">
                        <h3>❌ Ошибка сети</h3>
                        <p><strong>Сообщение:</strong> ${error.message}</p>
                        <p><strong>Размер запроса:</strong> ${JSON.stringify(testData).length} символов</p>
                    </div>
                `;
            }
        }
    </script>
</body>
</html> 