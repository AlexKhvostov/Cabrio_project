<!DOCTYPE html>
<html>
<head>
    <title>🧪 Простой тест загрузки изображения</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { background: #f5f5f5; padding: 20px; margin: 10px 0; border-radius: 8px; }
        .info { background: #d1ecf1; padding: 15px; border-radius: 4px; }
        .error { background: #f8d7da; padding: 15px; border-radius: 4px; color: #721c24; }
        .success { background: #d4edda; padding: 15px; border-radius: 4px; color: #155724; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; max-height: 200px; }
        input[type="file"] { margin: 10px 0; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🧪 Простой тест загрузки изображения</h1>
    
    <div class="section">
        <h2>📷 Выберите изображение</h2>
        <input type="file" id="imageFile" accept="image/*" onchange="testImageLoad(event)">
        <button onclick="testSend()">🚀 Тест отправки</button>
    </div>
    
    <div class="section">
        <h2>📊 Результат</h2>
        <div id="result"></div>
    </div>
    
    <script>
        let currentImage = null;
        
        function testImageLoad(event) {
            const file = event.target.files[0];
            const result = document.getElementById('result');
            
            if (!file) {
                result.innerHTML = '<div class="error">❌ Файл не выбран</div>';
                return;
            }
            
            result.innerHTML = '<div class="info">⏳ Загружаем изображение...</div>';
            
            const reader = new FileReader();
            reader.onload = function(e) {
                currentImage = e.target.result;
                
                const format = currentImage.match(/^data:([^;]+);base64,/)?.[1] || 'неизвестный';
                const size = Math.round(currentImage.length * 0.75 / 1024); // KB
                
                result.innerHTML = `
                    <div class="success">
                        <h3>✅ Изображение загружено</h3>
                        <p><strong>Файл:</strong> ${file.name}</p>
                        <p><strong>Размер файла:</strong> ${(file.size / 1024).toFixed(2)} KB</p>
                        <p><strong>Тип файла:</strong> ${file.type}</p>
                        <p><strong>Base64 формат:</strong> ${format}</p>
                        <p><strong>Base64 размер:</strong> ${size} KB</p>
                        <p><strong>Длина строки:</strong> ${currentImage.length} символов</p>
                        <p><strong>Начинается с:</strong> ${currentImage.substring(0, 50)}...</p>
                        <p><strong>Заканчивается:</strong> ...${currentImage.substring(currentImage.length - 20)}</p>
                    </div>
                `;
                
                console.log('=== ЗАГРУЗКА ИЗОБРАЖЕНИЯ ===');
                console.log('Файл:', file.name);
                console.log('Тип:', file.type);
                console.log('Размер файла:', file.size, 'байт');
                console.log('Base64 длина:', currentImage.length);
                console.log('Base64 формат:', format);
                console.log('Начало:', currentImage.substring(0, 100));
                console.log('Конец:', currentImage.substring(currentImage.length - 50));
            };
            
            reader.onerror = function() {
                result.innerHTML = '<div class="error">❌ Ошибка загрузки файла</div>';
            };
            
            reader.readAsDataURL(file);
        }
        
        async function testSend() {
            if (!currentImage) {
                alert('Сначала выберите изображение');
                return;
            }
            
            const result = document.getElementById('result');
            result.innerHTML += '<div class="info">⏳ Тестируем отправку...</div>';
            
            const testData = {
                auth: {
                    user_id: 1,
                    role: 'member'
                },
                data: {
                    image: currentImage
                }
            };
            
            console.log('=== ТЕСТ ОТПРАВКИ ===');
            console.log('Размер запроса:', JSON.stringify(testData).length, 'символов');
            console.log('Изображение в запросе:', !!testData.data.image);
            console.log('Длина изображения:', testData.data.image ? testData.data.image.length : 'N/A');
            
            try {
                const response = await fetch('../../api/ocr/recognize.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(testData)
                });
                
                const responseData = await response.json();
                
                result.innerHTML += `
                    <div class="${responseData.success ? 'success' : 'error'}">
                        <h3>${responseData.success ? '✅ Успех' : '❌ Ошибка'}</h3>
                        <p><strong>HTTP статус:</strong> ${response.status}</p>
                        <p><strong>Размер запроса:</strong> ${JSON.stringify(testData).length} символов</p>
                        <pre>${JSON.stringify(responseData, null, 2)}</pre>
                    </div>
                `;
                
            } catch (error) {
                result.innerHTML += `
                    <div class="error">
                        <h3>❌ Ошибка сети</h3>
                        <p><strong>Сообщение:</strong> ${error.message}</p>
                    </div>
                `;
            }
        }
    </script>
</body>
</html> 