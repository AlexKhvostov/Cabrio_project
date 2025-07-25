<?php
/**
 * CreateCarWithPhotoAction — создание автомобиля с фото.
 *
 * Назначение:
 *   Создаёт автомобиль и загружает его фото.
 *   Операция выполняется в транзакции.
 *
 * Зависимости:
 *   - Car (модель)
 *   - Photo (модель)
 *   - Logger, ResponseHelper (утилиты)
 *
 * Пример использования:
 *   $result = CreateCarWithPhotoAction::handle($carData, $photoData, $user_id);
 */
class CreateCarWithPhotoAction {
    /**
     * @param array $carData — данные автомобиля
     * @param array $photoData — данные фото (файл, meta)
     * @param int $user_id — id пользователя
     * @return array — результат (успех/ошибка, данные авто и фото)
     */
    public static function handle($carData, $photoData, $user_id) {
        // 1. Начать транзакцию
        // 2. Создать авто
        // 3. Загрузить фото, создать запись в photos
        // 4. Зафиксировать транзакцию
        // 5. Вернуть результат
    }
} 