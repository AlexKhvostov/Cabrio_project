<?php
/**
 * CreateEventWithPhotoAction — создание события с фото.
 *
 * Назначение:
 *   Создаёт событие и загружает его фото (например, обложку).
 *   Операция выполняется в транзакции.
 *
 * Зависимости:
 *   - Event (модель)
 *   - Photo (модель)
 *   - Logger, ResponseHelper (утилиты)
 *
 * Пример использования:
 *   $result = CreateEventWithPhotoAction::handle($eventData, $photoData, $user_id);
 */
class CreateEventWithPhotoAction {
    /**
     * @param array $eventData — данные события
     * @param array $photoData — данные фото (файл, meta)
     * @param int $user_id — id пользователя
     * @return array — результат (успех/ошибка, данные события и фото)
     */
    public static function handle($eventData, $photoData, $user_id) {
        // 1. Начать транзакцию
        // 2. Создать событие
        // 3. Загрузить фото, создать запись в photos
        // 4. Зафиксировать транзакцию
        // 5. Вернуть результат
    }
} 