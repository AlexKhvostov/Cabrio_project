<?php
/**
 * CreateReviewWithPhotoAction — создание отзыва с фото.
 *
 * Назначение:
 *   Создаёт отзыв и загружает фото к отзыву.
 *   Операция выполняется в транзакции.
 *
 * Зависимости:
 *   - Review (модель)
 *   - Photo (модель)
 *   - Logger, ResponseHelper (утилиты)
 *
 * Пример использования:
 *   $result = CreateReviewWithPhotoAction::handle($reviewData, $photoData, $user_id);
 */
class CreateReviewWithPhotoAction {
    /**
     * @param array $reviewData — данные отзыва
     * @param array $photoData — данные фото (файл, meta)
     * @param int $user_id — id пользователя
     * @return array — результат (успех/ошибка, данные отзыва и фото)
     */
    public static function handle($reviewData, $photoData, $user_id) {
        // 1. Начать транзакцию
        // 2. Создать отзыв
        // 3. Загрузить фото, создать запись в photos
        // 4. Зафиксировать транзакцию
        // 5. Вернуть результат
    }
} 