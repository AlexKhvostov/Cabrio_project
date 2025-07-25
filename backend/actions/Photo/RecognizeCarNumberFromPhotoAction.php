<?php
/**
 * RecognizeCarNumberFromPhotoAction — распознавание номера авто по фото и проверка в базе.
 *
 * Назначение:
 *   Отправляет фото на сторонний сервис для распознавания номера, затем проверяет номер в базе.
 *
 * Зависимости:
 *   - CheckCarExistsAction (action)
 *   - Logger, ResponseHelper (утилиты)
 *
 * Пример использования:
 *   $result = RecognizeCarNumberFromPhotoAction::handle($photo);
 */
class RecognizeCarNumberFromPhotoAction {
    /**
     * @param mixed $photo — файл фото
     * @return array — результат (распознанный номер, данные авто, сообщения)
     */
    public static function handle($photo) {
        // 1. Отправить фото на сторонний сервис, получить номер
        // 2. Вызвать CheckCarExistsAction::handle($number)
        // 3. Вернуть результат (номер, данные авто)
    }
} 