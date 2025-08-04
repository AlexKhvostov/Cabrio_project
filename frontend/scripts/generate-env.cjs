const fs = require('fs');
const path = require('path');

// Путь к глобальному .env файлу
const globalEnvPath = path.join(__dirname, '../../.env');
// Путь к локальному .env файлу фронтенда
const localEnvPath = path.join(__dirname, '../.env');

// Переменные, которые нужно перенести из глобального .env в фронтенд
const FRONTEND_VARS = [
  'UPLOADS_BASE_URL',
  'APP_URL',
  'VITE_API_URL'
];

function generateFrontendEnv() {
  console.log('🔧 Генерируем .env файл для фронтенда...');
  
  // Проверяем существование глобального .env
  if (!fs.existsSync(globalEnvPath)) {
    console.log('⚠️ Глобальный .env файл не найден, создаем пустой .env для фронтенда');
    fs.writeFileSync(localEnvPath, '# Frontend environment variables\n');
    return;
  }
  
  // Читаем глобальный .env
  const globalEnvContent = fs.readFileSync(globalEnvPath, 'utf8');
  const globalEnvVars = {};
  
  // Парсим переменные из глобального .env
  globalEnvContent.split('\n').forEach(line => {
    line = line.trim();
    if (line && !line.startsWith('#') && line.includes('=')) {
      const [key, ...valueParts] = line.split('=');
      const value = valueParts.join('=').trim();
      globalEnvVars[key.trim()] = value;
    }
  });
  
  // Формируем содержимое для фронтенда .env
  let frontendEnvContent = '# Frontend environment variables\n';
  frontendEnvContent += '# Автоматически сгенерировано из глобального .env\n\n';
  
  // Добавляем переменные для фронтенда
  FRONTEND_VARS.forEach(varName => {
    if (globalEnvVars[varName]) {
      // Для VITE_ переменных добавляем префикс, если его нет
      const frontendVarName = varName.startsWith('VITE_') ? varName : `VITE_${varName}`;
      frontendEnvContent += `${frontendVarName}=${globalEnvVars[varName]}\n`;
    }
  });
  
  // Добавляем дополнительные переменные для фронтенда
  frontendEnvContent += '\n# Development settings\n';
  frontendEnvContent += 'VITE_DEV_MODE=true\n';
  
  // Записываем в локальный .env
  fs.writeFileSync(localEnvPath, frontendEnvContent);
  
  console.log('✅ .env файл для фронтенда создан:', localEnvPath);
  console.log('📋 Переменные:', FRONTEND_VARS.map(v => v.startsWith('VITE_') ? v : `VITE_${v}`).join(', '));
}

// Запускаем генерацию
generateFrontendEnv(); 