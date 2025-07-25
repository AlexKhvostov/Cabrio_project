<?php
/**
 * AddBusinessCardAction — бизнес-операция добавления визитки.
 *
 * Назначение:
 *   Добавляет визитку к автомобилю:
 *   — Если авто уже есть, добавляет визитку к нему.
 *   — Если авто нет, создаёт новый авто и добавляет визитку.
 *
 * Зависимости:
 *   - BusinessCard (модель)
 *   - Car (модель)
 *   - CheckCarExistsAction (action)
 *   - Logger, ResponseHelper (утилиты)
 *
 * Пример использования:
 *   $result = AddBusinessCardAction::handle($carData, $businessCardData);
 */
class AddBusinessCardAction {
    /**
     * @param array $carData — данные автомобиля
     * @param array $businessCardData — данные визитки
     * @return array — результат (успех/ошибка, данные визитки, сообщения)
     */
    public static function handle($carData, $businessCardData) {
        // 1. Вызвать CheckCarExistsAction::handle($carData['number'])
        // 2. Если авто найдено — добавить визитку к нему
        // 3. Если авто не найдено — создать авто, затем добавить визитку
        // 4. Логировать действия
        // 5. Вернуть результат
    }
} 