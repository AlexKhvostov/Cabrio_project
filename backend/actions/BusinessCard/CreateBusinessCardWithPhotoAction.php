<?php
/**
 * CreateBusinessCardWithPhotoAction — создание визитки с фото.
 *
 * Назначение:
 *   Создаёт визитку (business_card) и загружает фото к ней.
 *   Операция выполняется в транзакции.
 *
 * Зависимости:
 *   - BusinessCard (модель)
 *   - Photo (модель)
 *   - Logger, ResponseHelper (утилиты)
 *
 * Пример использования:
 *   $result = CreateBusinessCardWithPhotoAction::handle($cardData, $photoData, $user_id);
 */
class CreateBusinessCardWithPhotoAction {
    /**
     * @param array $cardData — данные визитки
     * @param array $photoData — данные фото (файл, meta)
     * @param int $user_id — id пользователя
     * @return array — результат (успех/ошибка, данные визитки и фото)
     */
    public static function handle($cardData, $photoData, $user_id) {
        // 1. Начать транзакцию
        // 2. Создать визитку
        // 3. Загрузить фото, создать запись в photos
        // 4. Зафиксировать транзакцию
        // 5. Вернуть результат
    }
} 