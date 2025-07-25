<!DOCTYPE html>
<html>
<head>
    <title>🔍 Отладка формата изображения</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { background: #f5f5f5; padding: 20px; margin: 10px 0; border-radius: 8px; }
        .info { background: #d1ecf1; padding: 15px; border-radius: 4px; }
        .error { background: #f8d7da; padding: 15px; border-radius: 4px; color: #721c24; }
        .success { background: #d4edda; padding: 15px; border-radius: 4px; color: #155724; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        input[type="file"] { margin: 10px 0; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🔍 Отладка формата изображения</h1>
    
    <div class="section">
        <h2>📷 Выберите изображение для анализа</h2>
        <input type="file" id="imageFile" accept="image/*" onchange="analyzeImage(event)">
        <button onclick="testEndpoint()">🚀 Тест endpoint</button>
    </div>
    
    <div class="section">
        <h2>📊 Анализ изображения</h2>
        <div id="analysis"></div>
    </div>
    
    <div class="section">
        <h2>🧪 Тест endpoint</h2>
        <div id="endpointTest"></div>
    </div>
    
    <script>
        let currentImage = null;
        
        function analyzeImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                currentImage = e.target.result;
                displayAnalysis(file, currentImage);
            };
            reader.readAsDataURL(file);
        }
        
        function displayAnalysis(file, base64Data) {
            const analysis = document.getElementById('analysis');
            
            const format = base64Data.match(/^data:([^;]+);base64,/)?.[1] || 'неизвестный';
            const size = Math.round(base64Data.length * 0.75 / 1024); // KB
            const sizeMB = size / 1024;
            
            // Проверяем требования
            const isValidSize = sizeMB <= 3;
            const isValidFormat = isValidFormat(base64Data);
            
            analysis.innerHTML = `
                <div class="success">
                    <h3>✅ Изображение загружено</h3>
                    <p><strong>Файл:</strong> ${file.name}</p>
                    <p><strong>Размер файла:</strong> ${(file.size / 1024).toFixed(2)} KB</p>
                    <p><strong>Тип файла:</strong> ${file.type}</p>
                    <p><strong>Base64 формат:</strong> ${format}</p>
                    <p><strong>Base64 размер:</strong> ${size} KB (${sizeMB.toFixed(2)} MB)</p>
                    <p><strong>Начинается с:</strong> ${base64Data.substring(0, 50)}...</p>
                    <p><strong>Заканчивается:</strong> ...${base64Data.substring(base64Data.length - 20)}</p>
                </div>
                
                <h4>🔍 Проверка требований:</h4>
                <div class="${isValidFormat ? 'success' : 'error'}">
                    <p><strong>Формат:</strong> ${isValidFormat ? '✅ Поддерживается' : '❌ Не поддерживается'}</p>
                    <p><strong>Regex:</strong> /^data:image\/(jpeg|jpg|png|gif|webp);base64,/</p>
                </div>
                
                <div class="${isValidSize ? 'success' : 'error'}">
                    <p><strong>Размер:</strong> ${isValidSize ? '✅ В пределах нормы' : '❌ Слишком большой'}</p>
                    <p><strong>Текущий размер:</strong> ${sizeMB.toFixed(2)} MB</p>
                    <p><strong>Максимальный размер:</strong> 3 MB</p>
                </div>
                
                <h4>📋 Рекомендации для лучшего распознавания:</h4>
                <div class="info">
                    <ul>
                        <li><strong>Разрешение:</strong> 1024×768 или выше</li>
                        <li><strong>Ориентация:</strong> портретная (вертикальная)</li>
                        <li><strong>Автомобиль:</strong> минимум 15% площади изображения</li>
                        <li><strong>Номер:</strong> должен быть читаемым человеком</li>
                        <li><strong>Освещение:</strong> хорошее, без бликов</li>
                        <li><strong>Угол съёмки:</strong> фронтальный или под небольшим углом</li>
                    </ul>
                </div>
            `;
        }
        
        function isValidFormat(base64Data) {
            return /^data:image\/(jpeg|jpg|png|gif|webp);base64,/.test(base64Data);
        }
        
        async function testEndpoint() {
            if (!currentImage) {
                alert('Сначала выберите изображение');
                return;
            }
            
            const testData = {
                auth: {
                    user_id: 1,
                    role: 'member'
                },
                data: {
                    image: currentImage
                }
            };
            
            const endpointTest = document.getElementById('endpointTest');
            endpointTest.innerHTML = '<div class="info">⏳ Тестируем endpoint...</div>';
            
            try {
                const response = await fetch('../../api/ocr/recognize.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(testData)
                });
                
                const result = await response.json();
                
                endpointTest.innerHTML = `
                    <div class="${result.success ? 'success' : 'error'}">
                        <h3>${result.success ? '✅ Успех' : '❌ Ошибка'}</h3>
                        <pre>${JSON.stringify(result, null, 2)}</pre>
                    </div>
                `;
                
            } catch (error) {
                endpointTest.innerHTML = `
                    <div class="error">
                        <h3>❌ Ошибка сети</h3>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        }
    </script>
</body>
</html> 