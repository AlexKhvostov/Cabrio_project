<?php
/**
 * CreateUserWithPhotoAction — создание пользователя с фото.
 *
 * Назначение:
 *   Создаёт пользователя и загружает его фото (например, аватар).
 *   Операция выполняется в транзакции.
 *
 * Зависимости:
 *   - User (модель)
 *   - Photo (модель)
 *   - Logger, ResponseHelper (утилиты)
 *
 * Пример использования:
 *   $result = CreateUserWithPhotoAction::handle($userData, $photoData);
 */
class CreateUserWithPhotoAction {
    /**
     * @param array $userData — данные пользователя
     * @param array $photoData — данные фото (файл, meta)
     * @return array — результат (успех/ошибка, данные пользователя и фото)
     */
    public static function handle($userData, $photoData) {
        // 1. Начать транзакцию
        // 2. Создать пользователя
        // 3. Загрузить фото, создать запись в photos
        // 4. Зафиксировать транзакцию
        // 5. Вернуть результат
    }
} 